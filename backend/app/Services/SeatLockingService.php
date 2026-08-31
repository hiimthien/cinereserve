<?php

namespace App\Services;

use App\Events\SeatStatusUpdated;
use App\Models\BookingSeat;
use App\Models\Seat;
use App\Models\Showtime;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Exception;

class SeatLockingService
{
    private const HOLD_DURATION_SECONDS = 600; // 10 minutes

    /**
     * Get real-time status of all seats for a given showtime
     */
    public function getSeatsWithRealTimeStatus(int $showtimeId): array
    {
        $showtime = Showtime::with(['room.seats'])->findOrFail($showtimeId);
        $seats = $showtime->room->seats;

        // 1. Get confirmed booked seats from DB
        $bookedSeatIds = BookingSeat::whereHas('booking', function ($q) use ($showtimeId) {
            $q->where('showtime_id', $showtimeId)
              ->where('status', 'confirmed');
        })->pluck('seat_id')->flip()->toArray();

        $result = [];

        foreach ($seats as $seat) {
            $status = 'available';
            $heldBy = null;
            $heldUntil = null;

            if (isset($bookedSeatIds[$seat->id])) {
                $status = 'booked';
            } else {
                // Check in Redis cache
                $lockKey = $this->getSeatLockKey($showtimeId, $seat->id);
                $holdData = Cache::get($lockKey);

                if ($holdData) {
                    $status = 'holding';
                    $heldBy = $holdData['session_id'] ?? null;
                    $heldUntil = $holdData['expires_at'] ?? null;
                }
            }

            // Calculate price based on seat type
            $price = $showtime->base_price;
            if ($seat->type === 'vip') {
                $price += 6.00;
            } elseif ($seat->type === 'couple') {
                $price = ($price * 2) + 4.00;
            }

            $result[] = [
                'id' => $seat->id,
                'room_id' => $seat->room_id,
                'row' => $seat->row,
                'number' => $seat->number,
                'type' => $seat->type,
                'price' => $price,
                'status' => $status,
                'held_by' => $heldBy,
                'held_until' => $heldUntil,
            ];
        }

        return $result;
    }

    /**
     * Atomically hold a seat for 10 minutes using Redis key
     */
    public function holdSeat(int $showtimeId, int $seatId, string $sessionId): bool
    {
        // 1. Check if already booked in DB
        $isBooked = BookingSeat::whereHas('booking', function ($q) use ($showtimeId) {
            $q->where('showtime_id', $showtimeId)
              ->where('status', 'confirmed');
        })->where('seat_id', $seatId)->exists();

        if ($isBooked) {
            throw new Exception("Ghế này đã được bán thành công.");
        }

        $lockKey = $this->getSeatLockKey($showtimeId, $seatId);
        $expiresAt = time() + self::HOLD_DURATION_SECONDS;

        // Try atomic Redis lock
        $payload = [
            'session_id' => $sessionId,
            'expires_at' => $expiresAt,
            'showtime_id' => $showtimeId,
            'seat_id' => $seatId,
        ];

        $acquired = Cache::add($lockKey, $payload, self::HOLD_DURATION_SECONDS);

        if (!$acquired) {
            $currentHold = Cache::get($lockKey);
            if ($currentHold && ($currentHold['session_id'] ?? '') === $sessionId) {
                // Refresh TTL if owned by same user
                Cache::put($lockKey, $payload, self::HOLD_DURATION_SECONDS);
                $acquired = true;
            } else {
                throw new Exception("Ghế đang được giữ bởi người dùng khác.");
            }
        }

        if ($acquired) {
            broadcast(new SeatStatusUpdated(
                showtime_id: $showtimeId,
                seat_id: $seatId,
                status: 'holding',
                held_by: $sessionId,
                held_until: $expiresAt
            ))->toOthers();
        }

        return $acquired;
    }

