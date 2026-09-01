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
        $seats = $showtime->room ? $showtime->room->seats : collect();

        // Nếu phòng chưa có ghế, tự động tạo ma trận ghế chuẩn (118 ghế)
        if ($seats->isEmpty() && $showtime->room) {
            $rows = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K'];
            foreach ($rows as $row) {
                $cols = ($row === 'K') ? 6 : 14;
                for ($col = 1; $col <= $cols; $col++) {
                    $type = 'standard';
                    if ($row === 'K') {
                        $type = 'couple';
                    } elseif (in_array($row, ['E', 'F', 'G', 'H']) && $col >= 4 && $col <= 11) {
                        $type = 'vip';
                    }
                    Seat::create([
                        'room_id' => $showtime->room->id,
                        'row' => $row,
                        'number' => $col,
                        'type' => $type,
                    ]);
                }
            }
            $showtime->room->load('seats');
            $seats = $showtime->room->seats;
        }

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

            // Calculate price based on seat type (VND)
            $price = (float) ($showtime->base_price ?: 95000);
            if ($seat->type === 'vip') {
                $price = isset($showtime->price_vip) && (float)$showtime->price_vip > 0 
                    ? (float) $showtime->price_vip 
                    : ($price + 15000);
            } elseif ($seat->type === 'couple') {
                $price = isset($showtime->price_couple) && (float)$showtime->price_couple > 0 
                    ? (float) $showtime->price_couple 
                    : ($price * 2);
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

            // Calculate exact total price from seats (with Dynamic Pricing Engine)
            $pricingService = app(PricingService::class);
            $seatsTotal = 0;
            $seatsList = Seat::whereIn('id', $seatIds)->get();
            foreach ($seatsList as $seat) {
                $p = $pricingService->getSeatPrice($showtime, $seat);
                $seatsTotal += $p;
            }


            $combosTotal = 0;
            if (!empty($bookingData['combos'])) {
                foreach ($bookingData['combos'] as $cb) {
                    $combosTotal += (float) ($cb['price'] ?? 0) * (int) ($cb['quantity'] ?? 1);
                }
            }

            $discountAmount = (float) ($bookingData['discount_amount'] ?? 0);
            $calculatedTotal = max(0, $seatsTotal + $combosTotal - $discountAmount);

            $finalTotal = isset($bookingData['total_amount']) && (float)$bookingData['total_amount'] > 0
                ? (float) $bookingData['total_amount']
                : $calculatedTotal;

            // Create Booking with Combos, Voucher and Customer details
            $bookingCode = 'CR-' . strtoupper(substr(uniqid(), -6));
            $booking = \App\Models\Booking::create([
                'booking_code' => $bookingCode,
                'showtime_id' => $showtimeId,
                'user_name' => $bookingData['user_name'] ?? ($bookingData['card_holder'] ?? 'Cao Lương Thiện'),
                'user_email' => $bookingData['user_email'] ?? ($bookingData['email'] ?? 'caoluongthienk1@gmail.com'),
                'user_phone' => $bookingData['user_phone'] ?? ($bookingData['phone'] ?? '0388145796'),
                'total_amount' => $finalTotal,
                'combos' => $bookingData['combos'] ?? [],
                'voucher_code' => $bookingData['voucher_code'] ?? null,
                'discount_amount' => $discountAmount,
                'status' => 'confirmed',
                'expires_at' => now()->addDay(),
                'qr_code' => "https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=CINERESERVE-{$bookingCode}",
            ]);

            // Increment voucher usage if applied
            if (!empty($bookingData['voucher_code'])) {
                \App\Models\Voucher::where('code', $bookingData['voucher_code'])->increment('used_count');
            }


            // Award Loyalty points and update Membership tier
            $user = \App\Models\User::where('email', $booking->user_email)->first();
            if ($user) {
                $loyaltyResult = $user->processBookingLoyalty((float) $booking->total_amount, count($seatIds));

                // If upgraded to VIP or Diamond, send celebration upgrade mail!
                if ($loyaltyResult['upgraded']) {
                    try {
                        $upgradeVoucherCode = $user->membership_tier === 'diamond' ? 'FREEVECINE' : 'VIPCINE50';
                        $upgradeVoucher = \App\Models\Voucher::where('code', $upgradeVoucherCode)->first();
                        if ($upgradeVoucher) {
                            \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\LoyaltyVoucherMail(
                                user: $user,
                                voucher: $upgradeVoucher,
                                badgeText: "CHÚC MỪNG THĂNG HẠNG " . strtoupper($user->membership_tier),
                                customMessage: "Xin chúc mừng bạn đã xuất sắc thăng hạng lên {$user->getTierName()}! Để vinh danh cột mốc này, CineReserve gửi tặng bạn đặc quyền Voucher sau:",
                                subjectTitle: "👑 [CineReserve] Vinh danh thăng hạng {$user->getTierName()}! Tặng bạn Voucher độc quyền"
                            ));
                        }
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error('Lỗi gửi mail thăng hạng: ' . $e->getMessage());
                    }
                }
            }



            // Save booking seats & release Redis locks
            $seatsInfo = [];
            foreach ($seatIds as $seatId) {
                $seat = Seat::find($seatId);
                $seatPrice = $showtime->base_price ?: 95000;
                if ($seat && $seat->type === 'vip') $seatPrice += 20000;
                if ($seat && $seat->type === 'couple') $seatPrice = ($seatPrice * 2) + 30000;

                BookingSeat::create([
                    'booking_id' => $booking->id,
                    'seat_id' => $seatId,
                    'price' => $seatPrice,
                ]);

                // Forget redis lock
                Cache::forget($this->getSeatLockKey($showtimeId, $seatId));


                // Broadcast booked state
                event(new SeatStatusUpdated(
                    showtime_id: $showtimeId,
                    seat_id: $seatId,
                    status: 'booked'
                ));


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

            $booking->load(['showtime.movie', 'showtime.cinema', 'showtime.room', 'bookingSeats.seat']);
            $booking->setRelation('seats', collect($seatsInfo));

            // Gửi email vé điện tử qua Queue Job (Background Worker)
            try {
                if (!empty($booking->user_email)) {
                    \App\Jobs\SendTicketEmailJob::dispatch($booking);
                }
            } catch (\Exception $mailEx) {
                \Illuminate\Support\Facades\Log::error('Lỗi dispatch Queue Job gửi vé: ' . $mailEx->getMessage());
            }


            return $booking;
        });
    }

    /**
     * Thuật toán kiểm tra và chống để lại 1 ghế trống đơn lẻ (Anti-Orphan Seat)
     */
    public function validateAntiOrphanRule(int $showtimeId, array $selectedSeatIds): bool
    {
        if (empty($selectedSeatIds)) return true;

        $showtime = Showtime::find($showtimeId);
        if (!$showtime) return true;

        $allSeats = Seat::where('room_id', $showtime->room_id)->get()->groupBy('row');
        $selectedSet = array_flip($selectedSeatIds);

        foreach ($allSeats as $row => $seatsInRow) {
            $sorted = $seatsInRow->sortBy('number')->values();
            $total = $sorted->count();
            if ($total < 3) continue;

            $hasUserSeatInRow = false;
            foreach ($sorted as $s) {
                if (isset($selectedSet[$s->id])) {
                    $hasUserSeatInRow = true;
                    break;
                }
            }
            if (!$hasUserSeatInRow) continue;

            $isTaken = function (int $idx) use ($sorted, $total, $selectedSet, $showtimeId): bool {
                if ($idx < 0 || $idx >= $total) return true; // Mép ngoài tường coi như đã chặn
                $s = $sorted[$idx];
                if (isset($selectedSet[$s->id])) return true;
                if ($s->type === 'couple') return false;

                $lockKey = $this->getSeatLockKey($showtimeId, $s->id);
                return Cache::has($lockKey);
            };

            for ($i = 0; $i < $total; $i++) {
                if (!$isTaken($i)) {
                    if ($isTaken($i - 1) && $isTaken($i + 1)) {
                        return false; // Phát hiện tạo ra ghế trống đơn lẻ
                    }
                }
            }
        }

        return true;
    }

    private function getSeatLockKey(int $showtimeId, int $seatId): string
    {
        return "cinereserve:showtime:{$showtimeId}:seat:{$seatId}:holder";
    }
}
