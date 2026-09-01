<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Movie;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface MovieRepositoryInterface
{
    /**
     * Lấy danh sách phim phân trang theo bộ lọc
     */
    public function getPaginatedMovies(array $filters = [], int $perPage = 8): LengthAwarePaginator;

    /**
     * Lấy toàn bộ phim theo bộ lọc
     */
    public function getFilteredMovies(array $filters = []): Collection;

    /**
     * Tìm phim theo ID hoặc Slug
     */
    public function findByIdOrSlug(string|int $idOrSlug, array $relations = ['showtimes.room', 'showtimes.cinema', 'reviews']): ?Movie;

    /**
     * Tạo phim mới
     */
    public function create(array $attributes): Movie;

    /**
     * Cập nhật thông tin phim
     */
    public function update(int $id, array $attributes): Movie;

    /**
     * Xóa phim
     */
    public function delete(int $id): bool;
}
