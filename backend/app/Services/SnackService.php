<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Snack;
use App\Repositories\Contracts\SnackRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class SnackService
{
    public function __construct(
        protected SnackRepositoryInterface $snackRepository
    ) {}

    public function getFilteredSnacks(array $filters = []): Collection
    {
        return $this->snackRepository->getFilteredSnacks($filters);
    }

    public function findSnack(int $id): ?Snack
    {
        return $this->snackRepository->findById($id);
    }

    public function createSnack(array $data): Snack
    {
        return $this->snackRepository->create($data);
    }

    public function updateSnack(int $id, array $data): Snack
    {
        return $this->snackRepository->update($id, $data);
    }

    public function deleteSnack(int $id): bool
    {
        return $this->snackRepository->delete($id);
    }
}
