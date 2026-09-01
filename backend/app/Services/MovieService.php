<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Movie;
use App\Repositories\Contracts\MovieRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class MovieService
{
    public function __construct(
        protected MovieRepositoryInterface $movieRepository
    ) {}

    public function getPaginatedMovies(array $filters = [], int $perPage = 8): LengthAwarePaginator
    {
        return $this->movieRepository->getPaginatedMovies($filters, $perPage);
    }

    public function getFilteredMovies(array $filters = []): Collection
    {
        return $this->movieRepository->getFilteredMovies($filters);
    }

    public function getMovieDetail(string|int $idOrSlug): ?Movie
    {
        return $this->movieRepository->findByIdOrSlug($idOrSlug);
    }

    public function createMovie(array $data): Movie
    {
        $duration = $data['duration'] ?? $data['duration_minutes'] ?? 120;
        $slug = Str::slug($data['title']);

        $count = Movie::where('slug', 'like', "{$slug}%")->count();
        if ($count > 0) {
            $slug .= '-' . ($count + 1);
        }

        $payload = array_merge($data, [
            'slug' => $slug,
            'duration' => $duration,
            'tmdb_id' => $data['tmdb_id'] ?? rand(100000, 999999),
        ]);

        return $this->movieRepository->create($payload);
    }

    public function updateMovie(int $id, array $data): Movie
    {
        if (isset($data['duration_minutes']) && !isset($data['duration'])) {
            $data['duration'] = $data['duration_minutes'];
        }

        return $this->movieRepository->update($id, $data);
    }

    public function deleteMovie(int $id): bool
    {
        return $this->movieRepository->delete($id);
    }
}
