<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Snack;
use Illuminate\Database\Eloquent\Collection;

interface SnackRepositoryInterface
{
    public function getFilteredSnacks(array $filters = []): Collection;

    public function findById(int $id): ?Snack;

    public function create(array $attributes): Snack;

    public function update(int $id, array $attributes): Snack;

    public function delete(int $id): bool;
}
