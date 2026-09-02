<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Cinema;
use App\Models\Movie;
use App\Models\Room;
use App\Models\Showtime;
use App\Repositories\Contracts\ShowtimeRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ShowtimeService
{
    public function __construct(
        protected ShowtimeRepositoryInterface $showtimeRepository
    ) {}

    /**
     * Lấy danh sách phân trang theo bộ lọc
     */
    public function getPaginatedShowtimes(array $filters, int $perPage = 100): LengthAwarePaginator
    {
        return $this->showtimeRepository->getPaginatedShowtimes($filters, $perPage);
    }

    /**
     * Lấy chi tiết suất chiếu kèm quan hệ
     */
    public function findShowtime(int $id): Showtime
    {
        $showtime = $this->showtimeRepository->findById($id);
        if (!$showtime) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException("Không tìm thấy suất chiếu với ID {$id}");
        }
        return $showtime;
    }

    /**
     * Lấy danh sách suất chiếu theo phim
     */
    public function getShowtimesByMovieId(int $movieId): Collection
    {
        return $this->showtimeRepository->getShowtimesByMovieId($movieId);
    }

    /**
     * Tạo 1 suất chiếu đơn lẻ
     */
    public function createSingleShowtime(array $data): Showtime
    {
        $room = Room::findOrFail($data['room_id']);

        $basePrice = (float) $data['base_price'];
        $priceVip = isset($data['price_vip']) ? (float) $data['price_vip'] : ($basePrice + 15000);
        $priceCouple = isset($data['price_couple']) ? (float) $data['price_couple'] : ($basePrice * 2);

        $endTime = $data['end_time'] ?? date('H:i', strtotime($data['start_time'] . ' + 120 minutes'));

        $showtime = $this->showtimeRepository->create([
            'movie_id' => (int) $data['movie_id'],
            'cinema_id' => (int) $room->cinema_id,
            'room_id' => (int) $room->id,
            'show_date' => $data['show_date'],
            'start_time' => $data['start_time'],
            'end_time' => $endTime,
            'base_price' => $basePrice,
            'price_vip' => $priceVip,
            'price_couple' => $priceCouple,
            'format' => $data['format'],
            'status' => $data['status'] ?? 'scheduled',
        ]);

        return $showtime->load(['movie', 'cinema', 'room']);
    }

    /**
     * Tạo suất chiếu hàng loạt (Batch multi-cinemas, multi-dates, multi-slots)
     */
    public function generateBatchShowtimes(array $data): array
    {
        $movie = Movie::findOrFail($data['movie_id']);
        $startDate = Carbon::parse($data['start_date']);
        $daysCount = (int) $data['days_count'];
        $timeSlots = (array) $data['time_slots'];
        $basePrice = (float) $data['base_price'];
        $priceVip = isset($data['price_vip']) ? (float) $data['price_vip'] : ($basePrice + 15000);
        $priceCouple = isset($data['price_couple']) ? (float) $data['price_couple'] : ($basePrice * 2);
        $format = (string) $data['format'];
        $status = (string) ($data['status'] ?? 'scheduled');

        $cinemas = Cinema::with('rooms')->whereIn('id', $data['cinema_ids'])->get();
        $createdCount = 0;

        DB::transaction(function () use ($cinemas, $movie, $startDate, $daysCount, $timeSlots, $basePrice, $priceVip, $priceCouple, $format, $status, &$createdCount) {
            for ($d = 0; $d < $daysCount; $d++) {
                $showDate = $startDate->copy()->addDays($d)->toDateString();

                foreach ($cinemas as $cinema) {
                    $room = $cinema->rooms->first();
                    if (!$room) continue;

                    foreach ($timeSlots as $slotTime) {
                        $this->showtimeRepository->updateOrCreate(
                            [
                                'movie_id' => $movie->id,
                                'cinema_id' => $cinema->id,
                                'room_id' => $room->id,
                                'show_date' => $showDate,
                                'start_time' => $slotTime,
                            ],
                            [
                                'end_time' => date('H:i', strtotime($slotTime . ' + 120 minutes')),
                                'base_price' => $basePrice,
                                'price_vip' => $priceVip,
                                'price_couple' => $priceCouple,
                                'format' => $format,
                                'status' => $status,
                            ]
                        );
                        $createdCount++;
                    }
                }
            }
        });

        return [
            'created_count' => $createdCount,
            'movie_title' => $movie->title,
            'cinemas_count' => count($data['cinema_ids']),
        ];
    }

    /**
     * Cập nhật thông tin suất chiếu
     */
    public function updateShowtime(int $id, array $data): Showtime
    {
        if (isset($data['room_id'])) {
            $room = Room::findOrFail($data['room_id']);
            $data['cinema_id'] = $room->cinema_id;
        }

        return $this->showtimeRepository->update($id, $data);
    }

    /**
     * Xóa suất chiếu
     */
    public function deleteShowtime(int $id): bool
    {
        return $this->showtimeRepository->delete($id);
    }
}
