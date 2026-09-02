<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminSeatMatrixRequest;
use App\Services\RoomService;
use Illuminate\Http\JsonResponse;

class AdminRoomController extends Controller
{
    public function __construct(
        protected RoomService $roomService
    ) {}

    /**
     * Danh sách cụm rạp và phòng chiếu
     */
    public function index(): JsonResponse
    {
        $cinemas = $this->roomService->getAllRooms();

        return response()->json([
            'success' => true,
            'data' => $cinemas,
        ]);
    }

    /**
     * Lấy sơ đồ ghế chi tiết của phòng chiếu
     */
    public function getSeats(int $roomId): JsonResponse
    {
        $room = $this->roomService->getRoomDetails($roomId);

        return response()->json([
            'success' => true,
            'data' => [
                'room' => $room,
                'seats' => $room->seats->sortBy(['row', 'number'])->values(),
            ],
        ]);
    }

    /**
     * Cấu hình lại ma trận ghế (Standard, VIP, Couple) cho phòng chiếu
     */
    public function updateSeatMatrix(AdminSeatMatrixRequest $request, int $roomId): JsonResponse
    {
        $room = $this->roomService->updateSeatMatrix($roomId, $request->validated());

        return response()->json([
            'success' => true,
            'message' => "Đã cập nhật sơ đồ ghế phòng chiếu [{$room->name}] thành công!",
            'data' => [
                'room' => $room,
                'seats_count' => $room->seats()->count(),
            ],
        ]);
    }
}
