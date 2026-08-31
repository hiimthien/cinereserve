<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MovieController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $movies = Movie::with(['showtimes.cinema', 'showtimes.room'])
            ->where('status', 'now_showing')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $movies,
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $movie = Movie::with(['showtimes.cinema', 'showtimes.room'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $movie,
        ]);
    }
}
