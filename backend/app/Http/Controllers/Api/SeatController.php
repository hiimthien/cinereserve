<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\HoldSeatRequest;
use App\Services\SeatLockingService;
use Exception;
use Illuminate\Http\JsonResponse;

class SeatController extends Controller
{
    public function __construct(
        protected SeatLockingService $seatLockingService
    ) {}

    /**
     * Giữ ghế với Redis Atomic Lock (10 phút)
     */
    public function hold(HoldSeatRequest $request, int $showtimeId, int $seatId): JsonResponse
    {
        $validated = $request->validated();

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

    /**
     * Giải phóng ghế
     */
    public function release(HoldSeatRequest $request, int $showtimeId, int $seatId): JsonResponse
    {
        $validated = $request->validated();

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