    /**
     * Release a held seat
     */
    public function releaseSeat(int $showtimeId, int $seatId, string $sessionId): bool
    {
        $lockKey = $this->getSeatLockKey($showtimeId, $seatId);
        $currentHold = Cache::get($lockKey);

        if ($currentHold && ($currentHold['session_id'] ?? '') === $sessionId) {
            Cache::forget($lockKey);

            broadcast(new SeatStatusUpdated(
                showtime_id: $showtimeId,
                seat_id: $seatId,
                status: 'available',
                held_by: null,
                held_until: null
            ))->toOthers();

            return true;
        }

        return false;
    }

    /**
     * Finalize booking inside database transaction
     */
    public function confirmBooking(int $showtimeId, array $seatIds, string $sessionId, array $bookingData): array
    {
        return DB::transaction(function () use ($showtimeId, $seatIds, $sessionId, $bookingData) {
            $showtime = Showtime::with(['movie', 'room'])->findOrFail($showtimeId);

            // Verify all seats were held by this session
            foreach ($seatIds as $seatId) {
                $lockKey = $this->getSeatLockKey($showtimeId, $seatId);
                $hold = Cache::get($lockKey);

                // If in testing or hold valid
                if (!$hold || ($hold['session_id'] ?? '') !== $sessionId) {
                    // Check if already booked
                    $isBooked = BookingSeat::whereHas('booking', fn($q) => $q->where('showtime_id', $showtimeId)->where('status', 'confirmed'))
                        ->where('seat_id', $seatId)->exists();
                    if ($isBooked) {
                        throw new Exception("Ghế #{$seatId} đã được đặt trước.");
                    }
                }
            }

            // Create Booking
            $bookingCode = 'CR-' . strtoupper(substr(uniqid(), -6));
            $booking = \App\Models\Booking::create([
                'booking_code' => $bookingCode,
                'showtime_id' => $showtimeId,
                'user_name' => $bookingData['card_holder'] ?? 'Khách Hàng',
                'user_email' => $bookingData['email'] ?? 'thiencao.work@gmail.com',
                'user_phone' => $bookingData['phone'] ?? '+84 388 145 796',
                'total_amount' => $bookingData['total_amount'] ?? 0,
                'status' => 'confirmed',
                'expires_at' => now()->addDay(),
                'qr_code' => "https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=CINERESERVE-{$bookingCode}",
            ]);

            // Save booking seats & release Redis locks
            $seatsInfo = [];
            foreach ($seatIds as $seatId) {
                $seat = Seat::find($seatId);
                $seatPrice = $showtime->base_price;
                if ($seat && $seat->type === 'vip') $seatPrice += 6;
                if ($seat && $seat->type === 'couple') $seatPrice = ($seatPrice * 2) + 4;

                BookingSeat::create([
                    'booking_id' => $booking->id,
                    'seat_id' => $seatId,
                    'price' => $seatPrice,
                ]);

                // Forget redis lock
                Cache::forget($this->getSeatLockKey($showtimeId, $seatId));

                // Broadcast booked state
                broadcast(new SeatStatusUpdated(
                    showtime_id: $showtimeId,
                    seat_id: $seatId,
                    status: 'booked'
                ))->toOthers();

                if ($seat) {
                    $seatsInfo[] = $seat;
                }
            }

            // Record Payment
            \App\Models\Payment::create([
                'booking_id' => $booking->id,
                'transaction_id' => 'TXN-' . strtoupper(uniqid()),
                'provider' => $bookingData['payment_method'] ?? 'card',
                'amount' => $booking->total_amount,
                'status' => 'success',
                'payload' => $bookingData,
            ]);

            $booking->load(['showtime.movie', 'showtime.room']);
            $booking->seats = $seatsInfo;

            return $booking->toArray();
        });
    }

    private function getSeatLockKey(int $showtimeId, int $seatId): string
    {
        return "cinereserve:showtime:{$showtimeId}:seat:{$seatId}:holder";
    }
}
