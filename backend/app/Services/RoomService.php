<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Room;
use App\Repositories\Contracts\RoomRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class RoomService
{
    public function __construct(
        protected RoomRepositoryInterface $roomRepository
    ) {}

    public function getAllRooms(): Collection
    {
        return $this->roomRepository->getAllRoomsWithCinemas();
    }

    public function getRoomDetails(int $id): ?Room
    {
        return $this->roomRepository->findById($id);
    }

    public function updateSeatMatrix(int $roomId, array $config): Room
    {
        $totalRows = (int) $config['total_rows'];
        $seatsPerRow = (int) $config['seats_per_row'];
        $vipRows = $config['vip_rows'] ?? ['E', 'F', 'G'];
        $coupleRows = $config['couple_rows'] ?? ['J'];

        return $this->roomRepository->updateSeatMatrix($roomId, $totalRows, $seatsPerRow, $vipRows, $coupleRows);
    }
}
