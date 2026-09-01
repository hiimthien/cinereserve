<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MovieFilterRequest;
use App\Http\Resources\MovieResource;
use App\Models\Movie;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MovieController extends Controller
{
    /**
     * Lấy danh sách phim theo bộ lọc chuẩn RESTful
     */
    public function index(MovieFilterRequest $request): AnonymousResourceCollection
    {
        $movies = Movie::with(['showtimes.cinema', 'showtimes.room'])
            ->filter($request->validated())
            ->orderByDesc('rating')
            ->orderByDesc('release_date')
            ->get();

        return MovieResource::collection($movies);
    }

    /**
     * Chi tiết phim theo ID hoặc Slug
     */
    public function show(string $idOrSlug): MovieResource
    {
        $movie = Movie::with(['showtimes.cinema', 'showtimes.room'])
            ->where('id', is_numeric($idOrSlug) ? (int) $idOrSlug : 0)
            ->orWhere('slug', $idOrSlug)
            ->firstOrFail();

        return new MovieResource($movie);
    }
}
