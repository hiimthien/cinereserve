<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Services\ReviewService;
use Illuminate\Http\JsonResponse;

class MovieReviewController extends Controller
{
    public function __construct(
        protected ReviewService $reviewService
    ) {}

    /**
     * Lấy danh sách đánh giá của bộ phim
     */
    public function index(int $movieId): JsonResponse
    {
        $reviews = $this->reviewService->getMovieReviews($movieId);

        return response()->json([
            'success' => true,
            'data' => ReviewResource::collection($reviews),
        ]);
    }

    /**
     * Gửi đánh giá mới cho phim
     */
    public function store(StoreReviewRequest $request, int $movieId): JsonResponse
    {
        $validated = $request->validated();
        $user = $request->user();
        $review = $this->reviewService->addReview($movieId, $validated, $user);

        return response()->json([
            'success' => true,
            'message' => 'Cảm ơn bạn đã gửi đánh giá cho bộ phim!',
            'data' => new ReviewResource($review),
        ], 201);
    }

    /**
     * Xóa đánh giá (Admin)
     */
    public function destroy(int $id): JsonResponse
    {
        $this->reviewService->deleteReview($id);

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa đánh giá thành công.',
        ]);
    }
}
