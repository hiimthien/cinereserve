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
                'name' => 'Solo Combo (1 Bắp + 1 Nước)',
                'description' => '1 Bắp ngọt nóng hổi (60oz) + 1 Ly nước ngọt có ga mát lạnh (22oz)',
                'price' => 69000,
                'image_url' => 'https://images.unsplash.com/photo-1578849278619-e73505e9610f?w=300&auto=format&fit=crop&q=80',
                'badge' => 'Tiết Kiệm',
                'is_active' => true,
            ],
            [
                'name' => 'Couple Combo (1 Bắp Lớn + 2 Nước)',
                'description' => '1 Bắp rang bơ phô mai size L (85oz) + 2 Ly nước ngọt có ga tùy chọn (32oz)',
                'price' => 109000,
                'image_url' => 'https://images.unsplash.com/photo-1585647347483-22b66260dfff?w=300&auto=format&fit=crop&q=80',
                'badge' => 'Phổ Biến Nhất',
                'is_active' => true,
            ],
            [
                'name' => 'Party VIP Combo (2 Bắp + 4 Nước + 1 Snack)',
                'description' => '2 Bắp Caramel/Phô mai lớn + 4 Nước ngọt + 1 Khoai tây chiên giòn rụm',
                'price' => 169000,
                'image_url' => 'https://images.unsplash.com/photo-1505686994434-e3cc5abf1330?w=300&auto=format&fit=crop&q=80',
                'badge' => 'Ưu Đãi Nhóm',
                'is_active' => true,
            ],
            [
                'name' => 'Premium Nachos & Cheese Combo',
                'description' => '1 Khay bánh bắp Nachos giòn tan sốt phô mai & salsa cay nồng + 1 Nước ngọt lớn',
                'price' => 89000,
                'image_url' => 'https://images.unsplash.com/photo-1513456852971-30c0b8199d4d?w=300&auto=format&fit=crop&q=80',
                'badge' => 'Món Mới',
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
