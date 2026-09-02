<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ShowtimeResource;
use App\Services\SeatLockingService;
use App\Services\ShowtimeService;
use Illuminate\Http\JsonResponse;

class ShowtimeController extends Controller
{
    public function __construct(
        protected ShowtimeService $showtimeService,
        protected SeatLockingService $seatLockingService
    ) {}

    public function show(int $id): JsonResponse
    {
        $showtime = $this->showtimeService->findShowtime($id);

        return response()->json([
            'success' => true,
            'data' => new ShowtimeResource($showtime),
        ]);
    }

    public function getSeats(int $id): JsonResponse
    {
        $seats = $this->seatLockingService->getSeatsWithRealTimeStatus($id);

        return response()->json([
            'success' => true,
            'data' => $seats,
        ]);
    }
}
