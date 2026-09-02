<?php

declare(strict_types=1);

namespace App\Services;

use App\Events\SeatStatusUpdated;
use App\Models\Booking;
use App\Models\BookingSeat;
use App\Models\Payment;
use App\Models\Seat;
use App\Models\Showtime;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class BookingService
{
    public function __construct(
        protected SeatLockingService $seatLockingService
    ) {}

    /**
     * Xử lý thanh toán và tạo vé với Database Transaction + Pessimistic Locking
     */
    public function checkout(array $data): Booking
    {
        $showtimeId = (int) $data['showtime_id'];
        $seatIds = (array) $data['seat_ids'];
        $sessionId = (string) $data['session_id'];

        return DB::transaction(function () use ($data, $showtimeId, $seatIds, $sessionId) {
            $showtime = Showtime::with(['movie', 'cinema', 'room'])->findOrFail($showtimeId);

            // 1. Pessimistic Lock các bản ghi ghế trong DB để tránh Race Condition
            $seats = Seat::whereIn('id', $seatIds)->lockForUpdate()->get();

            if ($seats->count() !== count($seatIds)) {
                throw new InvalidArgumentException('Một số ghế được chọn không tồn tại.');
            }

            // 2. Tính toán tổng tiền vé theo loại ghế
            $totalAmount = 0;
            foreach ($seats as $seat) {
                $multiplier = match ($seat->type) {
                    'vip' => 1.3,
                    'couple' => 1.8,
                    default => 1.0,
                };
                $seatPrice = round(($showtime->base_price * $multiplier) / 1000) * 1000;
                $seat->price = $seatPrice;
                $totalAmount += $seatPrice;
            }

            // 3. Tính tiền combo bắp nước nếu có
            if (!empty($data['combos'])) {
                foreach ($data['combos'] as $combo) {
                    $totalAmount += (int) ($combo['price'] ?? 65000) * (int) $combo['quantity'];
                }
            }

            // 4. Tạo mã đặt vé & QR Code duy nhất
            $bookingCode = 'CR-' . strtoupper(Str::random(4)) . '-' . date('dmy');
            $qrHash = hash('sha256', "{$bookingCode}|{$showtimeId}|" . implode(',', $seatIds) . "|cinereserve_secret");

            $booking = Booking::create([
                'booking_code' => $bookingCode,
                'showtime_id' => $showtimeId,
                'user_name' => $data['user_name'],
                'user_email' => $data['user_email'],
                'user_phone' => $data['user_phone'],
                'total_amount' => $totalAmount,
                'status' => 'confirmed',
                'qr_code' => $qrHash,
            ]);

            // 5. Lưu từng ghế trong vé
            foreach ($seats as $seat) {
                BookingSeat::create([
                    'booking_id' => $booking->id,
                    'seat_id' => $seat->id,
                    'price' => $seat->price,
                ]);

                // Xóa lock tạm trong Redis
                $this->seatLockingService->releaseSeat($showtimeId, $seat->id, $sessionId);

                // Broadcast sự kiện ghế đã bán qua WebSocket Reverb
                broadcast(new SeatStatusUpdated(
                    showtime_id: $showtimeId,
                    seat_id: $seat->id,
                    status: 'booked'
                ))->toOthers();
            }

            // 6. Tạo bản ghi giao dịch thanh toán
            Payment::create([
                'booking_id' => $booking->id,
                'payment_method' => $data['payment_method'],
                'amount' => $totalAmount,
                'status' => 'success',
                'transaction_id' => 'TXN-' . strtoupper(Str::random(8)),
            ]);

            // Load relations đầy đủ để trả về Resource
            return $booking->load(['movie', 'showtime.cinema', 'showtime.room', 'seats']);
        });
    }
}
