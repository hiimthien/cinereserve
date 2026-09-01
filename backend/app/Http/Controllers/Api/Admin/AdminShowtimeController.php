<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminShowtimeRequest;
use App\Http\Resources\ShowtimeResource;
use App\Services\ShowtimeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminShowtimeController extends Controller
{
    public function __construct(
        protected ShowtimeService $showtimeService
    ) {}

    /**
     * Danh sách suất chiếu trong quản trị kèm Phân Trang & Bộ Lọc
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['date', 'cinema_id', 'movie_id', 'status']);
        $perPage = (int) $request->input('per_page', 100);

        $showtimes = $this->showtimeService->getPaginatedShowtimes($filters, $perPage);

        return response()->json([
            'success' => true,
            'data' => ShowtimeResource::collection($showtimes->items()),
            'pagination' => [
                'current_page' => $showtimes->currentPage(),
                'last_page' => $showtimes->lastPage(),
                'per_page' => $showtimes->perPage(),
                'total' => $showtimes->total(),
            ],
            'meta' => [
                'current_page' => $showtimes->currentPage(),
                'last_page' => $showtimes->lastPage(),
                'per_page' => $showtimes->perPage(),
                'total' => $showtimes->total(),
            ],
        ]);
    }

    /**
     * Tạo 1 suất chiếu đơn lẻ
     */
    public function store(AdminShowtimeRequest $request): JsonResponse
    {
        $showtime = $this->showtimeService->createSingleShowtime($request->validated());

        return response()->json([
            'success' => true,
            'message' => "Tạo suất chiếu {$showtime->start_time} ngày {$showtime->show_date} thành công!",
            'data' => new ShowtimeResource($showtime),
        ], 201);
    }

    /**
     * Tạo suất chiếu hàng loạt (Batch Generate cho nhiều rạp, nhiều ngày, nhiều khung giờ)
     */
    public function batchStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'movie_id' => 'required|exists:movies,id',
            'cinema_ids' => 'required|array|min:1',
            'cinema_ids.*' => 'exists:cinemas,id',
            'start_date' => 'required|date',
            'days_count' => 'required|integer|min:1|max:30',
            'time_slots' => 'required|array|min:1',
            'time_slots.*' => 'string',
            'base_price' => 'required|numeric|min:45000',
            'price_vip' => 'nullable|numeric|min:45000',
            'price_couple' => 'nullable|numeric|min:45000',
            'format' => 'required|string',
            'status' => 'nullable|string|in:scheduled,early_premiere',
        ]);

        $result = $this->showtimeService->generateBatchShowtimes($validated);

        return response()->json([
            'success' => true,
            'message' => "Đã tạo thành công {$result['created_count']} suất chiếu cho phim [{$result['movie_title']}] trên {$result['cinemas_count']} cụm rạp!",
        ], 201);
    }

    /**
     * Cập nhật thông tin 1 suất chiếu
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'room_id' => 'sometimes|exists:rooms,id',
            'show_date' => 'sometimes|date',
            'start_time' => 'sometimes|string',
            'end_time' => 'nullable|string',
            'base_price' => 'sometimes|numeric|min:45000',
            'price_vip' => 'nullable|numeric|min:45000',
            'price_couple' => 'nullable|numeric|min:45000',
            'format' => 'sometimes|string',
            'status' => 'sometimes|string|in:scheduled,early_premiere,cancelled',
        ]);

        $showtime = $this->showtimeService->updateShowtime($id, $validated);

        return response()->json([
            'success' => true,
            'message' => "Cập nhật suất chiếu thành công!",
            'data' => new ShowtimeResource($showtime),
        ]);
    }

    /**
     * Xóa suất chiếu
     */
    public function destroy(int $id): JsonResponse
    {
        $this->showtimeService->deleteShowtime($id);

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa suất chiếu thành công.',
        ]);
    }
}
