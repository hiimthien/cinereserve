<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cinema;
use App\Models\Room;
use App\Models\Seat;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminRoomController extends Controller
{
    /**
     * Danh sách cụm rạp và phòng chiếu
     */
    public function index(): JsonResponse
    {
        $cinemas = Cinema::with(['rooms.seats'])->get();

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
        $room = Room::with(['cinema', 'seats'])->findOrFail($roomId);

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
        $request->validate([
            'total_rows' => 'required|integer|min:4|max:15',
            'seats_per_row' => 'required|integer|min:6|max:20',
            'vip_rows' => 'nullable|array', // ['E', 'F', 'G']
            'couple_rows' => 'nullable|array', // ['J']
        ]);

        $room = Room::findOrFail($roomId);
        $totalRows = (int) $request->input('total_rows');
        $seatsPerRow = (int) $request->input('seats_per_row');
        $vipRows = $request->input('vip_rows', ['E', 'F', 'G']);
        $coupleRows = $request->input('couple_rows', ['J']);

        $rowLetters = range('A', 'Z');

        DB::transaction(function () use ($room, $totalRows, $seatsPerRow, $vipRows, $coupleRows, $rowLetters) {
            // Xóa ghế cũ
            Seat::where('room_id', $room->id)->delete();

            $newSeats = [];
            for ($r = 0; $r < $totalRows; $r++) {
                $rowLetter = $rowLetters[$r];
                $isCouple = in_array($rowLetter, $coupleRows);
                $isVip = in_array($rowLetter, $vipRows);

                $numSeats = $isCouple ? (int) floor($seatsPerRow / 2) : $seatsPerRow;

                for ($n = 1; $n <= $numSeats; $n++) {
                    $type = 'standard';
                    if ($isCouple) {
                        $type = 'couple';
                    } elseif ($isVip) {
                        $type = 'vip';
                    }

                    $newSeats[] = [
                        'room_id' => $room->id,
                        'row' => $rowLetter,
                        'number' => $n,
                        'type' => $type,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            Seat::insert($newSeats);
            $room->total_seats = count($newSeats);
            $room->save();
        });

        return response()->json([
            'success' => true,
            'message' => "Cấu hình ma trận ghế thành công cho phòng [{$room->name}]!",
            'data' => Room::with('seats')->find($roomId),
        ]);
    }
}
