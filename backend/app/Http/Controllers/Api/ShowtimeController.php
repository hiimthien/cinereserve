<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Showtime;
use App\Services\SeatLockingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShowtimeController extends Controller
{
    public function __construct(
        protected SeatLockingService $seatLockingService
    ) {}

    public function show(int $id): JsonResponse
    {
        $showtime = Showtime::with(['movie', 'cinema', 'room'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $showtime,
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
