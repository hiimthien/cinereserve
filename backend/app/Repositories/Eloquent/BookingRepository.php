<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Booking;
use App\Repositories\Contracts\BookingRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class BookingRepository implements BookingRepositoryInterface
{
    public function getFilteredBookings(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = Booking::with([
            'showtime.movie',
            'showtime.cinema',
            'showtime.room',
            'bookingSeats.seat',
            'payment',
        ]);

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['cinema_id']) && $filters['cinema_id'] !== 'all') {
            $cinemaId = (int) $filters['cinema_id'];
            $query->whereHas('showtime', fn($q) => $q->where('cinema_id', $cinemaId));
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('booking_code', 'like', "%{$search}%")
                  ->orWhere('user_name', 'like', "%{$search}%")
                  ->orWhere('user_email', 'like', "%{$search}%")
                  ->orWhere('user_phone', 'like', "%{$search}%")
                  ->orWhereHas('showtime.movie', fn($mq) => $mq->where('title', 'like', "%{$search}%"));
            });
        }

        return $query->orderBy('id', 'desc')->paginate($perPage);
    }

    public function findById(int $id, array $relations = [
        'showtime.movie',
        'showtime.cinema',
        'showtime.room',
        'bookingSeats.seat',
        'payment',
    ]): ?Booking
    {
        return Booking::with($relations)->findOrFail($id);
    }

    public function updateStatus(int $id, string $status, array $extraAttributes = []): Booking
    {
        $booking = Booking::findOrFail($id);
        $attributes = array_merge(['status' => $status], $extraAttributes);
        $booking->update($attributes);
        return $booking->fresh(['showtime.movie', 'showtime.cinema', 'showtime.room', 'bookingSeats.seat', 'payment']);
    }
}
