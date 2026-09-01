<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\MovieReview;
use Illuminate\Database\Eloquent\Collection;

interface ReviewRepositoryInterface
{
    /**
     * Lấy danh sách đánh giá mới nhất của 1 bộ phim
     */
    public function getByMovieId(int $movieId, int $limit = 20): Collection;

    /**
     * Tạo đánh giá mới
     */
    public function create(array $attributes): MovieReview;

    /**
     * Xóa đánh giá
     */
    public function delete(int $id): bool;

    /**
     * Tính điểm trung bình rating của phim
     */
    public function calculateAverageRating(int $movieId): float;
}
