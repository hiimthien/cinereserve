<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\MovieReview;
use App\Repositories\Contracts\ReviewRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ReviewRepository implements ReviewRepositoryInterface
{
    public function getByMovieId(int $movieId, int $limit = 20): Collection
    {
        return MovieReview::where('movie_id', $movieId)
            ->orderBy('id', 'desc')
            ->take($limit)
            ->get();
    }

    public function create(array $attributes): MovieReview
    {
        return MovieReview::create($attributes);
    }

    public function delete(int $id): bool
    {
        $review = MovieReview::findOrFail($id);
        return (bool) $review->delete();
    }

    public function calculateAverageRating(int $movieId): float
    {
        $avg = MovieReview::where('movie_id', $movieId)->avg('rating');
        return $avg ? round((float) $avg, 1) : 8.5;
    }
}
