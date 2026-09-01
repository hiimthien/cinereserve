<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CheckoutRequest;
use App\Http\Resources\BookingResource;
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

    /**
     * Danh sách vé của tôi (My Tickets) - Chỉ hiển thị vé của chính tài khoản đang đăng nhập
     */
    public function index(Request $request): JsonResponse
    {
        // Xác định Email của người dùng hiện tại
        $userEmail = $request->user()?->email ?? $request->input('email');

        // Bảo mật: Nếu chưa đăng nhập hoặc không có email, không trả về vé của người khác
        if (empty($userEmail)) {
            return response()->json([
                'success' => true,
                'data' => [],
                'meta' => [
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => 6,
                    'total' => 0,
                ]
            ]);
        }

        $query = Booking::with([
            'showtime.movie',
            'showtime.cinema',
            'showtime.room',
            'bookingSeats.seat',
            'payment',
        ])
        ->where('user_email', $userEmail);

        // Lọc theo trạng thái vé (all, confirmed, checked_in)
        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        // Lọc theo Phim (movie_id)
        if ($request->filled('movie_id') && $request->input('movie_id') !== 'all') {
            $movieId = (int) $request->input('movie_id');
            $query->whereHas('showtime', function ($sq) use ($movieId) {
                $sq->where('movie_id', $movieId);
            });
        }

        // Lọc theo Cụm Rạp (cinema_id)
        if ($request->filled('cinema_id') && $request->input('cinema_id') !== 'all') {
            $cinemaId = (int) $request->input('cinema_id');
            $query->whereHas('showtime', function ($sq) use ($cinemaId) {
                $sq->where('cinema_id', $cinemaId);
            });
        }

        // Tìm kiếm đa năng: Mã vé (CR-...), Tên phim, Tên rạp
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('booking_code', 'like', "%{$search}%")
                  ->orWhereHas('showtime.movie', function ($mq) use ($search) {
                      $mq->where('title', 'like', "%{$search}%")
                        ->orWhere('original_title', 'like', "%{$search}%");
                  })
                  ->orWhereHas('showtime.cinema', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%");
                  });
            });
        }

        $perPage = (int) $request->input('per_page', 6);
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

    /**
     * Xác nhận đặt vé và thanh toán (Thin Controller)
     */
    public function checkout(CheckoutRequest $request): JsonResponse
    {
        $validated = $request->validated();

        try {
            $booking = $this->seatLockingService->confirmBooking(
                (int) $validated['showtime_id'],
                (array) $validated['seat_ids'],
                (string) $validated['session_id'],
                $validated
            );

            return response()->json([
                'success' => true,
                'message' => 'Đặt vé và thanh toán thành công!',
                'data' => new BookingResource($booking),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Tra cứu chi tiết vé theo Booking Code
     */
    public function show(string $code): JsonResponse
    {
        $booking = Booking::with([
            'showtime.movie',
            'showtime.cinema',
            'showtime.room',
            'bookingSeats.seat',
            'payment',
        ])
        ->where('booking_code', $code)
        ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => new BookingResource($booking),
        ]);
    }
}
