<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SeatLockingService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SeatController extends Controller
{
    public function __construct(
        protected SeatLockingService $seatLockingService
    ) {}

    public function hold(Request $request, int $showtimeId, int $seatId): JsonResponse
    {
        $validated = $request->validate([
            'session_id' => 'required|string',
        ]);

        try {
            $success = $this->seatLockingService->holdSeat(
                $showtimeId,
                $seatId,
                $validated['session_id']
            );

            return response()->json([
                'success' => $success,
                'message' => 'Giữ ghế thành công trong 10 phút.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function release(Request $request, int $showtimeId, int $seatId): JsonResponse
    {
        $validated = $request->validate([
            'session_id' => 'required|string',
        ]);

        $success = $this->seatLockingService->releaseSeat(
            $showtimeId,
            $seatId,
            $validated['session_id']
        );

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Đã giải phóng ghế.' : 'Không thể giải phóng ghế.',
        ]);
    }
}
