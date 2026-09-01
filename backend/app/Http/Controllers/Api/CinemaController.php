<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CinemaResource;
use App\Http\Resources\MovieResource;
use App\Http\Resources\ShowtimeResource;
use App\Models\Cinema;
use App\Models\Showtime;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CinemaController extends Controller
{
    /**
     * Lấy danh sách rạp theo thành phố / chuỗi rạp
     */
    public function index(Request $request): JsonResponse
    {
        $query = Cinema::with('rooms');

        if ($request->filled('city') && $request->query('city') !== 'Tất cả') {
            $query->where('city', 'like', "%{$request->query('city')}%");
        }

        if ($request->filled('chain') && $request->query('chain') !== 'Tất cả') {
            $query->where('name', 'like', "%{$request->query('chain')}%");
        }

        if ($request->filled('search')) {
            $search = (string) $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        $cinemas = $query->get();

        return response()->json([
            'success' => true,
            'data' => CinemaResource::collection($cinemas),
        ]);
    }

    /**
     * Lấy lịch chiếu của 1 Rạp gom nhóm theo từng Phim đang chiếu tại rạp đó
     */
    public function showtimes(int $id, Request $request): JsonResponse
    {
        $cinema = Cinema::with('rooms')->findOrFail($id);

        $date = $request->query('date', date('Y-m-d'));

        // Lấy tất cả suất chiếu của rạp trong ngày đó
        $showtimes = Showtime::with(['movie', 'room'])
            ->where('cinema_id', $id)
            ->where('show_date', $date)
            ->orderBy('start_time')
            ->get();

        // Gom nhóm theo từng bộ phim
        $movieGroups = [];
        foreach ($showtimes as $st) {
            if (!$st->movie) continue;

            $movieId = $st->movie->id;
            if (!isset($movieGroups[$movieId])) {
                $movieGroups[$movieId] = [
                    'movie' => new MovieResource($st->movie),
                    'showtimes' => [],
                ];
            }

            $movieGroups[$movieId]['showtimes'][] = new ShowtimeResource($st);
        }

        return response()->json([
            'success' => true,
            'cinema' => new CinemaResource($cinema),
            'date' => $date,
            'movies' => array_values($movieGroups),
        ]);
    }
}
