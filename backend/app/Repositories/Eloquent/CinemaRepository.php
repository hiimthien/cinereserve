<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Cinema;
use App\Repositories\Contracts\CinemaRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class CinemaRepository implements CinemaRepositoryInterface
{
    public function getPaginatedCinemas(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = Cinema::withCount(['rooms', 'showtimes']);

        if (!empty($filters['city']) && $filters['city'] !== 'all') {
            $query->where('city', $filters['city']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('id', 'desc')->paginate($perPage);
    }

    public function getAllCinemas(array $relations = ['rooms']): Collection
    {
        return Cinema::with($relations)->orderBy('name', 'asc')->get();
    }

    public function findById(int $id, array $relations = ['rooms']): ?Cinema
    {
        return Cinema::with($relations)->findOrFail($id);
    }

    public function create(array $attributes): Cinema
    {
        return Cinema::create($attributes);
    }

    public function update(int $id, array $attributes): Cinema
    {
        $cinema = Cinema::findOrFail($id);
        $cinema->update($attributes);
        return $cinema->fresh(['rooms']);
    }

    public function delete(int $id): bool
    {
        $cinema = Cinema::findOrFail($id);
        return (bool) $cinema->delete();
    }
}
