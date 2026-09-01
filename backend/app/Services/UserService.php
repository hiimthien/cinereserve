<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function __construct(
        protected UserRepositoryInterface $userRepository
    ) {}

    public function getPaginatedUsers(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return $this->userRepository->getPaginatedUsers($filters, $perPage);
    }

    public function findUser(int $id): ?User
    {
        return $this->userRepository->findById($id);
    }

    public function createUser(array $data): User
    {
        $payload = [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password'] ?? 'password123'),
            'role' => $data['role'] ?? 'user',
            'phone' => $data['phone'] ?? null,
            'membership_tier' => $data['membership_tier'] ?? 'member',
            'points' => (int) ($data['points'] ?? 100),
        ];

        return $this->userRepository->create($payload);
    }

    public function updateUser(int $id, array $data): User
    {
        $payload = [
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'phone' => $data['phone'] ?? null,
            'membership_tier' => $data['membership_tier'] ?? 'member',
            'points' => (int) ($data['points'] ?? 0),
        ];

        if (!empty($data['password'])) {
            $payload['password'] = Hash::make($data['password']);
        }

        return $this->userRepository->update($id, $payload);
    }

    public function deleteUser(int $id): bool
    {
        return $this->userRepository->delete($id);
    }

    public function updateRole(int $id, string $role): User
    {
        return $this->userRepository->updateRole($id, $role);
    }

    public function adjustPoints(int $id, int $delta): User
    {
        return $this->userRepository->adjustPoints($id, $delta);
    }
}
