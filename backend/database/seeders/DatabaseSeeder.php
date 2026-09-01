<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Services\TmdbMovieSyncService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Đồng bộ phim từ TMDb API
        $this->command->info('🎬 Đang đồng bộ phim thực tế từ TMDb API...');
        try {
            /** @var TmdbMovieSyncService $tmdbService */
            $tmdbService = app(TmdbMovieSyncService::class);
            $syncStats = $tmdbService->syncAllMovies(2, 1);
            $this->command->info("✅ Đã cập nhật {$syncStats['total_movies']} phim từ TMDb!");
        } catch (\Exception $e) {
            $this->command->warn('⚠️ Không thể kết nối TMDb API, giữ nguyên phim hiện có.');
            Log::warning('TMDb Sync warning in seeder: ' . $e->getMessage());
        }

        // 2. Khởi tạo toàn bộ chuỗi rạp chiếu phim tại Việt Nam
        $this->command->info('🏢 Đang nạp danh sách cụm rạp toàn quốc...');
        $this->call(VietnamCinemasSeeder::class);

        // 3. Khởi tạo lịch chiếu dày đặc 14 ngày tới
        $this->command->info('🎟️ Đang sinh lịch chiếu cho 14 ngày liên tiếp...');
        $this->call(ShowtimeGeneratorSeeder::class);

        // 4. Khởi tạo danh mục Bắp Nước Combos
        $this->command->info('🍿 Đang nạp danh mục Combo Bắp Nước...');
        $this->call(SnackSeeder::class);

        // 5. Khởi tạo Mã Giảm Giá / Vouchers
        $this->command->info('🏷️ Đang nạp danh sách Voucher Khuyến Mãi...');
        $this->call(VoucherSeeder::class);

        // 6. Khởi tạo Danh mục Đổi Điểm Thưởng Loyalty
        $this->command->info('🎁 Đang nạp danh mục Đổi Thưởng Điểm CinePoints...');
        $this->call(LoyaltyRewardSeeder::class);

        // 7. Khởi tạo Tài Khoản Thành Viên Mẫu
        $this->command->info('👤 Đang nạp tài khoản thành viên (Member, VIP, Diamond)...');
        $this->call(UserSeeder::class);

        // 8. Đảm bảo toàn bộ giá tiền chuẩn VNĐ
        $this->call(FixUsdPricesSeeder::class);

        $this->command->info('🎉 Hoàn tất nạp toàn bộ cơ sở dữ liệu CineReserve!');

    }
}
