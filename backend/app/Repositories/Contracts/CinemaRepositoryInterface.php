<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Cinema;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface CinemaRepositoryInterface
{
    /**
     * Lấy danh sách phân trang theo bộ lọc (city, search, status)
     */
    public function getPaginatedCinemas(array $filters = [], int $perPage = 10): LengthAwarePaginator;

    /**
     * Lấy toàn bộ danh sách rạp
     */
    public function getAllCinemas(array $relations = ['rooms']): Collection;

    /**
     * Tìm rạp theo ID
     */
    public function findById(int $id, array $relations = ['rooms']): ?Cinema;

    /**
     * Tạo cụm rạp mới
     */
    public function create(array $attributes): Cinema;

    /**
     * Cập nhật cụm rạp
     */
    public function update(int $id, array $attributes): Cinema;

    /**
     * Xóa cụm rạp
     */
    public function delete(int $id): bool;
}
