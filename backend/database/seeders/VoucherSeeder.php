<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Voucher;
use Illuminate\Database\Seeder;

class VoucherSeeder extends Seeder
{
    public function run(): void
    {
        $vouchers = [
            [
                'code' => 'CINEMA20',
                'title' => 'Giảm 20% Giá Vé',
                'description' => 'Ưu đãi giảm 20% trên tổng tiền vé (Tối đa 50.000 đ)',
                'target' => 'ticket',
                'discount_type' => 'percent',
                'discount_value' => 20,
                'min_order_amount' => 95000,
                'max_discount_amount' => 50000,
                'usage_limit' => 500,
                'used_count' => 12,
                'expires_at' => now()->addMonths(6),
                'is_active' => true,
            ],
            [
                'code' => 'CHAOBANMOI',
                'title' => 'Chào Bạn Mới -30K',
                'description' => 'Giảm trực tiếp 30.000 đ cho đơn hàng từ 100.000 đ',
                'target' => 'all',
                'discount_type' => 'fixed',
                'discount_value' => 30000,
                'min_order_amount' => 100000,
                'max_discount_amount' => null,
                'usage_limit' => 1000,
                'used_count' => 45,
                'expires_at' => now()->addMonths(6),
                'is_active' => true,
            ],
            [
                'code' => 'BAPNUOCFREE',
                'title' => 'Giảm 50K Combo Bắp Nước',
                'description' => 'Giảm 50.000 đ trực tiếp vào tiền combo bắp nước',
                'target' => 'combo',
                'discount_type' => 'fixed',
                'discount_value' => 50000,
                'min_order_amount' => 69000,
                'max_discount_amount' => null,
                'usage_limit' => 300,
                'used_count' => 8,
                'expires_at' => now()->addMonths(6),
                'is_active' => true,
            ],
            [
                'code' => 'FREEVECINE',
                'title' => 'Miễn Phí 1 Vé Tiêu Chuẩn',
                'description' => 'Tặng 1 vé xem phim trị giá 95.000 đ khi đặt từ 2 vé trở lên',
                'target' => 'ticket',
                'discount_type' => 'fixed',
                'discount_value' => 95000,
                'min_order_amount' => 180000,
                'max_discount_amount' => null,
                'usage_limit' => 200,
                'used_count' => 5,
                'expires_at' => now()->addMonths(6),
                'is_active' => true,
            ],
            [
                'code' => 'VIPCINE50',
                'title' => 'Cine VIP Pass -50K',
                'description' => 'Giảm 50.000 đ cho đơn hàng tổng từ 200.000 đ',
                'target' => 'all',
                'discount_type' => 'fixed',
                'discount_value' => 50000,
                'min_order_amount' => 200000,
                'max_discount_amount' => null,
                'usage_limit' => 500,
                'used_count' => 20,
                'expires_at' => now()->addMonths(6),
                'is_active' => true,
            ],
        ];

        foreach ($vouchers as $v) {
            Voucher::updateOrCreate(
                ['code' => $v['code']],
                $v
            );
        }
    }
}
