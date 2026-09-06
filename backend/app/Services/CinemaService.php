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

    public function getFilteredCinemas(array $filters = []): Collection
    {
        return $this->cinemaRepository->getFilteredCinemas($filters, ['rooms']);
    }

    public function getCinemaShowtimes(int $cinemaId, string $date): array
    {
        $cacheKey = "cinemas:showtimes:cinema_{$cinemaId}:date_{$date}";

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 60, function () use ($cinemaId, $date) {
            $cinema = $this->cinemaRepository->findById($cinemaId, ['rooms']);

            if (! $cinema) {
                abort(404, 'Không tìm thấy rạp chiếu.');
            }

            $showtimes = \App\Models\Showtime::with(['movie', 'room'])
                ->where('cinema_id', $cinemaId)
                ->where('show_date', $date)
                ->orderBy('start_time')
                ->get();

            $movieGroups = [];
            foreach ($showtimes as $st) {
                if (! $st->movie) {
                    continue;
                }

                $movieId = $st->movie->id;
                if (! isset($movieGroups[$movieId])) {
                    $movieGroups[$movieId] = [
                        'movie' => new \App\Http\Resources\MovieResource($st->movie),
                        'showtimes' => [],
                    ];
                }

                $movieGroups[$movieId]['showtimes'][] = new \App\Http\Resources\ShowtimeResource($st);
            }

            return [
                'cinema' => new \App\Http\Resources\CinemaResource($cinema),
                'date' => $date,
                'movies' => array_values($movieGroups),
            ];
        });
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
        $roomsCount = isset($data['default_rooms_count']) ? (int) $data['default_rooms_count'] : 2;
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
