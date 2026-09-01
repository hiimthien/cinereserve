<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Cinema;
use App\Models\Room;
use App\Models\Seat;
use Illuminate\Database\Seeder;

class VietnamCinemasSeeder extends Seeder
{
    public function run(): void
    {
        $cinemasData = [
            // ====================================================
            // 🏙️ 1. TP. HỒ CHÍ MINH (12 Cụm Rạp Lớn)
            // ====================================================
            [
                'name' => 'CGV Vincom Landmark 81',
                'address' => 'Tầng B1, Vincom Center Landmark 81, 720A Điện Biên Phủ, P. 22, Bình Thạnh',
                'city' => 'Hồ Chí Minh',
                'rooms' => [
                    ['name' => 'Hall 1 (IMAX Laser)', 'room_type' => 'IMAX Laser', 'total_seats' => 118],
                    ['name' => 'Hall 2 (Gold Class VIP)', 'room_type' => 'Gold Class VIP', 'total_seats' => 60],
                    ['name' => 'Hall 3 (Dolby Atmos)', 'room_type' => 'Dolby Atmos', 'total_seats' => 90],
                ],
            ],
            [
                'name' => 'CGV Sư Vạn Hạnh',
                'address' => 'Tầng 6, Vạn Hạnh Mall, 11 Sư Vạn Hạnh, Phường 12, Quận 10',
                'city' => 'Hồ Chí Minh',
                'rooms' => [
                    ['name' => 'Hall 1 (IMAX)', 'room_type' => 'IMAX', 'total_seats' => 118],
                    ['name' => 'Hall 2 (L\'amour Giường Nằm)', 'room_type' => 'L\'amour Bed', 'total_seats' => 40],
                    ['name' => 'Hall 3 (2D Standard)', 'room_type' => '2D Standard', 'total_seats' => 90],
                ],
            ],
            [
                'name' => 'CGV Crescent Mall',
                'address' => 'Tầng 5, Crescent Mall, 101 Tôn Dật Tiên, Tân Phú, Quận 7',
                'city' => 'Hồ Chí Minh',
                'rooms' => [
                    ['name' => 'Hall 1 (Gold Class VIP)', 'room_type' => 'Gold Class VIP', 'total_seats' => 60],
                    ['name' => 'Hall 2 (Starium Laser)', 'room_type' => 'Starium Laser', 'total_seats' => 118],
                ],
            ],
            [
                'name' => 'CGV SC VivoCity',
                'address' => 'Tầng 5, TTTM SC VivoCity, 1058 Nguyễn Văn Linh, Tân Phong, Quận 7',
                'city' => 'Hồ Chí Minh',
                'rooms' => [
                    ['name' => 'Hall 1 (IMAX Laser)', 'room_type' => 'IMAX Laser', 'total_seats' => 118],
                    ['name' => 'Hall 2 (2D Standard)', 'room_type' => '2D Standard', 'total_seats' => 90],
                ],
            ],
            [
                'name' => 'CGV Hùng Vương Plaza',
                'address' => 'Tầng 7, Hùng Vương Plaza, 126 Hồng Bàng, Phường 12, Quận 5',
                'city' => 'Hồ Chí Minh',
                'rooms' => [
                    ['name' => 'Hall 1 (Gold Class VIP)', 'room_type' => 'Gold Class VIP', 'total_seats' => 60],
                    ['name' => 'Hall 2 (2D Standard)', 'room_type' => '2D Standard', 'total_seats' => 90],
                ],
            ],
            [
                'name' => 'CGV Aeon Mall Tân Phú',
                'address' => 'Tầng 3, Aeon Mall Celadon, 30 Bờ Bao Tân Thắng, Sơn Kỳ, Tân Phú',
                'city' => 'Hồ Chí Minh',
                'rooms' => [
                    ['name' => 'Hall 1 (Starium 2D)', 'room_type' => 'Starium Laser', 'total_seats' => 118],
                    ['name' => 'Hall 2 (2D Standard)', 'room_type' => '2D Standard', 'total_seats' => 90],
                ],
            ],
            [
                'name' => 'Lotte Cinema Nam Sài Gòn',
                'address' => 'Tầng 3, Lotte Mart Nam Sài Gòn, 469 Nguyễn Hữu Thọ, Tân Hưng, Quận 7',
                'city' => 'Hồ Chí Minh',
                'rooms' => [
                    ['name' => 'Screen 1 (Super Plex Laser)', 'room_type' => 'Super Plex Laser', 'total_seats' => 118],
                    ['name' => 'Screen 2 (2D Standard)', 'room_type' => '2D Standard', 'total_seats' => 90],
                ],
            ],
            [
                'name' => 'Lotte Cinema Gò Vấp',
                'address' => 'Tầng 3, Lotte Mart Gò Vấp, 242 Nguyễn Văn Lượng, Phường 10, Gò Vấp',
                'city' => 'Hồ Chí Minh',
                'rooms' => [
                    ['name' => 'Screen 1 (Cine Comfort)', 'room_type' => 'Cine Comfort', 'total_seats' => 90],
                    ['name' => 'Screen 2 (2D Standard)', 'room_type' => '2D Standard', 'total_seats' => 90],
                ],
            ],
            [
                'name' => 'Galaxy Cinema Nguyễn Du',
                'address' => '116 Nguyễn Du, Phường Bến Thành, Quận 1',
                'city' => 'Hồ Chí Minh',
                'rooms' => [
                    ['name' => 'Phòng 1 (Dolby 7.1)', 'room_type' => '2D Dolby 7.1', 'total_seats' => 118],
                    ['name' => 'Phòng 2 (2D Standard)', 'room_type' => '2D Standard', 'total_seats' => 90],
                ],
            ],
            [
                'name' => 'Galaxy Cinema Tân Bình',
                'address' => '246 Nguyễn Hồng Đào, Phường 14, Tân Bình',
                'city' => 'Hồ Chí Minh',
                'rooms' => [
                    ['name' => 'Phòng 1 (3D Atmos)', 'room_type' => '3D Dolby Atmos', 'total_seats' => 118],
                    ['name' => 'Phòng 2 (2D Standard)', 'room_type' => '2D Standard', 'total_seats' => 90],
                ],
            ],
            [
                'name' => 'BHD Star Bitexco Icon 68',
                'address' => 'Tầng 3 & 4, Bitexco Financial Tower, 2 Hải Triều, Bến Nghé, Quận 1',
                'city' => 'Hồ Chí Minh',
                'rooms' => [
                    ['name' => 'Hall 1 (First Class VIP)', 'room_type' => 'First Class VIP', 'total_seats' => 60],
                    ['name' => 'Hall 2 (2D Atmos)', 'room_type' => 'Dolby Atmos', 'total_seats' => 90],
                ],
            ],
            [
                'name' => 'Cinestar Quốc Thanh',
                'address' => '271 Nguyễn Trãi, Phường Nguyễn Cư Trinh, Quận 1',
                'city' => 'Hồ Chí Minh',
                'rooms' => [
                    ['name' => 'Cinema 1 (2D Sound 7.1)', 'room_type' => '2D Cinema Sound', 'total_seats' => 118],
                    ['name' => 'Cinema 2 (2D Standard)', 'room_type' => '2D Standard', 'total_seats' => 90],
                ],
            ],

            // ====================================================
            // 🏛️ 2. THỦ ĐÔ HÀ NỘI (9 Cụm Rạp Lớn)
            // ====================================================
            [
                'name' => 'CGV Vincom Royal City',
                'address' => 'Tầng B2, Vincom Mega Mall Royal City, 72A Nguyễn Trãi, Thanh Xuân',
                'city' => 'Hà Nội',
                'rooms' => [
                    ['name' => 'Hall 1 (IMAX Laser)', 'room_type' => 'IMAX Laser', 'total_seats' => 118],
                    ['name' => 'Hall 2 (Gold Class VIP)', 'room_type' => 'Gold Class VIP', 'total_seats' => 60],
                    ['name' => 'Hall 3 (Dolby Atmos)', 'room_type' => 'Dolby Atmos', 'total_seats' => 90],
                ],
            ],
            [
                'name' => 'CGV Vincom Bà Triệu',
                'address' => 'Tầng 6, Vincom Center Hà Nội, 191 Bà Triệu, Hai Bà Trưng',
                'city' => 'Hà Nội',
                'rooms' => [
                    ['name' => 'Hall 1 (Gold Class VIP)', 'room_type' => 'Gold Class VIP', 'total_seats' => 60],
                    ['name' => 'Hall 2 (2D Standard)', 'room_type' => '2D Standard', 'total_seats' => 90],
                ],
            ],
            [
                'name' => 'CGV Vincom Metropolis Liễu Giai',
                'address' => 'Tầng M3, Vincom Center Metropolis, 29 Liễu Giai, Ba Đình',
                'city' => 'Hà Nội',
                'rooms' => [
                    ['name' => 'Hall 1 (IMAX Laser)', 'room_type' => 'IMAX Laser', 'total_seats' => 118],
                    ['name' => 'Hall 2 (L\'amour Giường Nằm)', 'room_type' => 'L\'amour Bed', 'total_seats' => 40],
                ],
            ],
            [
                'name' => 'CGV Aeon Mall Long Biên',
                'address' => 'Tầng 4, TTTM Aeon Mall Long Biên, 27 Cổ Linh, Long Biên',
                'city' => 'Hà Nội',
                'rooms' => [
                    ['name' => 'Hall 1 (Starium 2D)', 'room_type' => 'Starium Laser', 'total_seats' => 118],
                    ['name' => 'Hall 2 (2D Standard)', 'room_type' => '2D Standard', 'total_seats' => 90],
                ],
            ],
            [
                'name' => 'CGV Aeon Mall Hà Đông',
                'address' => 'Tầng 3, TTTM Aeon Mall Hà Đông, Dương Nội, Hà Đông',
                'city' => 'Hà Nội',
                'rooms' => [
                    ['name' => 'Hall 1 (IMAX Laser)', 'room_type' => 'IMAX Laser', 'total_seats' => 118],
                    ['name' => 'Hall 2 (Gold Class VIP)', 'room_type' => 'Gold Class VIP', 'total_seats' => 60],
                ],
            ],
            [
                'name' => 'Lotte Cinema West Lake Tây Hồ',
                'address' => 'Tầng 4, Lotte Mall West Lake Hanoi, 272 Võ Chí Công, Tây Hồ',
                'city' => 'Hà Nội',
                'rooms' => [
                    ['name' => 'Screen 1 (Super Plex Laser)', 'room_type' => 'Super Plex Laser', 'total_seats' => 118],
                    ['name' => 'Screen 2 (Cine Comfort)', 'room_type' => 'Cine Comfort', 'total_seats' => 90],
                ],
            ],
            [
                'name' => 'Lotte Cinema Landmark Keangnam',
                'address' => 'Tầng 5 & 6, Keangnam Hanoi Landmark Tower, E6 Phạm Hùng, Cầu Giấy',
                'city' => 'Hà Nội',
                'rooms' => [
                    ['name' => 'Screen 1 (Super Plex)', 'room_type' => 'Super Plex Laser', 'total_seats' => 118],
                    ['name' => 'Screen 2 (2D Standard)', 'room_type' => '2D Standard', 'total_seats' => 90],
                ],
            ],
            [
                'name' => 'Beta Cinemas Mỹ Đình',
                'address' => 'Tầng hầm B1, Tòa nhà Golden Palace, Mễ Trì, Nam Từ Liêm',
                'city' => 'Hà Nội',
                'rooms' => [
                    ['name' => 'Hall 1 (2D Standard)', 'room_type' => '2D Standard', 'total_seats' => 90],
                    ['name' => 'Hall 2 (2D Standard)', 'room_type' => '2D Standard', 'total_seats' => 90],
                ],
            ],
            [
                'name' => 'Trung Tâm Chiếu Phim Quốc Gia (NCC)',
                'address' => '87 Láng Hạ, Thành Công, Ba Đình',
                'city' => 'Hà Nội',
                'rooms' => [
                    ['name' => 'Phòng 1 (Màn Ảnh Lớn)', 'room_type' => 'Super Plex Laser', 'total_seats' => 118],
                    ['name' => 'Phòng 2 (2D Standard)', 'room_type' => '2D Standard', 'total_seats' => 90],
                ],
            ],

            // ====================================================
            // 🌊 3. ĐÀ NẴNG (3 Cụm Rạp)
            // ====================================================
            [
                'name' => 'CGV Vincom Plaza Đà Nẵng',
                'address' => 'Tầng 4, TTTM Vincom Plaza Ngô Quyền, 910A Ngô Quyền, Sơn Trà',
                'city' => 'Đà Nẵng',
                'rooms' => [
                    ['name' => 'Hall 1 (IMAX Laser)', 'room_type' => 'IMAX Laser', 'total_seats' => 118],
                    ['name' => 'Hall 2 (Gold Class VIP)', 'room_type' => 'Gold Class VIP', 'total_seats' => 60],
                    ['name' => 'Hall 3 (2D Standard)', 'room_type' => '2D Standard', 'total_seats' => 90],
                ],
            ],
            [
                'name' => 'Lotte Cinema Đà Nẵng',
                'address' => 'Tầng 5, TTTM Lotte Mart Đà Nẵng, 6 Nại Nam, Hòa Cường Bắc, Hải Châu',
                'city' => 'Đà Nẵng',
                'rooms' => [
                    ['name' => 'Screen 1 (2D Dolby)', 'room_type' => '2D Dolby 7.1', 'total_seats' => 118],
                    ['name' => 'Screen 2 (2D Standard)', 'room_type' => '2D Standard', 'total_seats' => 90],
                ],
            ],
            [
                'name' => 'Galaxy Cinema Đà Nẵng',
                'address' => 'Tầng 3, TTTM Coop Mart, 478 Điện Biên Phủ, Thanh Khê',
                'city' => 'Đà Nẵng',
                'rooms' => [
                    ['name' => 'Phòng 1 (2D Dolby Atmos)', 'room_type' => 'Dolby Atmos', 'total_seats' => 90],
                ],
            ],

            // ====================================================
            // ⚓ 4. HẢI PHÒNG (2 Cụm Rạp)
            // ====================================================
            [
                'name' => 'CGV Vincom Imperia Hải Phòng',
                'address' => 'Tầng 4, TTTM Vincom Plaza Imperia, Số 1 Bạch Đằng, Thượng Lý, Hồng Bàng',
                'city' => 'Hải Phòng',
                'rooms' => [
                    ['name' => 'Hall 1 (Gold Class VIP)', 'room_type' => 'Gold Class VIP', 'total_seats' => 60],
                    ['name' => 'Hall 2 (2D Standard)', 'room_type' => '2D Standard', 'total_seats' => 90],
                ],
            ],
            [
                'name' => 'CGV Aeon Mall Lê Chân Hải Phòng',
                'address' => 'Tầng 3, TTTM Aeon Mall Lê Chân, 10 Võ Nguyên Giáp, Kênh Dương, Lê Chân',
                'city' => 'Hải Phòng',
                'rooms' => [
                    ['name' => 'Hall 1 (IMAX Laser)', 'room_type' => 'IMAX Laser', 'total_seats' => 118],
                    ['name' => 'Hall 2 (2D Standard)', 'room_type' => '2D Standard', 'total_seats' => 90],
                ],
            ],

            // ====================================================
            // 🌾 5. CẦN THƠ (2 Cụm Rạp)
            // ====================================================
            [
                'name' => 'CGV Vincom Hùng Vương Cần Thơ',
                'address' => 'Tầng 5, TTTM Vincom Plaza Hùng Vương, 2 Hùng Vương, Thới Bình, Ninh Kiều',
                'city' => 'Cần Thơ',
                'rooms' => [
                    ['name' => 'Hall 1 (2D Dolby 7.1)', 'room_type' => '2D Dolby 7.1', 'total_seats' => 118],
                    ['name' => 'Hall 2 (2D Standard)', 'room_type' => '2D Standard', 'total_seats' => 90],
                ],
            ],
            [
                'name' => 'Lotte Cinema Ninh Kiều Cần Thơ',
                'address' => 'Tầng 3, TTTM Lotte Mart Cần Thơ, 84 Mậu Thân, An Hòa, Ninh Kiều',
                'city' => 'Cần Thơ',
                'rooms' => [
                    ['name' => 'Screen 1 (2D Standard)', 'room_type' => '2D Standard', 'total_seats' => 90],
                ],
            ],

            // ====================================================
            // 🏭 6. BÌNH DƯƠNG & ĐỒNG NAI (3 Cụm Rạp)
            // ====================================================
            [
                'name' => 'CGV Aeon Canary Bình Dương',
                'address' => 'Tầng 2, Aeon Mall Bình Dương Canary, Đại lộ Bình Dương, Thuận An',
                'city' => 'Bình Dương',
                'rooms' => [
                    ['name' => 'Hall 1 (Starium Laser)', 'room_type' => 'Starium Laser', 'total_seats' => 118],
                    ['name' => 'Hall 2 (2D Standard)', 'room_type' => '2D Standard', 'total_seats' => 90],
                ],
            ],
            [
                'name' => 'CGV Big C Đồng Nai',
                'address' => 'Tầng 2, Big C Đồng Nai, Khu phố 1, Long Bình Tân, Biên Hòa',
                'city' => 'Đồng Nai',
                'rooms' => [
                    ['name' => 'Hall 1 (2D Standard)', 'room_type' => '2D Standard', 'total_seats' => 90],
                ],
            ],
            [
                'name' => 'Lotte Cinema Biên Hòa',
                'address' => 'Tầng 5, TTTM Vincom Plaza Biên Hòa, 1096 Phạm Văn Thuận, Tân Mai',
                'city' => 'Đồng Nai',
                'rooms' => [
                    ['name' => 'Screen 1 (Cine Comfort)', 'room_type' => 'Cine Comfort', 'total_seats' => 90],
                ],
            ],

            // ====================================================
            // 🏖️ 7. VŨNG TÀU, NHA TRANG, ĐÀ LẠT, QUẢNG NINH, HUẾ, VINH
            // ====================================================
            [
                'name' => 'CGV Lam Sơn Square Vũng Tàu',
                'address' => 'Tầng 4, Lam Sơn Square, 9 Lê Lợi, Phường 1, TP. Vũng Tàu',
                'city' => 'Bà Rịa - Vũng Tàu',
                'rooms' => [
                    ['name' => 'Hall 1 (Gold Class VIP)', 'room_type' => 'Gold Class VIP', 'total_seats' => 60],
                    ['name' => 'Hall 2 (2D Standard)', 'room_type' => '2D Standard', 'total_seats' => 90],
                ],
            ],
            [
                'name' => 'Lotte Cinema Nha Trang Thái Nguyên',
                'address' => 'Tầng 3, Siêu thị Lotte Mart, 58 đường 23/10, Phương Sơn, TP. Nha Trang',
                'city' => 'Khánh Hòa',
                'rooms' => [
                    ['name' => 'Screen 1 (2D Dolby 7.1)', 'room_type' => '2D Dolby 7.1', 'total_seats' => 118],
                    ['name' => 'Screen 2 (2D Standard)', 'room_type' => '2D Standard', 'total_seats' => 90],
                ],
            ],
            [
                'name' => 'Cinestar Đà Lạt',
                'address' => 'Quảng trường Lâm Viên, Phường 10, TP. Đà Lạt',
                'city' => 'Lâm Đồng',
                'rooms' => [
                    ['name' => 'Cinema 1 (2D Laser)', 'room_type' => '2D Cinema Sound', 'total_seats' => 118],
                    ['name' => 'Cinema 2 (2D Standard)', 'room_type' => '2D Standard', 'total_seats' => 90],
                ],
            ],
            [
                'name' => 'CGV Vincom Hạ Long',
                'address' => 'Tầng 4, TTTM Vincom Center Hạ Long, Khu Cột Đồng Hồ, Bạch Đằng, Hạ Long',
                'city' => 'Quảng Ninh',
                'rooms' => [
                    ['name' => 'Hall 1 (2D Standard)', 'room_type' => '2D Standard', 'total_seats' => 90],
                ],
            ],
            [
                'name' => 'BHD Star Vincom Huế',
                'address' => 'Tầng 5, TTTM Vincom Plaza Huế, 50A Hùng Vương, Phú Nhuận, TP. Huế',
                'city' => 'Thừa Thiên Huế',
                'rooms' => [
                    ['name' => 'Hall 1 (2D Dolby Atmos)', 'room_type' => 'Dolby Atmos', 'total_seats' => 100],
                ],
            ],
            [
                'name' => 'Lotte Cinema Vinh Nghệ An',
                'address' => 'Tầng 5, TTTM VRC, 1 Phan Bội Châu, Lê Lợi, TP. Vinh',
                'city' => 'Nghệ An',
                'rooms' => [
                    ['name' => 'Screen 1 (2D Standard)', 'room_type' => '2D Standard', 'total_seats' => 90],
                ],
            ],
        ];

        foreach ($cinemasData as $cData) {
            $cinema = Cinema::updateOrCreate(
                ['name' => $cData['name']],
                [
                    'address' => $cData['address'],
                    'city' => $cData['city'],
                ]
            );

            foreach ($cData['rooms'] as $rData) {
                $room = Room::updateOrCreate(
                    ['cinema_id' => $cinema->id, 'name' => $rData['name']],
                    [
                        'room_type' => $rData['room_type'],
                        'total_seats' => $rData['total_seats'],
                    ]
                );

                $this->createSeatsForRoom($room);
            }
        }
    }

    protected function createSeatsForRoom(Room $room): void
    {
        if (Seat::where('room_id', $room->id)->exists()) {
            return;
        }

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
                    'room_id' => $room->id,
                    'row' => $row,
                    'number' => $col,
                    'type' => $type,
                ]);
            }
        }
    }
}
