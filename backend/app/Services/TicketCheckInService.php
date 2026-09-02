<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Booking;
use App\Repositories\Contracts\BookingRepositoryInterface;
use Carbon\Carbon;
use Exception;

class TicketCheckInService
{
    public function __construct(
        protected BookingRepositoryInterface $bookingRepository
    ) {}

    public function findBookingForCheckIn(string $rawCode): ?Booking
    {
        $rawCode = trim($rawCode);
        $bookingCode = $rawCode;
        if (str_starts_with($rawCode, 'CINERESERVE-')) {
            $bookingCode = str_replace('CINERESERVE-', '', $rawCode);
        }

        return Booking::with([
            'showtime.movie',
            'showtime.cinema',
            'showtime.room',
            'bookingSeats.seat',
        ])->where('booking_code', $bookingCode)->first();
    }

    public function processCheckIn(Booking $booking, ?string $staffName = null, ?int $cinemaId = null): array
    {
        if ($booking->status !== 'confirmed') {
            return [
                'success' => false,
                'status' => 'UNPAID',
                'message' => "Vé này chưa được thanh toán thành công (Trạng thái: {$booking->status}).",
                'data' => $this->formatTicketData($booking),
            ];
        }

        // Kiểm tra quá hạn
        if ($booking->showtime) {
            $startShow = Carbon::parse($booking->showtime->start_time);
            $expiredThreshold = $startShow->copy()->addMinutes(45);
            if (Carbon::now()->isAfter($expiredThreshold)) {
                return [
                    'success' => false,
                    'status' => 'EXPIRED',
                    'message' => "Suất chiếu đã bắt đầu lúc {$startShow->format('H:i d/m/Y')} (Quá 45 phút). Vé đã hết hạn vào rạp.",
                    'data' => $this->formatTicketData($booking),
                ];
            }
        }

        // Kiểm tra đúng rạp
        if ($cinemaId && $booking->showtime && (int) $booking->showtime->cinema_id !== (int) $cinemaId) {
            $ticketCinema = $booking->showtime->cinema?->name ?? 'Rạp khác';
            return [
                'success' => false,
                'status' => 'WRONG_CINEMA',
                'message' => "Vé này được đặt tại [{$ticketCinema}], không thuộc cụm rạp hiện tại!",
                'data' => $this->formatTicketData($booking),
            ];
        }

        // Kiểm tra đã check-in trước đó
        if ($booking->check_in_status === 'checked_in') {
            $checkInTime = $booking->checked_in_at ? Carbon::parse($booking->checked_in_at)->format('H:i:s d/m/Y') : 'Không rõ';
            return [
                'success' => false,
                'status' => 'ALREADY_CHECKED_IN',
                'message' => "CẢNH BÁO: Vé này đã được soát vé vào lúc {$checkInTime}!",
                'data' => $this->formatTicketData($booking),
            ];
        }

        // Thực hiện Check-in
        $now = Carbon::now();
        $booking->update([
            'check_in_status' => 'checked_in',
            'checked_in_at' => $now,
            'checked_in_by' => $staffName ?: 'Staff Gate Scanner',
        ]);

        return [
            'success' => true,
            'status' => 'VALID',
            'message' => "Soát vé thành công! Chúc quý khách xem phim vui vẻ.",
            'data' => $this->formatTicketData($booking->fresh([
                'showtime.movie',
                'showtime.cinema',
                'showtime.room',
                'bookingSeats.seat',
            ])),
        ];
    }

    public function formatTicketData(Booking $booking): array
    {
        $seats = $booking->bookingSeats->map(function ($bs) {
            return $bs->seat ? "{$bs->seat->row}{$bs->seat->number}" : 'Ghế';
        })->toArray();

        return [
            'booking_id' => $booking->id,
            'booking_code' => $booking->booking_code,
            'movie_title' => $booking->showtime?->movie?->title ?? 'Phim',
            'cinema_name' => $booking->showtime?->cinema?->name ?? 'CineReserve Cinema',
            'room_name' => $booking->showtime?->room?->name ?? 'Phòng chiếu',
            'start_time' => $booking->showtime?->start_time ? Carbon::parse($booking->showtime->start_time)->format('H:i - d/m/Y') : '',
            'seats' => $seats,
            'seats_count' => count($seats),
            'combos' => $booking->combos ?? [],
            'customer_name' => $booking->user_name,
            'customer_phone' => $booking->user_phone,
            'total_amount' => (float) $booking->total_amount,
            'check_in_status' => $booking->check_in_status ?? 'pending',
            'checked_in_at' => $booking->checked_in_at ? Carbon::parse($booking->checked_in_at)->format('H:i:s d/m/Y') : null,
            'checked_in_by' => $booking->checked_in_by,
        ];
    }
}
