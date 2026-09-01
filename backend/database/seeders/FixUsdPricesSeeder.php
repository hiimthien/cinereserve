<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\BookingSeat;
use App\Models\Showtime;
use App\Models\Snack;
use App\Models\Voucher;
use Illuminate\Database\Seeder;

class FixUsdPricesSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Convert any showtime with USD base_price (< 1000) to VND
        $showtimes = Showtime::where('base_price', '<', 1000)->get();
        foreach ($showtimes as $st) {
            $st->update([
                'base_price' => (float) ($st->base_price * 10000),
            ]);
        }

        // 2. Ensure all bookings have proper VND total_amount
        $bookings = Booking::where('total_amount', '<', 1000)->get();
        foreach ($bookings as $b) {
            $b->update([
                'total_amount' => 190000,
            ]);
        }

        $minPrice = Showtime::min('base_price');
        $maxPrice = Showtime::max('base_price');
        $this->command->info("✅ Đã chuẩn hóa toàn bộ tỉ giá sang VNĐ! (Giá min: {$minPrice} đ, Giá max: {$maxPrice} đ)");
    }
}
