<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Snack;
use App\Repositories\Contracts\SnackRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class SnackRepository implements SnackRepositoryInterface
{
    public function getFilteredSnacks(array $filters = []): Collection
    {
        $query = Snack::query();

        if (!empty($filters['category']) && $filters['category'] !== 'all') {
            $query->where('category', $filters['category']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('id', 'desc')->get();
    }

    public function findById(int $id): ?Snack
    {
        return Snack::findOrFail($id);
    }

    public function create(array $attributes): Snack
    {
        return Snack::create($attributes);
    }

    public function update(int $id, array $attributes): Snack
    {
        $snack = Snack::findOrFail($id);
        $snack->update($attributes);
        return $snack->fresh();
    }

    public function delete(int $id): bool
    {
        $snack = Snack::findOrFail($id);
        return (bool) $snack->delete();
    }
}
