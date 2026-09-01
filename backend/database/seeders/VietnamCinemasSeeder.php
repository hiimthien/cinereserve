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
            // --- TP. HỒ CHÍ MINH ---
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
                    ['name' => 'Hall 1 (Gold Class)', 'room_type' => 'Gold Class VIP', 'total_seats' => 60],
                    ['name' => 'Hall 2 (Starium Laser)', 'room_type' => 'Starium 2D', 'total_seats' => 118],
                ],
            ],
            [
                'name' => 'Lotte Cinema Nam Sài Gòn',
                'address' => 'Tầng 3, Lotte Mart Nam Sài Gòn, 469 Nguyễn Hữu Thọ, Tân Hưng, Quận 7',
                'city' => 'Hồ Chí Minh',
                'rooms' => [
                    ['name' => 'Screen 1 (Super Plex)', 'room_type' => 'Super Plex Laser', 'total_seats' => 118],
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
                'name' => 'BHD Star Cineplex Bitexco Icon 68',
                'address' => 'Tầng 3 & 4, Bitexco Financial Tower, 2 Hải Triều, Bến Nghé, Quận 1',
                'city' => 'Hồ Chí Minh',
                'rooms' => [
                    ['name' => 'Hall 1 (First Class)', 'room_type' => 'First Class VIP', 'total_seats' => 60],
                    ['name' => 'Hall 2 (2D Atmos)', 'room_type' => 'Dolby Atmos', 'total_seats' => 90],
                ],
            ],
            [
                'name' => 'BHD Star 3/2',
                'address' => 'Tầng 5, Siêu Thị Vincom 3/2, 3C Đường 3/2, Phường 11, Quận 10',
                'city' => 'Hồ Chí Minh',
                'rooms' => [
                    ['name' => 'Hall 1 (2D Atmos)', 'room_type' => 'Dolby Atmos', 'total_seats' => 118],
                    ['name' => 'Hall 2 (2D Standard)', 'room_type' => '2D Standard', 'total_seats' => 90],
                ],
            ],
            [
                'name' => 'Cinestar Quốc Thanh',
                'address' => '271 Nguyễn Trãi, Phường Nguyễn Cư Trinh, Quận 1',
                'city' => 'Hồ Chí Minh',
                'rooms' => [
                    ['name' => 'Cinema 1 (Cinema Sound 7.1)', 'room_type' => '2D Cinema Sound', 'total_seats' => 118],
                    ['name' => 'Cinema 2 (2D Standard)', 'room_type' => '2D Standard', 'total_seats' => 90],
                ],
            ],
            [
                'name' => 'Beta Cinemas Quang Trung',
                'address' => '645 Quang Trung, Phường 11, Gò Vấp',
                'city' => 'Hồ Chí Minh',
                'rooms' => [
                    ['name' => 'Hall 1 (2D Standard)', 'room_type' => '2D Standard', 'total_seats' => 90],
                ],
            ],

            // --- HÀ NỘI ---
            [
                'name' => 'CGV Vincom Royal City',
                'address' => 'Tầng B2, TTTM Vincom Mega Mall Royal City, 72A Nguyễn Trãi, Thanh Xuân',
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
                    ['name' => 'Hall 1 (Gold Class)', 'room_type' => 'Gold Class VIP', 'total_seats' => 60],
                    ['name' => 'Hall 2 (2D Standard)', 'room_type' => '2D Standard', 'total_seats' => 90],
                ],
            ],
            [
                'name' => 'CGV Vincom Metropolis Liễu Giai',
                'address' => 'Tầng M3, Vincom Center Metropolis, 29 Liễu Giai, Ba Đình',
                'city' => 'Hà Nội',
                'rooms' => [
                    ['name' => 'Hall 1 (IMAX)', 'room_type' => 'IMAX Laser', 'total_seats' => 118],
                    ['name' => 'Hall 2 (L\'amour)', 'room_type' => 'L\'amour Bed', 'total_seats' => 40],
                ],
            ],
            [
                'name' => 'Lotte Cinema West Lake Tây Hồ',
                'address' => 'Tầng 4, Lotte Mall West Lake Hanoi, 272 Võ Chí Công, Tây Hồ',
                'city' => 'Hà Nội',
                'rooms' => [
                    ['name' => 'Screen 1 (IMAX Laser)', 'room_type' => 'IMAX Laser', 'total_seats' => 118],
                    ['name' => 'Screen 2 (Cine Comfort VIP)', 'room_type' => 'Cine Comfort', 'total_seats' => 60],
                ],
            ],
            [
                'name' => 'BHD Star Vincom Phạm Ngọc Thạch',
                'address' => 'Tầng 8, TTTM Vincom, 2 Phạm Ngọc Thạch, Trung Tự, Đống Đa',
                'city' => 'Hà Nội',
                'rooms' => [
                    ['name' => 'Hall 1 (First Class VIP)', 'room_type' => 'First Class VIP', 'total_seats' => 60],
                    ['name' => 'Hall 2 (2D Atmos)', 'room_type' => 'Dolby Atmos', 'total_seats' => 90],
                ],
            ],
            [
                'name' => 'Beta Cinemas Thanh Xuân',
                'address' => 'Tầng hầm B1, Tòa nhà Golden West, 2 Lê Văn Thiêm, Nhân Chính, Thanh Xuân',
                'city' => 'Hà Nội',
                'rooms' => [
                    ['name' => 'Hall 1 (2D Standard)', 'room_type' => '2D Standard', 'total_seats' => 90],
                ],
            ],

            // --- ĐÀ NẴNG ---
            [
                'name' => 'CGV Vincom Plaza Đà Nẵng',
                'address' => 'Tầng 4, TTTM Vincom Plaza Ngô Quyền, 910A Ngô Quyền, An Hải Bắc, Sơn Trà',
                'city' => 'Đà Nẵng',
                'rooms' => [
                    ['name' => 'Hall 1 (Dolby Atmos)', 'room_type' => 'Dolby Atmos', 'total_seats' => 118],
                    ['name' => 'Hall 2 (2D Standard)', 'room_type' => '2D Standard', 'total_seats' => 90],
                ],
            ],
            [
                'name' => 'Lotte Cinema Đà Nẵng',
                'address' => 'Tầng 5, TTTM Lotte Mart Đà Nẵng, Hòa Cường Bắc, Hải Châu',
                'city' => 'Đà Nẵng',
                'rooms' => [
                    ['name' => 'Screen 1 (2D Standard)', 'room_type' => '2D Standard', 'total_seats' => 90],
                ],
            ],

            // --- HẢI PHÒNG ---
            [
                'name' => 'CGV Vincom Imperia Hải Phòng',
                'address' => 'Tầng 4, TTTM Vincom Plaza Imperia, Số 1 Bạch Đằng, Thượng Lý, Hồng Bàng',
                'city' => 'Hải Phòng',
                'rooms' => [
                    ['name' => 'Hall 1 (IMAX Laser)', 'room_type' => 'IMAX Laser', 'total_seats' => 118],
                    ['name' => 'Hall 2 (Gold Class VIP)', 'room_type' => 'Gold Class VIP', 'total_seats' => 60],
                ],
            ],

            // --- CẦN THƠ ---
            [
                'name' => 'CGV Vincom Hùng Vương Cần Thơ',
                'address' => 'Tầng 5, TTTM Vincom Plaza Hùng Vương, 2 Hùng Vương, Thới Bình, Ninh Kiều',
                'city' => 'Cần Thơ',
                'rooms' => [
                    ['name' => 'Hall 1 (2D Standard)', 'room_type' => '2D Standard', 'total_seats' => 90],
                ],
            ],

            // --- ĐÀ LẠT ---
            [
                'name' => 'Cinestar Đà Lạt',
                'address' => 'Quảng trường Lâm Viên, Phường 10, TP. Đà Lạt',
                'city' => 'Lâm Đồng',
                'rooms' => [
                    ['name' => 'Cinema 1 (2D Standard)', 'room_type' => '2D Standard', 'total_seats' => 90],
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
