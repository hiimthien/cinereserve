<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminCinemaRequest;
use App\Http\Resources\CinemaResource;
use App\Services\CinemaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminCinemaController extends Controller
{
    public function __construct(
        protected CinemaService $cinemaService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['city', 'search']);
        $perPage = (int) $request->input('per_page', 10);

        $cinemas = $this->cinemaService->getPaginatedCinemas($filters, $perPage);

        return response()->json([
            'success' => true,
            'data' => CinemaResource::collection($cinemas->items()),
            'meta' => [
                'current_page' => $cinemas->currentPage(),
                'last_page' => $cinemas->lastPage(),
                'per_page' => $cinemas->perPage(),
                'total' => $cinemas->total(),
            ],
        ]);
    }

    public function store(AdminCinemaRequest $request): JsonResponse
    {
        $cinema = $this->cinemaService->createCinema($request->validated());

        return response()->json([
            'success' => true,
            'message' => "Đã thêm cụm rạp [{$cinema->name}] thành công!",
            'data' => new CinemaResource($cinema),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $cinema = $this->cinemaService->findCinema($id);

        return response()->json([
            'success' => true,
            'data' => new CinemaResource($cinema),
        ]);
    }

    public function update(AdminCinemaRequest $request, int $id): JsonResponse
    {
        $cinema = $this->cinemaService->updateCinema($id, $request->validated());

        return response()->json([
            'success' => true,
            'message' => "Đã cập nhật thông tin rạp [{$cinema->name}] thành công!",
            'data' => new CinemaResource($cinema),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->cinemaService->deleteCinema($id);

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa cụm rạp thành công khỏi hệ thống.',
        ]);
    }
}
