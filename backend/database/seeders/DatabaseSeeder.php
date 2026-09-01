<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Movie;
use App\Services\TmdbMovieSyncService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🚀 Bắt đầu khởi tạo dữ liệu thực tế chuẩn rạp chiếu phim CineReserve...');

        // Dọn dẹp sạch danh mục phim cũ để chống trùng lặp
        Schema::disableForeignKeyConstraints();
        Movie::truncate();
        Schema::enableForeignKeyConstraints();

        // 1. Đồng bộ phim tự động từ TMDb API (The Movie Database)
        $this->command->info('🎬 1. Đang đồng bộ phim trực tuyến thời gian thực từ TMDb API (Region: VN)...');
        $hasTmdbMovies = false;

        try {
            /** @var TmdbMovieSyncService $tmdbService */
            $tmdbService = app(TmdbMovieSyncService::class);
            $syncStats = $tmdbService->syncAllMovies(1, 1);
            if ($syncStats['total_movies'] > 0) {
                $hasTmdbMovies = true;
                $this->command->info("✅ Đã đồng bộ thành công {$syncStats['total_movies']} phim mới nhất từ TMDb!");
            }
        } catch (\Throwable $e) {
            $this->command->warn('⚠️ Không thể kết nối TMDb API, sẽ nạp danh mục phim mẫu.');
            Log::warning('TMDb Sync fallback in seeder: ' . $e->getMessage());
        }

        // Nếu TMDb trống hoặc chưa có phim, nạp RealisticMoviesSeeder làm dữ liệu gốc
        if (!$hasTmdbMovies || Movie::count() === 0) {
            $this->command->info('📦 Nạp danh mục 15 phim bom tấn mẫu chuẩn Cục Điện Ảnh...');
            $this->call(RealisticMoviesSeeder::class);
        }

        // 2. Tài Khoản Thành Viên (Admin, Staff, Diamond, Gold, Silver, Member)
        $this->command->info('👤 2. Đang nạp tài khoản người dùng mẫu...');
        $this->call(UserSeeder::class);

        // 3. Cụm Rạp & Phòng Chiếu Toàn Quốc
        $this->command->info('🏢 3. Đang nạp cụm rạp, phòng chiếu và ma trận ghế...');
        $this->call(VietnamCinemasSeeder::class);

        // 4. Lịch Chiếu 7 Ngày & Ghế Đã Bán Sinh Động
        $this->command->info('🎟️ 4. Đang sinh lịch chiếu & 12 đơn đặt vé mẫu thực tế...');
        $this->call(RealisticShowtimesAndBookingsSeeder::class);

        // 5. Đánh Giá & Nhận Xét Phim Thực Tế
        $this->command->info('⭐ 5. Đang nạp 40+ đánh giá & nhận xét phim sinh động...');
        $this->call(MovieReviewsSeeder::class);

        // 6. Combo Bắp Nước F&B
        $this->command->info('🍿 6. Đang nạp danh mục Combo Bắp Nước...');
        $this->call(SnackSeeder::class);

        // 7. Mã Voucher Giảm Giá
        $this->command->info('🏷️ 7. Đang nạp danh sách Voucher Khuyến Mãi...');
        $this->call(VoucherSeeder::class);

        // 8. Đổi Điểm Thưởng CinePoints
        $this->command->info('🎁 8. Đang nạp danh mục Đổi Thưởng Điểm Loyalty...');
        $this->call(LoyaltyRewardSeeder::class);

        $this->command->info('🎉 HOÀN TẤT NẠP TOÀN BỘ CƠ SỞ DỮ LIỆU THỰC TẾ CHO CINERESERVE!');
    }
}
