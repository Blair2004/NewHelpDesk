<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ThreadResource;
use App\Jobs\TranslateMessageJob;
use App\Models\Thread;
use App\Models\ThreadMessage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class ThreadController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Thread::with(['owner', 'category', 'tags', 'assignedUser', 'assignedDepartment']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('visibility')) {
            $query->where('visibility', $request->input('visibility'));
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        if ($request->input('assigned_to_me')) {
            $query->where('assigned_user_id', auth()->id());
        }

        if ($request->input('unassigned')) {
            $query->whereNull('assigned_user_id');
        }

        // Filter by visibility for non-staff
        if (! auth()->user()->is_staff) {
            $query->where(function ($q) {
                $q->where('visibility', 'public')
                    ->orWhere('owner_id', auth()->id())
                    ->orWhereHas('participants', function ($participantQuery) {
                        $participantQuery->where('user_id', auth()->id());
                    });
            });
        }

        $threads = $query->latest('last_reply_at')->paginate(20);

        return ThreadResource::collection($threads);
    }

    public function store(Request $request): ThreadResource
    {
        Gate::authorize('create-thread');

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category_id' => 'nullable|exists:categories,id',
            'visibility' => 'in:public,private',
            'tags' => 'array',
            'tags.*' => 'exists:tags,id',
            'license_id' => auth()->user()->is_staff ? 'nullable' : 'required|exists:user_licenses,id',
        ]);
        
        // For non-staff users, verify the license belongs to them and is valid
        if (! auth()->user()->is_staff && isset($validated['license_id'])) {
            $license = auth()->user()->licenses()->find($validated['license_id']);
            
            if (! $license) {
                return response()->json(['message' => 'Invalid license selected'], 403);
            }
            
            if (! $license->isValid()) {
                return response()->json(['message' => 'Selected license has expired or is inactive'], 403);
            }
        }

        $thread = Thread::create([
            'title' => $validated['title'],
            'locale' => auth()->user()->preferred_locale,
            'visibility' => $validated['visibility'] ?? 'public',
            'status' => 'open',
            'owner_id' => auth()->id(),
            'category_id' => $validated['category_id'] ?? null,
            'license_id' => $validated['license_id'] ?? null,
        ]);

        if (isset($validated['tags'])) {
            $thread->tags()->sync($validated['tags']);
        }

        // Create initial message
        $message = ThreadMessage::create([
            'thread_id' => $thread->id,
            'author_id' => auth()->id(),
            'author_type' => auth()->user()->is_staff ? 'agent' : 'user',
            'content' => $validated['content'],
            'locale' => $thread->locale,
            'visibility' => 'public',
        ]);

        // Add owner as participant
        $thread->participants()->attach(auth()->id(), ['joined_at' => now()]);

        // Queue translation job
        TranslateMessageJob::dispatch($message);

        return new ThreadResource($thread->load(['owner', 'category', 'tags', 'messages.author']));
    }

    public function show(Thread $thread): ThreadResource
    {
        Gate::authorize('view-thread', $thread);

        return new ThreadResource($thread->load([
            'owner',
            'category',
            'tags',
            'assignedUser',
            'assignedDepartment',
            'participants',
            'messages' => function ($query) use ($thread) {
                $query->with(['author', 'attachments'])
                    ->where('locale', $thread->locale)
                    ->whereNull('deleted_at')
                    ->orderBy('created_at');
            },
        ]));
    }

    public function update(Request $request, Thread $thread): ThreadResource
    {
        Gate::authorize('edit-thread', $thread);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'status' => 'in:open,pending,resolved,closed',
            'is_locked' => 'boolean',
            'assigned_user_id' => 'nullable|exists:users,id',
            'assigned_department_id' => 'nullable|exists:departments,id',
            'tags' => 'array',
            'tags.*' => 'exists:tags,id',
        ]);

        $thread->update($validated);

        if (isset($validated['tags'])) {
            $thread->tags()->sync($validated['tags']);
        }

        return new ThreadResource($thread->load(['owner', 'category', 'tags', 'assignedUser', 'assignedDepartment']));
    }

    public function destroy(Thread $thread): \Illuminate\Http\JsonResponse
    {
        Gate::authorize('delete-thread', $thread);

        $thread->delete();

        return response()->json(['message' => 'Thread deleted successfully']);
    }

    public function stats(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = auth()->user();
        
        $query = Thread::query();
        
        // Filter by visibility for non-staff
        if (! $user->is_staff) {
            $query->where(function ($q) use ($user) {
                $q->where('visibility', 'public')
                    ->orWhere('owner_id', $user->id)
                    ->orWhereHas('participants', function ($participantQuery) use ($user) {
                        $participantQuery->where('user_id', $user->id);
                    });
            });
        }
        
        return response()->json([
            'newThreads' => (clone $query)->where('status', 'open')->whereDate('created_at', '>=', now()->subDays(7))->count(),
            'openThreads' => (clone $query)->where('status', 'open')->count(),
            'pendingThreads' => (clone $query)->where('status', 'pending')->count(),
            'closedThreads' => (clone $query)->where('status', 'closed')->count(),
        ]);
    }
}
