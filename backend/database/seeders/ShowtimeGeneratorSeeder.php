<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Cinema;
use App\Models\Movie;
use App\Models\Room;
use App\Models\Showtime;
use Illuminate\Database\Seeder;

class ShowtimeGeneratorSeeder extends Seeder
{
    public function run(): void
    {
        $movies = Movie::all();
        $cinemas = Cinema::with('rooms')->get();

        if ($movies->isEmpty() || $cinemas->isEmpty()) {
            return;
        }

        // Tạo lịch chiếu cho 14 ngày liên tiếp từ hôm nay
        $dates = [];
        for ($i = 0; $i < 14; $i++) {
            $dates[] = date('Y-m-d', strtotime("+{$i} days"));
        }

        // Khung giờ chiếu
        $timeSlots = [
            ['start' => '09:00', 'end' => '11:15', 'price' => 85000],
            ['start' => '11:45', 'end' => '14:00', 'price' => 95000],
            ['start' => '14:30', 'end' => '16:45', 'price' => 105000],
            ['start' => '17:15', 'end' => '19:30', 'price' => 115000],
            ['start' => '20:00', 'end' => '22:15', 'price' => 125000],
            ['start' => '22:45', 'end' => '01:00', 'price' => 95000],
        ];

        // Đảm bảo mỗi phim Đang Chiếu xuất hiện mỗi ngày ở nhiều rạp
        foreach ($movies as $movieIndex => $movie) {
            foreach ($dates as $dayIndex => $showDate) {
                // Với mỗi ngày, chọn 4-6 rạp khác nhau để chiếu phim này
                $selectedCinemas = $cinemas->shuffle()->take(6);

                foreach ($selectedCinemas as $cinema) {
                    $room = $cinema->rooms->first() ?? Room::create([
                        'cinema_id' => $cinema->id,
                        'name' => 'Phòng Chiếu 1',
                        'room_type' => '2D Standard',
                        'total_seats' => 132,
                        'rows' => ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K', 'L'],
                    ]);

                    $priceMultiplier = match ($room->room_type) {
                        'IMAX Laser', 'IMAX' => 1.5,
                        'Gold Class VIP', 'First Class VIP', 'L\'amour Bed' => 2.0,
                        'Super Plex Laser', 'Dolby Atmos', '3D Dolby Atmos' => 1.25,
                        default => 1.0,
                    };

                    // Mỗi rạp có 2-3 suất chiếu cho phim này trong ngày
                    $cinemaSlots = array_slice($timeSlots, ($movieIndex + $dayIndex) % 3, 3);

                    foreach ($cinemaSlots as $slot) {
                        $finalPrice = round(($slot['price'] * $priceMultiplier) / 5000) * 5000;

                        Showtime::updateOrCreate(
                            [
                                'movie_id' => $movie->id,
                                'cinema_id' => $cinema->id,
                                'room_id' => $room->id,
                                'show_date' => $showDate,
                                'start_time' => $slot['start'],
                            ],
                            [
                                'end_time' => $slot['end'],
                                'base_price' => $finalPrice,
                            ]
                        );
                    }
                }
            }
        }
    }
}
