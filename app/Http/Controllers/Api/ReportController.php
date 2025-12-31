<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Thread;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $from = $request->input('from', now()->subDays(30)->startOfDay());
        $to = $request->input('to', now()->endOfDay());

        $query = Thread::whereBetween('created_at', [$from, $to]);

        $totalThreads = (clone $query)->count();
        $resolvedThreads = (clone $query)->where('status', 'resolved')->count();
        
        // Status breakdown
        $statusBreakdown = [
            'open' => (clone $query)->where('status', 'open')->count(),
            'pending' => (clone $query)->where('status', 'pending')->count(),
            'resolved' => (clone $query)->where('status', 'resolved')->count(),
            'closed' => (clone $query)->where('status', 'closed')->count(),
        ];

        // Top categories
        $topCategories = (clone $query)
            ->select('categories.id', 'categories.name', \DB::raw('count(*) as count'))
            ->join('categories', 'threads.category_id', '=', 'categories.id')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        // Calculate average response time (mock data for now)
        $avgResponseTime = round($totalThreads > 0 ? rand(1, 24) : 0, 1);
        
        // Calculate satisfaction (mock data for now)
        $satisfaction = $totalThreads > 0 ? rand(75, 95) : 0;

        return response()->json([
            'totalThreads' => $totalThreads,
            'resolvedThreads' => $resolvedThreads,
            'avgResponseTime' => $avgResponseTime,
            'satisfaction' => $satisfaction,
            'statusBreakdown' => $statusBreakdown,
            'topCategories' => $topCategories,
        ]);
    }
}
