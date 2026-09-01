<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Booking;
use App\Repositories\Contracts\BookingRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AdminBookingService
{
    public function __construct(
        protected BookingRepositoryInterface $bookingRepository
    ) {}

    /**
     * Lấy danh sách đặt vé có lọc và phân trang
     */
    public function getPaginatedBookings(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return $this->bookingRepository->getFilteredBookings($filters, $perPage);
    }

    /**
     * Soát vé Check-in cho đơn hàng
     */
    public function checkInTicket(int $id): Booking
    {
        $booking = Booking::findOrFail($id);
        $booking->update([
            'check_in_status' => 'checked_in',
            'checked_in_at' => now(),
        ]);

        return $booking->fresh(['showtime.movie', 'showtime.cinema', 'showtime.room', 'bookingSeats.seat', 'payment']);
    }

    /**
     * Hủy đơn đặt vé
     */
    public function cancelBooking(int $id): Booking
    {
        return $this->bookingRepository->updateStatus($id, 'cancelled');
    }
}
