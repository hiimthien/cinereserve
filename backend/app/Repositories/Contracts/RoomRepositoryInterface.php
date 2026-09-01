<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Room;
use Illuminate\Database\Eloquent\Collection;

interface RoomRepositoryInterface
{
    /**
     * Lấy danh sách rạp và phòng chiếu kèm sơ đồ ghế
     */
    public function getAllRoomsWithCinemas(): Collection;

    /**
     * Tìm phòng chiếu theo ID
     */
    public function findById(int $id, array $relations = ['cinema', 'seats']): ?Room;

    /**
     * Cập nhật ma trận ghế cho phòng chiếu
     */
    public function updateSeatMatrix(int $roomId, int $totalRows, int $seatsPerRow, array $vipRows, array $coupleRows): Room;
}
