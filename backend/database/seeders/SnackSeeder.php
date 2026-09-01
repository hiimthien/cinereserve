<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Snack;
use Illuminate\Database\Seeder;

class SnackSeeder extends Seeder
{
    public function run(): void
    {
        $combos = [
            [
                'name' => 'Combo Bắp Nước Đơn (1 Bắp + 1 Nước)',
                'description' => '1 Bắp ngọt rang bơ nóng hổi thơm lừng (60oz) + 1 Ly nước ngọt có ga mát lạnh (22oz)',
                'price' => 85000,
                'image_url' => 'https://images.unsplash.com/photo-1578849278619-e73505e9610f?w=400&auto=format&fit=crop&q=80',
                'badge' => 'Tiết Kiệm',
                'is_active' => true,
            ],
            [
                'name' => 'Combo Đôi Sweetbox (1 Bắp 2 Ngăn + 2 Nước)',
                'description' => '1 Bắp 2 Ngăn (Phô Mai & Caramel) size L + 2 Ly nước ngọt có ga tùy chọn (32oz)',
                'price' => 125000,
                'image_url' => 'https://images.unsplash.com/photo-1585647347483-22b66260dfff?w=400&auto=format&fit=crop&q=80',
                'badge' => 'Bán Chạy Nhất',
                'is_active' => true,
            ],
            [
                'name' => 'Combo Gia Đình Blockbuster (2 Bắp Lớn + 4 Nước)',
                'description' => '2 Bắp lớn tự chọn vị + 4 Nước ngọt lớn + 1 Phần Phô mai que chiên giòn rụm',
                'price' => 195000,
                'image_url' => 'https://images.unsplash.com/photo-1505686994434-e3cc5abf1330?w=400&auto=format&fit=crop&q=80',
                'badge' => 'Ưu Đãi Nhóm',
                'is_active' => true,
            ],
            [
                'name' => 'Combo Bắp Phô Mai & Bánh Nachos Salsa',
                'description' => '1 Bắp Phô Mai Jumbo + 1 Khay bánh bắp Nachos sốt phô mai & salsa cay nồng + 1 Nước',
                'price' => 110000,
                'image_url' => 'https://images.unsplash.com/photo-1513456852971-30c0b8199d4d?w=400&auto=format&fit=crop&q=80',
                'badge' => 'Món Mới',
                'is_active' => true,
            ],
            [
                'name' => 'Bắp Rang Bơ Phô Mai Jumbo (Lớn)',
                'description' => 'Bắp rang bơ lắc bột phô mai Cheddar thơm béo ngậy size khổng lồ 85oz',
                'price' => 59000,
                'image_url' => 'https://images.unsplash.com/photo-1578849278619-e73505e9610f?w=400&auto=format&fit=crop&q=80',
                'badge' => 'Phô Mai',
                'is_active' => true,
            ],
            [
                'name' => 'Bắp Rang Bơ Caramel Premium (Lớn)',
                'description' => 'Bắp rang bơ phủ lớp sốt caramel giòn tan ngọt ngào chuẩn rạp CGV',
                'price' => 59000,
                'image_url' => 'https://images.unsplash.com/photo-1585647347483-22b66260dfff?w=400&auto=format&fit=crop&q=80',
                'badge' => 'Caramel',
                'is_active' => true,
            ],
            [
                'name' => 'Nước Ngọt Pepsi / 7Up / Mirinda (Size Lớn 32oz)',
                'description' => 'Nước ngọt có ga phục vụ kèm đá mát lạnh cực đã',
                'price' => 35000,
                'image_url' => 'https://images.unsplash.com/photo-1622483767028-3f66f32aef97?w=400&auto=format&fit=crop&q=80',
                'badge' => 'Giải Khát',
                'is_active' => true,
            ],
            [
                'name' => 'Xúc Xích Nướng Phô Mai & Bánh Mì Hotdog',
                'description' => 'Xúc xích Đức nướng nóng giòn kẹp bánh mì sốt mù tạt mật ong và tương cà',
                'price' => 49000,
                'image_url' => 'https://images.unsplash.com/photo-1619740455993-9e612b1af08a?w=400&auto=format&fit=crop&q=80',
                'badge' => 'Ăn Vặt',
                'is_active' => true,
            ],
        ];

        foreach ($combos as $combo) {
            Snack::updateOrCreate(
                ['name' => $combo['name']],
                $combo
            );
        }
    }
}
