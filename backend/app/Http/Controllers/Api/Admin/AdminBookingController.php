<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Models\BookingSeat;
use App\Models\Seat;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminBookingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Booking::with([
            'showtime.movie',
            'showtime.cinema',
            'showtime.room',
            'bookingSeats.seat',
            'payment',
        ]);

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('cinema_id') && $request->input('cinema_id') !== 'all') {
            $cinemaId = (int) $request->input('cinema_id');
            $query->whereHas('showtime', fn($q) => $q->where('cinema_id', $cinemaId));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('booking_code', 'like', "%{$search}%")
                  ->orWhere('user_name', 'like', "%{$search}%")
                  ->orWhere('user_email', 'like', "%{$search}%")
                  ->orWhere('user_phone', 'like', "%{$search}%")
                  ->orWhereHas('showtime.movie', fn($mq) => $mq->where('title', 'like', "%{$search}%"));
            });
        }

        $perPage = (int) $request->input('per_page', 10);
        $paginated = $query->orderBy('id', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => BookingResource::collection($paginated->items()),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
            ]
        ]);
    }

    public function checkIn(int $id): JsonResponse
    {
        $booking = Booking::findOrFail($id);
        $booking->update([
            'status' => 'checked_in',
            'checked_in_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Đã soát vé thành công cho mã #{$booking->booking_code}!",
            'data' => new BookingResource($booking),
        ]);
    }

    public function cancel(int $id): JsonResponse
    {
        $booking = Booking::findOrFail($id);
        $booking->update(['status' => 'cancelled']);

        return response()->json([
            'success' => true,
            'message' => "Đã hủy đơn vé #{$booking->booking_code}!",
        ]);
    }
}
