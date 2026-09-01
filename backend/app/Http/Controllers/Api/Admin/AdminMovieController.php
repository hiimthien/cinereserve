<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminMovieRequest;
use App\Http\Resources\MovieResource;
use App\Models\Movie;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminMovieController extends Controller
{
    /**
     * Danh sách phim trong quản trị kèm Phân Trang & Bộ Lọc
     */
    public function index(Request $request): JsonResponse
    {
        $query = Movie::query();

        if ($request->filled('search')) {
            $s = $request->input('search');
            $query->where(function ($q) use ($s) {
                $q->where('title', 'like', "%{$s}%")
                  ->orWhere('original_title', 'like', "%{$s}%")
                  ->orWhere('director', 'like', "%{$s}%")
                  ->orWhere('cast', 'like', "%{$s}%");
            });
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        $perPage = (int) $request->input('per_page', 8);
        $movies = $query->orderBy('id', 'desc')->paginate($perPage);

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

    /**
     * Thêm phim mới
     */
    public function store(AdminMovieRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $duration = $validated['duration'] ?? $validated['duration_minutes'] ?? 120;

        $slug = Str::slug($validated['title']);
        $count = Movie::where('slug', 'like', "{$slug}%")->count();
        if ($count > 0) {
            $slug .= '-' . ($count + 1);
        }

        $movie = Movie::create(array_merge($validated, [
            'slug' => $slug,
            'duration' => $duration,
            'tmdb_id' => rand(100000, 999999),
        ]));

        return response()->json([
            'success' => true,
            'message' => "Thêm phim [{$movie->title}] thành công!",
            'data' => new MovieResource($movie),
        ], 201);
    }

    /**
     * Cập nhật phim
     */
    public function update(AdminMovieRequest $request, int $id): JsonResponse
    {
        $movie = Movie::findOrFail($id);
        $validated = $request->validated();

        if (isset($validated['duration_minutes']) && !isset($validated['duration'])) {
            $validated['duration'] = $validated['duration_minutes'];
        }

        $movie->update($validated);

        return response()->json([
            'success' => true,
            'message' => "Cập nhật phim [{$movie->title}] thành công!",
            'data' => new MovieResource($movie),
        ]);
    }

    /**
     * Xóa phim
     */
    public function destroy(int $id): JsonResponse
    {
        $movie = Movie::findOrFail($id);
        $title = $movie->title;
        $movie->delete();

        return response()->json([
            'success' => true,
            'message' => "Đã xóa phim [{$title}].",
        ]);
    }
}
