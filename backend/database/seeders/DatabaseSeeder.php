<?php

namespace Database\Seeders;

use App\Models\Cinema;
use App\Models\Movie;
use App\Models\Room;
use App\Models\Seat;
use App\Models\Showtime;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Admin / Default User
        User::firstOrCreate(
            ['email' => 'thiencao.work@gmail.com'],
            [
                'name' => 'Cao Luong Thien',
                'password' => bcrypt('password123'),
            ]
        );

        // 2. Create Movie (Dune: Part Two)
        $movie = Movie::firstOrCreate(
            ['slug' => 'dune-part-two'],
            [
                'title' => 'Dune: Part Two',
                'original_title' => 'Dune: Part Two (2024)',
                'duration' => 166,
                'release_date' => '2024-03-01',
                'poster_url' => 'https://images.unsplash.com/photo-1534447677768-be436bb09401?w=600&auto=format&fit=crop&q=80',
                'backdrop_url' => 'https://images.unsplash.com/photo-1509198397868-475647b2a1e5?w=1600&auto=format&fit=crop&q=80',
                'trailer_url' => 'https://www.youtube.com/watch?v=Way9Dexny3w',
                'rating' => 8.6,
                'genre' => ['Sci-Fi', 'Adventure', 'Action', 'IMAX'],
                'description' => 'Paul Atreides unites with Chani and the Fremen while seeking revenge against the conspirators who destroyed his family. Facing a choice between the love of his life and the fate of the universe, he endeavors to prevent a terrible future only he can foresee.',
                'director' => 'Denis Villeneuve',
                'cast' => ['Timothée Chalamet', 'Zendaya', 'Rebecca Ferguson', 'Javier Bardem', 'Austin Butler'],
                'status' => 'now_showing',
            ]
        );

        // 3. Create Cinemas
        $cinema1 = Cinema::firstOrCreate(
            ['name' => 'CineReserve IMAX - Landmark 81'],
            [
                'address' => 'Tầng B1, Vincom Center Landmark 81, P. 22, Bình Thạnh',
                'city' => 'Ho Chi Minh City',
            ]
        );

        $cinema2 = Cinema::firstOrCreate(
            ['name' => 'CineReserve Thủ Đức - Moonlight'],
            [
                'address' => 'Đặng Văn Bi, P. Trường Thọ, TP. Thủ Đức',
                'city' => 'Ho Chi Minh City',
            ]
        );

        // 4. Create Rooms
        $room1 = Room::firstOrCreate(
            ['cinema_id' => $cinema1->id, 'name' => 'Hall 1 (IMAX Laser)'],
            [
                'room_type' => 'IMAX Laser',
                'total_seats' => 120,
            ]
        );

        $room2 = Room::firstOrCreate(
            ['cinema_id' => $cinema1->id, 'name' => 'Hall 2 (VIP Luxe)'],
            [
                'room_type' => 'VIP Gold Class',
                'total_seats' => 60,
            ]
        );

        // 5. Create Seats Matrix for Room 1 (Rows A to J) if not already created
        if (Seat::where('room_id', $room1->id)->count() === 0) {
            $rows = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J'];
            foreach ($rows as $row) {
                $isCoupleRow = ($row === 'J');
                $cols = $isCoupleRow ? 6 : 14;

                for ($col = 1; $col <= $cols; $col++) {
                    $type = 'standard';
                    if ($isCoupleRow) {
                        $type = 'couple';
                    } elseif (in_array($row, ['E', 'F', 'G']) && $col >= 4 && $col <= 11) {
                        $type = 'vip';
                    }

                    Seat::create([
                        'room_id' => $room1->id,
                        'row' => $row,
                        'number' => $col,
                        'type' => $type,
                    ]);
                }
            }
        }

        // 6. Create Showtimes
        $dates = [
            date('Y-m-d'),
            date('Y-m-d', strtotime('+1 day')),
            date('Y-m-d', strtotime('+2 days')),
        ];

        foreach ($dates as $showDate) {
            Showtime::firstOrCreate(
                [
                    'movie_id' => $movie->id,
                    'room_id' => $room1->id,
                    'show_date' => $showDate,
                    'start_time' => '18:30',
                ],
                [
                    'cinema_id' => $cinema1->id,
                    'end_time' => '21:16',
                    'base_price' => 12.00,
                ]
            );

            Showtime::firstOrCreate(
                [
                    'movie_id' => $movie->id,
                    'room_id' => $room1->id,
                    'show_date' => $showDate,
                    'start_time' => '20:45',
                ],
                [
                    'cinema_id' => $cinema1->id,
                    'end_time' => '23:31',
                    'base_price' => 14.00,
                ]
            );

            Showtime::firstOrCreate(
                [
                    'movie_id' => $movie->id,
                    'room_id' => $room1->id,
                    'show_date' => $showDate,
                    'start_time' => '22:30',
                ],
                [
                    'cinema_id' => $cinema1->id,
                    'end_time' => '01:16',
                    'base_price' => 10.00,
                ]
            );
        }
    }
}
