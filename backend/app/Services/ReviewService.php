<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Movie;
use App\Models\MovieReview;
use App\Models\User;
use App\Repositories\Contracts\ReviewRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ReviewService
{
    public function __construct(
        protected ReviewRepositoryInterface $reviewRepository
    ) {}

    public function getMovieReviews(int $movieId, int $limit = 20): Collection
    {
        return $this->reviewRepository->getByMovieId($movieId, $limit);
    }

    public function addReview(int $movieId, array $data, ?User $user = null): MovieReview
    {
        $movie = Movie::findOrFail($movieId);

        $userName = $user ? $user->name : ($data['user_name'] ?? 'Khán Giả CineReserve');
        $userAvatar = $user ? $user->avatar : null;
        $tier = $user ? $user->membership_tier : 'member';

        $review = $this->reviewRepository->create([
            'movie_id' => $movie->id,
            'user_id' => $user?->id,
            'user_name' => $userName,
            'user_avatar' => $userAvatar,
            'membership_tier' => $tier,
            'rating' => min(10, max(1, (int) $data['rating'])),
            'comment' => $data['comment'],
            'likes_count' => 0,
        ]);

        // Cập nhật rating trung bình của phim
        $newAverage = $this->reviewRepository->calculateAverageRating($movie->id);
        $movie->update(['rating' => $newAverage]);

        return $review;
    }

    public function deleteReview(int $id): bool
    {
        $review = MovieReview::findOrFail($id);
        $movieId = $review->movie_id;

        $deleted = $this->reviewRepository->delete($id);

        // Cập nhật lại rating của phim sau khi xóa
        $newAverage = $this->reviewRepository->calculateAverageRating($movieId);
        Movie::where('id', $movieId)->update(['rating' => $newAverage]);

        return $deleted;
    }
}
