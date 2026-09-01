<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminAnalyticsController extends Controller
{
    public function __construct(
        protected AnalyticsService $analyticsService
    ) {}

    /**
     * Dashboard Overview KPIs & Charts Data with Dynamic Filters
     */
    public function getAnalytics(Request $request): JsonResponse
    {
        $filterParams = $request->only(['period', 'cinema_id', 'movie_id', 'start_date', 'end_date']);
        $analyticsData = $this->analyticsService->calculateDashboardOverview($filterParams);

        return response()->json([
            'success' => true,
            'data' => $analyticsData,
        ]);
    }
}
