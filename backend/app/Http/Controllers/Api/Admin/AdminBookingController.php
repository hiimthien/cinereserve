<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookingResource;
use App\Services\AdminBookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminBookingController extends Controller
{
    public function __construct(
        protected AdminBookingService $adminBookingService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['status', 'cinema_id', 'search']);
        $perPage = (int) $request->input('per_page', 10);

        $paginated = $this->adminBookingService->getPaginatedBookings($filters, $perPage);

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
        $booking = $this->adminBookingService->checkInTicket($id);

        return response()->json([
            'success' => true,
            'message' => "Đã soát vé thành công cho mã #{$booking->booking_code}!",
            'data' => new BookingResource($booking),
        ]);
    }

    public function cancel(int $id): JsonResponse
    {
        $booking = $this->adminBookingService->cancelBooking($id);

        return response()->json([
            'success' => true,
            'message' => "Đã hủy đơn vé #{$booking->booking_code}!",
            'data' => new BookingResource($booking),
        ]);
    }
}
