<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MovieFilterRequest;
use App\Http\Resources\MovieResource;
use App\Services\MovieService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MovieController extends Controller
{
    public function __construct(
        protected MovieService $movieService
    ) {}

    /**
     * Lấy danh sách phim theo bộ lọc chuẩn RESTful
     */
    public function index(MovieFilterRequest $request): AnonymousResourceCollection
    {
        $movies = $this->movieService->getFilteredMovies($request->validated());

        return MovieResource::collection($movies);
    }

    /**
     * Chi tiết phim theo ID hoặc Slug
     */
    public function show(string $idOrSlug): MovieResource
    {
        $movie = $this->movieService->getMovieDetail($idOrSlug);

        return new MovieResource($movie);
    }
}
