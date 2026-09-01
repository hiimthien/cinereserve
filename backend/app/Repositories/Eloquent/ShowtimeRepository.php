<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Showtime;
use App\Repositories\Contracts\ShowtimeRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ShowtimeRepository implements ShowtimeRepositoryInterface
{
    public function getPaginatedShowtimes(array $filters = [], int $perPage = 100): LengthAwarePaginator
    {
        $query = Showtime::with(['movie', 'cinema', 'room']);

        if (!empty($filters['date'])) {
            $query->whereDate('show_date', $filters['date']);
        }

        if (!empty($filters['cinema_id']) && $filters['cinema_id'] !== 'all') {
            $query->where('cinema_id', (int) $filters['cinema_id']);
        }

        if (!empty($filters['movie_id']) && $filters['movie_id'] !== 'all') {
            $query->where('movie_id', (int) $filters['movie_id']);
        }

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('show_date', 'asc')
            ->orderBy('start_time', 'asc')
            ->paginate($perPage);
    }

    public function getShowtimesByMovieId(int $movieId): Collection
    {
        return Showtime::with(['cinema', 'room'])
            ->where('movie_id', $movieId)
            ->orderBy('show_date', 'asc')
            ->orderBy('start_time', 'asc')
            ->get();
    }

    public function findById(int $id, array $relations = ['movie', 'cinema', 'room']): ?Showtime
    {
        return Showtime::with($relations)->findOrFail($id);
    }

    public function create(array $attributes): Showtime
    {
        return Showtime::create($attributes);
    }

    public function updateOrCreate(array $attributes, array $values = []): Showtime
    {
        return Showtime::updateOrCreate($attributes, $values);
    }

    public function update(int $id, array $attributes): Showtime
    {
        $showtime = Showtime::findOrFail($id);
        $showtime->update($attributes);
        return $showtime->fresh(['movie', 'cinema', 'room']);
    }

    public function delete(int $id): bool
    {
        $showtime = Showtime::findOrFail($id);
        return (bool) $showtime->delete();
    }
}
