<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use Carbon\Carbon;
use Illuminate\Support\Collection;

interface AnalyticsRepositoryInterface
{
    /**
     * Lấy tổng doanh thu trong khoảng thời gian theo bộ lọc
     */
    public function getRevenue(Carbon $startDate, Carbon $endDate, ?int $cinemaId = null, ?int $movieId = null): float;

    /**
     * Lấy tổng số lượng vé đã bán trong khoảng thời gian theo bộ lọc
     */
    public function getTicketsCount(Carbon $startDate, Carbon $endDate, ?int $cinemaId = null, ?int $movieId = null): int;

    /**
     * Lấy số lượng phim tổng thể
     */
    public function getTotalMoviesCount(): int;

    /**
     * Lấy số lượng suất chiếu đang và sắp chiếu
     */
    public function getActiveShowtimesCount(): int;

    /**
     * Lấy số lượng rạp
     */
    public function getTotalCinemasCount(): int;

    /**
     * Lấy tổng số lượng ghế trong hệ thống
     */
    public function getTotalSeatsCount(): int;

    /**
     * Lấy doanh thu phân nhóm theo ngày/giờ
     */
    public function getRevenueTrend(string $period, Carbon $startDate, Carbon $endDate, int $daysCount, ?int $cinemaId = null, ?int $movieId = null): array;

    /**
     * Lấy danh sách Top phim theo doanh thu
     */
    public function getTopMovies(int $limit = 5): Collection;

    /**
     * Lấy thị phần doanh thu theo chuỗi rạp
     */
    public function getCinemaDistribution(float $totalRevenue): array;
}
