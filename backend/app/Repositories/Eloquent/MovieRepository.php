<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Movie;
use App\Repositories\Contracts\MovieRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class MovieRepository implements MovieRepositoryInterface
{
    public function getPaginatedMovies(array $filters = [], int $perPage = 8): LengthAwarePaginator
    {
        return Movie::with(['showtimes.cinema', 'showtimes.room'])
            ->filter($filters)
            ->orderBy('id', 'desc')
            ->paginate($perPage);
    }

    public function getFilteredMovies(array $filters = []): Collection
    {
        return Movie::with(['showtimes.cinema', 'showtimes.room'])
            ->filter($filters)
            ->orderByDesc('rating')
            ->orderByDesc('release_date')
            ->get();
    }

    public function findByIdOrSlug(string|int $idOrSlug, array $relations = ['showtimes.room', 'showtimes.cinema', 'reviews']): ?Movie
    {
        $query = Movie::with($relations);

        if (is_numeric($idOrSlug)) {
            return $query->find((int) $idOrSlug);
        }

        return $query->where('slug', $idOrSlug)->firstOrFail();
    }

    public function create(array $attributes): Movie
    {
        return Movie::create($attributes);
    }

    public function update(int $id, array $attributes): Movie
    {
        $movie = Movie::findOrFail($id);
        $movie->update($attributes);
        return $movie->fresh();
    }

    public function delete(int $id): bool
    {
        $movie = Movie::findOrFail($id);
        return (bool) $movie->delete();
    }
}
