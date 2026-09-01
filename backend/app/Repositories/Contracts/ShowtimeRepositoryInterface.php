<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Showtime;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ShowtimeRepositoryInterface
{
    /**
     * Lấy danh sách phân trang theo bộ lọc (date, cinema_id, movie_id, status)
     */
    public function getPaginatedShowtimes(array $filters = [], int $perPage = 100): LengthAwarePaginator;

    /**
     * Lấy danh sách suất chiếu theo phim
     */
    public function getShowtimesByMovieId(int $movieId): Collection;

    /**
     * Tìm suất chiếu theo ID kèm eager load quan hệ
     */
    public function findById(int $id, array $relations = ['movie', 'cinema', 'room']): ?Showtime;

    /**
     * Tạo 1 suất chiếu đơn lẻ
     */
    public function create(array $attributes): Showtime;

    /**
     * Tạo hoặc cập nhật suất chiếu
     */
    public function updateOrCreate(array $attributes, array $values = []): Showtime;

    /**
     * Cập nhật suất chiếu theo ID
     */
    public function update(int $id, array $attributes): Showtime;

    /**
     * Xóa suất chiếu
     */
    public function delete(int $id): bool;
}
