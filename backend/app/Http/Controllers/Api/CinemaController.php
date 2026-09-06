<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CinemaResource;
use App\Services\CinemaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CinemaController extends Controller
{
    public function __construct(
        protected CinemaService $cinemaService
    ) {}

    /**
     * Lấy danh sách rạp theo thành phố / chuỗi rạp (Thin Controller)
     */
    public function index(Request $request): JsonResponse
    {
        $cinemas = $this->cinemaService->getFilteredCinemas($request->all());

        return response()->json([
            'success' => true,
            'data' => CinemaResource::collection($cinemas),
        ]);
    }

    /**
     * Lấy lịch chiếu của 1 Rạp gom nhóm theo từng Phim đang chiếu tại rạp đó (Thin Controller)
     */
    public function showtimes(int $id, Request $request): JsonResponse
    {
        $date = (string) $request->query('date', date('Y-m-d'));
        $data = $this->cinemaService->getCinemaShowtimes($id, $date);

        return response()->json(array_merge(['success' => true], $data));
    }
}
