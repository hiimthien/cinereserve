<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\SeatLockingService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function __construct(
        protected SeatLockingService $seatLockingService
    ) {}

    public function checkout(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'showtime_id' => 'required|integer|exists:showtimes,id',
            'seat_ids' => 'required|array|min:1',
            'seat_ids.*' => 'integer|exists:seats,id',
            'session_id' => 'required|string',
            'total_amount' => 'required|numeric',
            'payment_method' => 'required|string|in:card,momo,vnpay',
            'card_holder' => 'nullable|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
        ]);

        try {
            $booking = $this->seatLockingService->confirmBooking(
                $validated['showtime_id'],
                $validated['seat_ids'],
                $validated['session_id'],
                $validated
            );

            return response()->json([
                'success' => true,
                'message' => 'Đặt vé và thanh toán thành công!',
                'data' => $booking,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function show(string $code): JsonResponse
    {
        $booking = Booking::with(['showtime.movie', 'showtime.room.cinema', 'bookingSeats.seat', 'payment'])
            ->where('booking_code', $code)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $booking,
        ]);
    }
}
