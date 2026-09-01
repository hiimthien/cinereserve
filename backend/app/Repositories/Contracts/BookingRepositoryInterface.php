<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Booking;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface BookingRepositoryInterface
{
    /**
     * Lấy danh sách đơn đặt vé kèm bộ lọc và phân trang
     */
    public function getFilteredBookings(array $filters = [], int $perPage = 10): LengthAwarePaginator;

    /**
     * Tìm đơn đặt vé theo ID kèm quan hệ
     */
    public function findById(int $id, array $relations = [
        'showtime.movie',
        'showtime.cinema',
        'showtime.room',
        'bookingSeats.seat',
        'payment',
    ]): ?Booking;

    /**
     * Cập nhật trạng thái đơn đặt vé
     */
    public function updateStatus(int $id, string $status, array $extraAttributes = []): Booking;
}
