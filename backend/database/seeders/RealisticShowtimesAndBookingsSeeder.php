<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\BookingSeat;
use App\Models\Cinema;
use App\Models\Movie;
use App\Models\Payment;
use App\Models\Room;
use App\Models\Seat;
use App\Models\Showtime;
use App\Models\User;
use App\Services\PricingService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class RealisticShowtimesAndBookingsSeeder extends Seeder
{
    public function run(): void
    {
        $movies = Movie::where('status', 'now_showing')->get();
        if ($movies->isEmpty()) {
            $movies = Movie::all();
        }
        $cinemas = Cinema::with('rooms.seats')->get();
        $users = User::where('role', 'user')->get();
        if ($users->isEmpty()) {
            $users = User::all();
        }
        $pricingService = app(PricingService::class);

        if ($movies->isEmpty() || $cinemas->isEmpty()) {
            return;
        }

        // 1. Dọn dẹp dữ liệu cũ để nạp mới tinh tươm
        Schema::disableForeignKeyConstraints();
        Payment::truncate();
        BookingSeat::truncate();
        Booking::truncate();
        Showtime::truncate();
        Schema::enableForeignKeyConstraints();

        // Danh sách khung giờ chuẩn rạp chiếu phim Việt Nam
        $timeSlots = [
            '08:30:00', // Suất Sáng (Early Bird -15K)
            '10:45:00', // Suất Trưa
            '13:15:00', // Suất Chiều
            '15:45:00', // Suất Chiều
            '18:30:00', // Giờ Vàng Tối (Weekend Surge +15K)
            '20:45:00', // Giờ Vàng Tối (Weekend Surge +15K)
            '23:15:00', // Suất Khuya (Midnight -15K)
        ];

        $today = Carbon::today();
        $createdShowtimes = [];

        // 2. Phân loại phim bom tấn chiếu rạp IMAX
        $imaxMovies = $movies->filter(fn($m) => in_array($m->slug, [
            'deadpool-and-wolverine',
            'dune-part-two',
            'spider-man-across-the-spider-verse',
            'godzilla-x-kong-the-new-empire',
            'alien-romulus-sneak-show'
        ]));
        if ($imaxMovies->isEmpty()) $imaxMovies = $movies;

        // 3. Sinh lịch chiếu cho 30 Cụm rạp x Phòng chiếu trong 7 ngày tới (Hôm nay -> +6 ngày)
        for ($dayOffset = 0; $dayOffset < 7; $dayOffset++) {
            $currentDate = $today->copy()->addDays($dayOffset);
            $dateStr = $currentDate->format('Y-m-d');

            foreach ($cinemas as $cinema) {
                foreach ($cinema->rooms as $room) {
                    $isImax = str_contains($room->room_type, 'IMAX') || str_contains($room->room_type, 'Super Plex') || str_contains($room->room_type, 'Starium');

                    // Chọn phim phù hợp định dạng phòng
                    $moviePool = $isImax ? $imaxMovies : $movies;
                    
                    // Mỗi phòng chiếu 3 - 5 suất/ngày
                    $dailySlots = collect($timeSlots)->random(rand(3, 5))->sort()->values();

                    foreach ($dailySlots as $startTime) {
                        $movie = $moviePool->random();
                        $basePrice = $isImax ? 145000 : 95000;

                        $st = Showtime::create([
                            'movie_id' => $movie->id,
                            'cinema_id' => $cinema->id,
                            'room_id' => $room->id,
                            'show_date' => $dateStr,
                            'start_time' => $startTime,
                            'end_time' => Carbon::parse($startTime)->addMinutes($movie->duration ?: 120)->format('H:i:s'),
                            'base_price' => $basePrice,
                        ]);

                        $createdShowtimes[] = $st;
                    }
                }
            }
        }

        // 4. Sinh thêm các suất chiếu lịch sử trong 30 ngày qua để tạo báo cáo doanh thu Dashboard
        $historicalShowtimes = [];
        for ($pastDay = 30; $pastDay >= 1; $pastDay--) {
            $pastDate = $today->copy()->subDays($pastDay);
            $pastDateStr = $pastDate->format('Y-m-d');

            // Chọn ngẫu nhiên 6 rạp mỗi ngày trong quá khứ để tạo suất chiếu lịch sử
            foreach ($cinemas->random(min(6, $cinemas->count())) as $cinema) {
                if ($cinema->rooms->isEmpty()) continue;
                $room = $cinema->rooms->first();
                $movie = $movies->random();

                $historicalSt = Showtime::create([
                    'movie_id' => $movie->id,
                    'cinema_id' => $cinema->id,
                    'room_id' => $room->id,
                    'show_date' => $pastDateStr,
                    'start_time' => '19:30:00',
                    'end_time' => '21:45:00',
                    'base_price' => 95000,
                ]);

                $historicalShowtimes[] = $historicalSt;
            }
        }

        $sampleCombos = [
            [['id' => 1, 'name' => 'Combo Bắp Nước Đơn (1 Bắp + 1 Pepsi)', 'price' => 85000, 'quantity' => 1]],
            [['id' => 2, 'name' => 'Combo Đôi Sweetbox (1 Bắp 2 Ngăn + 2 Pepsi)', 'price' => 125000, 'quantity' => 1]],
            [['id' => 3, 'name' => 'Combo Gia Đình Blockbuster (2 Bắp Lớn + 4 Pepsi)', 'price' => 195000, 'quantity' => 1]],
            [['id' => 4, 'name' => 'Combo Bắp Phô Mai & Bánh Nachos Salsa', 'price' => 110000, 'quantity' => 1]],
        ];

        // 5. Tạo 80+ Đơn Đặt Vé Lịch Sử (Đã Hoàn Thành) để lấp đầy Doanh Thu Dashboard
        foreach ($historicalShowtimes as $histSt) {
            $room = Room::with('seats')->find($histSt->room_id);
            if (!$room || $room->seats->isEmpty()) continue;

            $seatsCount = rand(2, 6);
            $selectedSeats = $room->seats->random($seatsCount);
            $user = $users->random();
            $combos = rand(0, 1) ? collect($sampleCombos)->random() : [];
            $comboTotal = collect($combos)->sum(fn($c) => $c['price'] * $c['quantity']);
            $seatsTotal = $seatsCount * 95000;
            $totalAmount = $seatsTotal + $comboTotal;
            $bookingCode = 'CR' . strtoupper(Str::random(6));

            $histBooking = Booking::create([
                'booking_code' => $bookingCode,
                'user_id' => $user->id,
                'showtime_id' => $histSt->id,
                'user_name' => $user->name,
                'user_email' => $user->email,
                'user_phone' => $user->phone ?: '0912345678',
                'total_amount' => $totalAmount,
                'combos' => $combos,
                'status' => 'confirmed',
                'check_in_status' => 'checked_in',
                'checked_in_at' => Carbon::parse($histSt->show_date)->setTimeFromTimeString($histSt->start_time)->subMinutes(rand(10, 30)),
                'reminder_sent_at' => Carbon::parse($histSt->show_date)->setTimeFromTimeString($histSt->start_time)->subHours(2),
                'qr_code' => $bookingCode,
                'created_at' => Carbon::parse($histSt->show_date)->subDays(rand(1, 3)),
            ]);

            Payment::create([
                'booking_id' => $histBooking->id,
                'transaction_id' => 'TXN' . strtoupper(Str::random(10)),
                'provider' => rand(0, 1) ? 'vnpay' : 'momo',
                'amount' => $totalAmount,
                'status' => 'success',
            ]);

            foreach ($selectedSeats as $s) {
                BookingSeat::create([
                    'booking_id' => $histBooking->id,
                    'seat_id' => $s->id,
                    'price' => 95000,
                ]);
            }
        }

        // 6. Tạo 16 Đơn Đặt Vé Mẫu Hôm Nay & Ngày Mai Cho Các User Thực Tế
        $hotShowtimes = collect($createdShowtimes)->filter(function ($st) use ($today) {
            $showDate = Carbon::parse($st->show_date);
            return $showDate->isToday() || $showDate->isTomorrow();
        });

        $bookingIndex = 0;
        foreach ($hotShowtimes->take(16) as $st) {
            $bookingIndex++;
            $room = Room::with('seats')->find($st->room_id);
            if (!$room || $room->seats->isEmpty()) continue;

            $seatsToBook = $room->seats->random(rand(2, 4));
            $targetUser = $users[$bookingIndex % $users->count()];

            $bookingCode = 'CR' . strtoupper(Str::random(6));
            $status = 'confirmed';
            $checkInStatus = 'pending';
            $checkedInAt = null;
            $reminderSentAt = null;

            if ($bookingIndex <= 8) {
                // 8 Vé Chưa Soát (Pending) để test quét QR & nhận Email
                $checkInStatus = 'pending';
            } elseif ($bookingIndex <= 13) {
                // 5 Vé Đã Soát (Checked In)
                $checkInStatus = 'checked_in';
                $checkedInAt = now()->subMinutes(rand(10, 45));
                $reminderSentAt = now()->subHours(2);
            } else {
                // 3 Vé Quá Hạn (Expired)
                $status = 'expired';
                $checkInStatus = 'expired';
            }

            $seatsTotal = 0;
            foreach ($seatsToBook as $s) {
                $seatsTotal += $pricingService->getSeatPrice($st, $s);
            }

            $combos = collect($sampleCombos)->random();
            $comboTotal = collect($combos)->sum(fn($c) => $c['price'] * $c['quantity']);
            $totalAmount = $seatsTotal + $comboTotal;

            $booking = Booking::create([
                'booking_code' => $bookingCode,
                'user_id' => $targetUser->id,
                'showtime_id' => $st->id,
                'user_name' => $targetUser->name,
                'user_email' => $targetUser->email,
                'user_phone' => $targetUser->phone ?: '0912345678',
                'total_amount' => $totalAmount,
                'combos' => $combos,
                'status' => $status,
                'check_in_status' => $checkInStatus,
                'checked_in_at' => $checkedInAt,
                'reminder_sent_at' => $reminderSentAt,
                'qr_code' => $bookingCode,
                'created_at' => now()->subHours(rand(1, 24)),
            ]);

            Payment::create([
                'booking_id' => $booking->id,
                'transaction_id' => 'TXN' . strtoupper(Str::random(10)),
                'provider' => $bookingIndex % 2 === 0 ? 'vnpay' : 'momo',
                'amount' => $totalAmount,
                'status' => $status === 'confirmed' ? 'success' : 'failed',
            ]);

            foreach ($seatsToBook as $s) {
                BookingSeat::create([
                    'booking_id' => $booking->id,
                    'seat_id' => $s->id,
                    'price' => $pricingService->getSeatPrice($st, $s),
                ]);
            }
        }

        // 7. Tạo ghế đã bán (Occupied Red Seats) cho 60+ suất chiếu hôm nay để phòng rạp đông đúc như thật
        foreach ($hotShowtimes->skip(16)->take(60) as $st) {
            $room = Room::with('seats')->find($st->room_id);
            if (!$room || $room->seats->isEmpty()) continue;

            $randomSeats = $room->seats->random(rand(8, 22)); // 15 - 25% ghế đã có người mua
            $dummyBooking = Booking::create([
                'booking_code' => 'CR' . strtoupper(Str::random(6)),
                'user_id' => $users->random()->id,
                'showtime_id' => $st->id,
                'user_name' => 'Khán Giả Rạp',
                'user_email' => 'guest@gmail.com',
                'user_phone' => '0901234567',
                'total_amount' => $randomSeats->count() * 95000,
                'status' => 'confirmed',
                'check_in_status' => 'pending',
                'qr_code' => 'CR' . strtoupper(Str::random(6)),
            ]);

            Payment::create([
                'booking_id' => $dummyBooking->id,
                'transaction_id' => 'TXN' . strtoupper(Str::random(10)),
                'provider' => 'vnpay',
                'amount' => $randomSeats->count() * 95000,
                'status' => 'success',
            ]);

            foreach ($randomSeats as $s) {
                BookingSeat::create([
                    'booking_id' => $dummyBooking->id,
                    'seat_id' => $s->id,
                    'price' => 95000,
                ]);
            }
        }
    }
}
