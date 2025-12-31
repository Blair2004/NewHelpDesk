<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = User::where('is_staff', false)
            ->withCount(['licenses', 'threads']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $isActive = $request->input('status') === 'active';
            $query->where('is_active', $isActive);
        }

        $customers = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json($customers);
    }

    public function updateStatus(Request $request, User $customer): JsonResponse
    {
        $validated = $request->validate([
            'is_active' => 'required|boolean',
        ]);

        $customer->update($validated);

        return response()->json($customer);
    }
}
