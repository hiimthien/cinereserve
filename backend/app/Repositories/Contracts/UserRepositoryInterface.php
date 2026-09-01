<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface
{
    /**
     * Lấy danh sách người dùng phân trang theo bộ lọc (role, tier, search)
     */
    public function getPaginatedUsers(array $filters = [], int $perPage = 10): LengthAwarePaginator;

    /**
     * Tìm người dùng theo ID
     */
    public function findById(int $id, array $relations = ['bookings']): ?User;

    /**
     * Tạo tài khoản người dùng mới
     */
    public function create(array $attributes): User;

    /**
     * Cập nhật thông tin người dùng
     */
    public function update(int $id, array $attributes): User;

    /**
     * Xóa tài khoản người dùng
     */
    public function delete(int $id): bool;

    /**
     * Cập nhật vai trò (Role: admin / staff / user)
     */
    public function updateRole(int $id, string $role): User;

    /**
     * Điều chỉnh điểm CinePoints
     */
    public function adjustPoints(int $id, int $pointsDelta): User;
}
