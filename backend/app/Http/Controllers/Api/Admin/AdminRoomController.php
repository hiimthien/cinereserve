<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\RoomService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
    public function updateSeatMatrix(Request $request, int $roomId): JsonResponse
    {
        $validated = $request->validate([
            'total_rows' => 'required|integer|min:4|max:15',
            'seats_per_row' => 'required|integer|min:6|max:20',
            'vip_rows' => 'nullable|array',
            'couple_rows' => 'nullable|array',
        ]);

        $room = $this->roomService->updateSeatMatrix($roomId, $validated);

        return response()->json([
            'success' => true,
            'message' => "Cấu hình ma trận ghế thành công cho phòng [{$room->name}]!",
            'data' => $room,
        ]);
    }
}
