<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\LoyaltyReward;
use Illuminate\Database\Seeder;

class LoyaltyRewardSeeder extends Seeder
{
    public function run(): void
    {
        $rewards = [
            [
                'reward_key' => 'voucher_20k',
                'title' => 'Voucher Giảm 20.000 đ',
                'description' => 'Áp dụng cho mọi đơn đặt vé từ 95.000 đ',
                'points_required' => 50,
                'discount_value' => 20000,
                'target' => 'all',
                'badge' => 'Phổ biến',
                'icon' => 'tag',
                'prefix' => 'REDEEM20K-',
                'is_active' => true,
            ],
            [
                'reward_key' => 'free_snack',
                'title' => 'Miễn Phí 1 Solo Combo Bắp Nước',
                'description' => 'Tặng 1 Bắp rang bơ nóng hổi (60oz) + 1 Ly nước ngọt (22oz) tại quầy',
                'points_required' => 100,
                'discount_value' => 69000,
                'target' => 'combo',
                'badge' => 'Bắp Nước Free',
                'icon' => 'popcorn',
                'prefix' => 'SNACKFREE-',
                'is_active' => true,
            ],
            [
                'reward_key' => 'voucher_50k',
                'title' => 'Voucher Giảm 50.000 đ',
                'description' => 'Áp dụng cho đơn hàng tổng từ 150.000 đ trở lên',
                'points_required' => 150,
                'discount_value' => 50000,
                'target' => 'all',
                'badge' => 'Tiết kiệm lớn',
                'icon' => 'percent',
                'prefix' => 'REDEEM50K-',
                'is_active' => true,
            ],
            [
                'reward_key' => 'free_ticket',
                'title' => 'Miễn Phí 1 Vé Xem Phim Tiêu Chuẩn',
                'description' => 'Miễn phí 100% 1 vé xem phim 2D/3D bất kỳ trị giá 95.000 đ',
                'points_required' => 250,
                'discount_value' => 95000,
                'target' => 'ticket',
                'badge' => 'Vé Miễn Phí',
                'icon' => 'ticket',
                'prefix' => 'TICKETFREE-',
                'is_active' => true,
            ],
            [
                'reward_key' => 'vip_couple_pass',
                'title' => 'Gói Trọn Gói Siêu VIP Đôi',
                'description' => '2 Vé Phim Ghế VIP/Couple + 1 Couple Combo Bắp Nước lớn',
                'points_required' => 400,
                'discount_value' => 250000,
                'target' => 'all',
                'badge' => 'Đặc Quyền VVIP',
                'icon' => 'crown',
                'prefix' => 'VIPCOUPLE-',
                'is_active' => true,
            ],
        ];

        foreach ($rewards as $r) {
            LoyaltyReward::updateOrCreate(
                ['reward_key' => $r['reward_key']],
                $r
            );
        }
    }
}
