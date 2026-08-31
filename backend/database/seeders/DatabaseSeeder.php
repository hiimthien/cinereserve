<?php

namespace Database\Seeders;

use App\Models\Cinema;
use App\Models\Movie;
use App\Models\Room;
use App\Models\Seat;
use App\Models\Showtime;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Admin & Test Users
        User::firstOrCreate(
            ['email' => 'thiencao.work@gmail.com'],
            [
                'name' => 'Cao Luong Thien',
                'password' => bcrypt('password123'),
            ]
        );

        // 2. Rich Movie Catalog (Blockbuster Movies)
        $moviesData = [
            [
                'title' => 'Dune: Part Two',
                'original_title' => 'Dune: Part Two (2024)',
                'slug' => 'dune-part-two',
                'duration' => 166,
                'release_date' => '2024-03-01',
                'poster_url' => 'https://images.unsplash.com/photo-1534447677768-be436bb09401?w=600&auto=format&fit=crop&q=80',
                'backdrop_url' => 'https://images.unsplash.com/photo-1509198397868-475647b2a1e5?w=1600&auto=format&fit=crop&q=80',
                'trailer_url' => 'https://www.youtube.com/embed/Way9Dexny3w',
                'rating' => 8.6,
                'genre' => ['Sci-Fi', 'Adventure', 'Action', 'IMAX'],
                'description' => 'Paul Atreides hợp lực cùng Chani và người Fremen trên hành trình trả thù những kẻ đã hủy hoại gia đình mình, đồng thời đối mặt với lựa chọn giữa tình yêu và số phận vũ trụ.',
                'director' => 'Denis Villeneuve',
                'cast' => ['Timothée Chalamet', 'Zendaya', 'Rebecca Ferguson', 'Javier Bardem', 'Austin Butler'],
                'status' => 'now_showing',
            ],
            [
                'title' => 'Oppenheimer',
                'original_title' => 'Oppenheimer (2023)',
                'slug' => 'oppenheimer',
                'duration' => 180,
                'release_date' => '2023-07-21',
                'poster_url' => 'https://images.unsplash.com/photo-1440404653325-ab127d49abc1?w=600&auto=format&fit=crop&q=80',
                'backdrop_url' => 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?w=1600&auto=format&fit=crop&q=80',
                'trailer_url' => 'https://www.youtube.com/embed/uYPbbksJxIg',
                'rating' => 8.9,
                'genre' => ['Biography', 'Drama', 'History', 'IMAX 70mm'],
                'description' => 'Câu chuyện về nhà vật lý lý thuyết J. Robert Oppenheimer, người lãnh đạo Dự án Manhattan phát triển bom nguyên tử đầu tiên trong Thế chiến II.',
                'director' => 'Christopher Nolan',
                'cast' => ['Cillian Murphy', 'Emily Blunt', 'Matt Damon', 'Robert Downey Jr.'],
                'status' => 'now_showing',
            ],
            [
                'title' => 'Deadpool & Wolverine',
                'original_title' => 'Deadpool & Wolverine (2024)',
                'slug' => 'deadpool-and-wolverine',
                'duration' => 128,
                'release_date' => '2024-07-26',
                'poster_url' => 'https://images.unsplash.com/photo-1568876694728-451bbf694b83?w=600&auto=format&fit=crop&q=80',
                'backdrop_url' => 'https://images.unsplash.com/photo-1578632767115-351597cf2477?w=1600&auto=format&fit=crop&q=80',
                'trailer_url' => 'https://www.youtube.com/embed/73_1biulkYk',
                'rating' => 8.0,
                'genre' => ['Action', 'Comedy', 'Sci-Fi', 'Marvel'],
                'description' => 'Deadpool và Wolverine bắt tay trong một nhiệm vụ liên vũ trụ đầy hài hước và bùng nổ của Marvel Cinematic Universe.',
                'director' => 'Shawn Levy',
                'cast' => ['Ryan Reynolds', 'Hugh Jackman', 'Emma Corrin', 'Matthew Macfadyen'],
                'status' => 'now_showing',
            ],
            [
                'title' => 'Spider-Man: Across the Spider-Verse',
                'original_title' => 'Spider-Man: Across the Spider-Verse (2023)',
                'slug' => 'spider-man-across-the-spider-verse',
                'duration' => 140,
                'release_date' => '2023-06-02',
                'poster_url' => 'https://images.unsplash.com/photo-1635805737707-575885ab0820?w=600&auto=format&fit=crop&q=80',
                'backdrop_url' => 'https://images.unsplash.com/photo-1607604276583-eef5d076aa5f?w=1600&auto=format&fit=crop&q=80',
                'trailer_url' => 'https://www.youtube.com/embed/cqGjhVJWtEg',
                'rating' => 8.7,
                'genre' => ['Animation', 'Action', 'Adventure', '3D'],
                'description' => 'Miles Morales được phóng qua Đa vũ trụ, nơi cậu chạm trán một đội ngũ Người Nhện mang trọng trách bảo vệ sự tồn vong của thế giới.',
                'director' => 'Joaquim Dos Santos',
                'cast' => ['Shameik Moore', 'Hailee Steinfeld', 'Oscar Isaac', 'Daniel Kaluuya'],
                'status' => 'now_showing',
            ],
            [
                'title' => 'Interstellar (10th Anniversary IMAX)',
                'original_title' => 'Interstellar (2014)',
                'slug' => 'interstellar',
                'duration' => 169,
                'release_date' => '2024-09-27',
                'poster_url' => 'https://images.unsplash.com/photo-1446776811953-b23d57bd21aa?w=600&auto=format&fit=crop&q=80',
                'backdrop_url' => 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?w=1600&auto=format&fit=crop&q=80',
                'trailer_url' => 'https://www.youtube.com/embed/zSWdZVtXT7E',
                'rating' => 8.7,
                'genre' => ['Sci-Fi', 'Drama', 'Adventure', 'IMAX Laser'],
                'description' => 'Khi tương lai của Trái Đất bị đe dọa bởi nạn đói, một nhóm nhà thám hiểm du hành qua lỗ sâu không gian để tìm kiếm ngôi nhà mới cho nhân loại.',
                'director' => 'Christopher Nolan',
                'cast' => ['Matthew McConaughey', 'Anne Hathaway', 'Jessica Chastain', 'Michael Caine'],
                'status' => 'coming_soon',
            ],
            [
                'title' => 'Avatar: Fire and Ash',
                'original_title' => 'Avatar: Fire and Ash (2025)',
                'slug' => 'avatar-fire-and-ash',
                'duration' => 192,
                'release_date' => '2025-12-19',
                'poster_url' => 'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?w=600&auto=format&fit=crop&q=80',
                'backdrop_url' => 'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?w=1600&auto=format&fit=crop&q=80',
                'trailer_url' => 'https://www.youtube.com/embed/d9MyW72ELq0',
                'rating' => 9.0,
                'genre' => ['Sci-Fi', 'Action', 'Fantasy', '3D IMAX'],
                'description' => 'Hành trình khám phá bộ tộc Tro Tàn trên hành tinh Pandora, mở ra những khía cạnh bí ẩn và đen tối hơn của thế giới Na\'vi.',
                'director' => 'James Cameron',
                'cast' => ['Sam Worthington', 'Zoe Saldana', 'Sigourney Weaver', 'Michelle Yeoh'],
                'status' => 'coming_soon',
            ],
        ];

        $createdMovies = [];
        foreach ($moviesData as $mData) {
            $createdMovies[] = Movie::updateOrCreate(
                ['slug' => $mData['slug']],
                $mData
            );
        }

        // 3. Cinema Chains
        $cinema1 = Cinema::updateOrCreate(
            ['name' => 'CineReserve IMAX - Landmark 81'],
            [
                'address' => 'Tầng B1, Vincom Center Landmark 81, P. 22, Bình Thạnh',
                'city' => 'Ho Chi Minh City',
            ]
        );

        $cinema2 = Cinema::updateOrCreate(
            ['name' => 'CineReserve Moonlight - Thủ Đức'],
            [
                'address' => '102 Đặng Văn Bi, P. Trường Thọ, TP. Thủ Đức',
                'city' => 'Ho Chi Minh City',
            ]
        );

        $cinema3 = Cinema::updateOrCreate(
            ['name' => 'CineReserve Gold Class - Crescent Mall'],
            [
                'address' => 'Tầng 5, Crescent Mall, Phú Mỹ Hưng, Quận 7',
                'city' => 'Ho Chi Minh City',
            ]
        );

        // 4. Rooms
        $room1 = Room::firstOrCreate(
            ['cinema_id' => $cinema1->id, 'name' => 'Hall 1 (IMAX Laser)'],
            ['room_type' => 'IMAX Laser', 'total_seats' => 118]
        );

        $room2 = Room::firstOrCreate(
            ['cinema_id' => $cinema1->id, 'name' => 'Hall 2 (Dolby Atmos)'],
            ['room_type' => 'Dolby Atmos', 'total_seats' => 90]
        );

        $room3 = Room::firstOrCreate(
            ['cinema_id' => $cinema2->id, 'name' => 'Hall 1 (VIP Luxe)'],
            ['room_type' => 'VIP Gold Class', 'total_seats' => 60]
        );

        $room4 = Room::firstOrCreate(
            ['cinema_id' => $cinema3->id, 'name' => 'Hall 1 (Starium Laser)'],
            ['room_type' => 'Starium 2D', 'total_seats' => 120]
        );

        // 5. Create Seats Matrix for Room 1 if needed
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

        // 6. Showtimes for All Now Showing Movies
        $dates = [
            date('Y-m-d'),
            date('Y-m-d', strtotime('+1 day')),
            date('Y-m-d', strtotime('+2 days')),
            date('Y-m-d', strtotime('+3 days')),
            date('Y-m-d', strtotime('+4 days')),
        ];

        $timeSlots = [
            ['start' => '10:15', 'end' => '12:45', 'price' => 9.50],
            ['start' => '13:30', 'end' => '16:00', 'price' => 11.00],
            ['start' => '16:45', 'end' => '19:15', 'price' => 12.50],
            ['start' => '19:30', 'end' => '22:00', 'price' => 14.50],
            ['start' => '22:15', 'end' => '00:45', 'price' => 11.50],
        ];

        foreach ($createdMovies as $mov) {
            if ($mov->status !== 'now_showing') continue;

            foreach ($dates as $idx => $d) {
                // Generate 3 showtimes per date per movie
                $selectedSlots = array_slice($timeSlots, $idx % 3, 3);
                foreach ($selectedSlots as $slot) {
                    Showtime::firstOrCreate(
                        [
                            'movie_id' => $mov->id,
                            'room_id' => $room1->id,
                            'show_date' => $d,
                            'start_time' => $slot['start'],
                        ],
                        [
                            'cinema_id' => $cinema1->id,
                            'end_time' => $slot['end'],
                            'base_price' => $slot['price'],
                        ]
                    );
                }
            }
        }
    }
}
