<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Cinema;
use App\Models\Room;
use App\Repositories\Contracts\CinemaRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class CinemaService
{
    public function __construct(
        protected CinemaRepositoryInterface $cinemaRepository
    ) {}

    public function getPaginatedCinemas(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return $this->cinemaRepository->getPaginatedCinemas($filters, $perPage);
    }

    public function getAllCinemas(): Collection
    {
        return $this->cinemaRepository->getAllCinemas(['rooms']);
    }

    public function findCinema(int $id): ?Cinema
    {
        return $this->cinemaRepository->findById($id, ['rooms']);
    }

    public function createCinema(array $data): Cinema
    {
        $cinema = $this->cinemaRepository->create([
            'name' => $data['name'],
            'address' => $data['address'],
            'city' => $data['city'] ?? 'Hồ Chí Minh',
        ]);

        // Auto-create default rooms if specified or create 2 standard rooms by default
        $roomsCount = isset($data['default_rooms_count']) ? (int)$data['default_rooms_count'] : 2;
        for ($i = 1; $i <= $roomsCount; $i++) {
            Room::create([
                'cinema_id' => $cinema->id,
                'name' => "Phòng Chiếu {$i}",
                'room_type' => $i === 1 ? 'IMAX Laser' : '2D Standard',
                'total_seats' => 80,
            ]);
        }

        return $cinema->load(['rooms']);
    }

    public function updateCinema(int $id, array $data): Cinema
    {
        return $this->cinemaRepository->update($id, [
            'name' => $data['name'],
            'address' => $data['address'],
            'city' => $data['city'] ?? 'Hồ Chí Minh',
        ]);
    }

    public function deleteCinema(int $id): bool
    {
        return $this->cinemaRepository->delete($id);
    }
}
