<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MessageResource;
use App\Jobs\TranslateMessageJob;
use App\Models\MessageRevision;
use App\Models\Thread;
use App\Models\ThreadMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class MessageController extends Controller
{
    public function store(Request $request, Thread $thread): MessageResource
    {
        Gate::authorize('create-message', $thread);

        $validated = $request->validate([
            'content' => 'required|string',
            'visibility' => 'in:public,sensitive',
            'attachments' => 'array',
            'attachments.*' => 'image|max:5120',
        ]);

        $message = ThreadMessage::create([
            'thread_id' => $thread->id,
            'author_id' => auth()->id(),
            'author_type' => auth()->user()->is_staff ? 'agent' : 'user',
            'content' => $validated['content'],
            'locale' => $thread->locale,
            'visibility' => $validated['visibility'] ?? 'public',
        ]);

        // Handle attachments
        if (isset($validated['attachments'])) {
            foreach ($validated['attachments'] as $file) {
                $path = $file->store('attachments', 'public');
                $message->attachments()->create([
                    'filename' => $file->getClientOriginalName(),
                    'path' => $path,
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                ]);
            }
        }

        // Update thread timestamps
        $thread->update([
            'last_reply_at' => now(),
            'last_user_reply_at' => ! auth()->user()->is_staff ? now() : $thread->last_user_reply_at,
        ]);

        // Add author as participant if not already
        if (! $thread->participants()->where('user_id', auth()->id())->exists()) {
            $thread->participants()->attach(auth()->id(), ['joined_at' => now()]);
        }

        // Queue translation job
        TranslateMessageJob::dispatch($message);

        return new MessageResource($message->load(['author', 'attachments']));
    }

    public function update(Request $request, ThreadMessage $message): MessageResource
    {
        Gate::authorize('edit-message', $message);

        $validated = $request->validate([
            'content' => 'required|string',
        ]);

        // Store revision
        if (auth()->user()->is_staff) {
            MessageRevision::create([
                'message_id' => $message->id,
                'old_content' => $message->content,
                'new_content' => $validated['content'],
                'revised_by' => auth()->id(),
            ]);
        }

        $message->update(['content' => $validated['content']]);

        // Re-translate if original message
        if ($message->isOriginal()) {
            // Delete existing translations
            $message->translations()->delete();

            // Queue new translations
            TranslateMessageJob::dispatch($message);
        }

        return new MessageResource($message->load(['author', 'attachments']));
    }

    public function destroy(ThreadMessage $message): \Illuminate\Http\JsonResponse
    {
        Gate::authorize('delete-message', $message);

        // Delete attachments from storage
        foreach ($message->attachments as $attachment) {
            Storage::disk('public')->delete($attachment->path);
        }

        $message->delete();

        return response()->json(['message' => 'Message deleted successfully']);
    }

    public function revisions(ThreadMessage $message): \Illuminate\Http\JsonResponse
    {
        Gate::authorize('view-message-revisions');

        $revisions = $message->revisions()
            ->with('reviser')
            ->latest()
            ->get();

        return response()->json($revisions);
    }
}
