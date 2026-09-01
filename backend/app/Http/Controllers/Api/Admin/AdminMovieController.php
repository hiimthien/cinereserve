<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminMovieRequest;
use App\Http\Resources\MovieResource;
use App\Services\MovieService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminMovieController extends Controller
{
    public function __construct(
        protected MovieService $movieService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'status', 'genre', 'city', 'cinema_id', 'date', 'room_type']);
        $perPage = (int) $request->input('per_page', 8);

        $movies = $this->movieService->getPaginatedMovies($filters, $perPage);

        return response()->json([
            'success' => true,
            'data' => MovieResource::collection($movies->items()),
            'meta' => [
                'current_page' => $movies->currentPage(),
                'last_page' => $movies->lastPage(),
                'per_page' => $movies->perPage(),
                'total' => $movies->total(),
            ],
            'pagination' => [
                'current_page' => $movies->currentPage(),
                'last_page' => $movies->lastPage(),
                'per_page' => $movies->perPage(),
                'total' => $movies->total(),
            ],
        ]);
    }

    public function store(AdminMovieRequest $request): JsonResponse
    {
        $movie = $this->movieService->createMovie($request->validated());

        return response()->json([
            'success' => true,
            'message' => "Thêm phim [{$movie->title}] thành công!",
            'data' => new MovieResource($movie),
        ], 201);
    }

    public function update(AdminMovieRequest $request, int $id): JsonResponse
    {
        $movie = $this->movieService->updateMovie($id, $request->validated());

        return response()->json([
            'success' => true,
            'message' => "Cập nhật phim [{$movie->title}] thành công!",
            'data' => new MovieResource($movie),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->movieService->deleteMovie($id);

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa phim khỏi danh mục.',
        ]);
    }
}
