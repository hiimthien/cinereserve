<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            // ==========================================
            // 👑 QUẢN TRỊ VIÊN & NHÂN VIÊN HỆ THỐNG
            // ==========================================
            [
                'email' => 'admin@admin.com',
                'name' => 'Quản Trị Viên (Admin Master)',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'phone' => '0900000001',
                'avatar' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=120&h=120&fit=crop',
                'points' => 0,
                'membership_tier' => 'admin',
                'total_spent' => 0,
                'total_tickets_bought' => 0,
            ],
            [
                'email' => 'staff@cinereserve.vn',
                'name' => 'Nhân Viên Soát Vé (TP.HCM)',
                'password' => Hash::make('password'),
                'role' => 'staff',
                'phone' => '0900000002',
                'avatar' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=120&h=120&fit=crop',
                'points' => 0,
                'membership_tier' => 'staff',
                'total_spent' => 0,
                'total_tickets_bought' => 0,
            ],
            [
                'email' => 'staff.hanoi@cinereserve.vn',
                'name' => 'Nhân Viên Soát Vé (Hà Nội)',
                'password' => Hash::make('password'),
                'role' => 'staff',
                'phone' => '0900000003',
                'avatar' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=120&h=120&fit=crop',
                'points' => 0,
                'membership_tier' => 'staff',
                'total_spent' => 0,
                'total_tickets_bought' => 0,
            ],

            // ==========================================
            // 💎 KHÁCH HÀNG HẠNG KIM CƯƠNG (DIAMOND: 500+ PTS)
            // ==========================================
            [
                'email' => 'diamond@gmail.com',
                'name' => 'Lê Hoàng Nam',
                'password' => Hash::make('password'),
                'role' => 'user',
                'phone' => '0988888888',
                'avatar' => 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=120&h=120&fit=crop',
                'points' => 680,
                'membership_tier' => 'diamond',
                'total_spent' => 6800000,
                'total_tickets_bought' => 45,
            ],
            [
                'email' => 'tran.quoc.bao@gmail.com',
                'name' => 'Trần Quốc Bảo',
                'password' => Hash::make('password'),
                'role' => 'user',
                'phone' => '0988123456',
                'avatar' => 'https://images.unsplash.com/photo-1570295999919-56ceb5ecca61?w=120&h=120&fit=crop',
                'points' => 540,
                'membership_tier' => 'diamond',
                'total_spent' => 5400000,
                'total_tickets_bought' => 36,
            ],
            [
                'email' => 'nguyen.hong.phuc@gmail.com',
                'name' => 'Nguyễn Hồng Phúc',
                'password' => Hash::make('password'),
                'role' => 'user',
                'phone' => '0988654321',
                'avatar' => 'https://images.unsplash.com/photo-1527980965255-d3b416303d12?w=120&h=120&fit=crop',
                'points' => 720,
                'membership_tier' => 'diamond',
                'total_spent' => 7200000,
                'total_tickets_bought' => 48,
            ],

            // ==========================================
            // 🥇 KHÁCH HÀNG HẠNG VÀNG (GOLD: 200 - 499 PTS)
            // ==========================================
            [
                'email' => 'gold@gmail.com',
                'name' => 'Nguyễn Thị Thanh Trúc',
                'password' => Hash::make('password'),
                'role' => 'user',
                'phone' => '0977777777',
                'avatar' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=120&h=120&fit=crop',
                'points' => 320,
                'membership_tier' => 'gold',
                'total_spent' => 3200000,
                'total_tickets_bought' => 24,
            ],
            [
                'email' => 'pham.minh.tuan@gmail.com',
                'name' => 'Phạm Minh Tuấn',
                'password' => Hash::make('password'),
                'role' => 'user',
                'phone' => '0977112233',
                'avatar' => 'https://images.unsplash.com/photo-1522075469751-3a6694fb2f61?w=120&h=120&fit=crop',
                'points' => 250,
                'membership_tier' => 'gold',
                'total_spent' => 2500000,
                'total_tickets_bought' => 19,
            ],
            [
                'email' => 'dang.thu.thao@gmail.com',
                'name' => 'Đặng Thu Thảo',
                'password' => Hash::make('password'),
                'role' => 'user',
                'phone' => '0977334455',
                'avatar' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=120&h=120&fit=crop',
                'points' => 380,
                'membership_tier' => 'gold',
                'total_spent' => 3800000,
                'total_tickets_bought' => 28,
            ],

            // ==========================================
            // 🥈 KHÁCH HÀNG HẠNG BẠC (SILVER: 80 - 199 PTS)
            // ==========================================
            [
                'email' => 'silver@gmail.com',
                'name' => 'Võ Hoàng Yến',
                'password' => Hash::make('password'),
                'role' => 'user',
                'phone' => '0966666666',
                'avatar' => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=120&h=120&fit=crop',
                'points' => 140,
                'membership_tier' => 'silver',
                'total_spent' => 1400000,
                'total_tickets_bought' => 10,
            ],
            [
                'email' => 'doan.khanh.linh@gmail.com',
                'name' => 'Đoàn Khánh Linh',
                'password' => Hash::make('password'),
                'role' => 'user',
                'phone' => '0966554433',
                'avatar' => 'https://images.unsplash.com/photo-1517841905240-472988babdf9?w=120&h=120&fit=crop',
                'points' => 95,
                'membership_tier' => 'silver',
                'total_spent' => 950000,
                'total_tickets_bought' => 7,
            ],
            [
                'email' => 'bui.anh.khoa@gmail.com',
                'name' => 'Bùi Anh Khoa',
                'password' => Hash::make('password'),
                'role' => 'user',
                'phone' => '0966778899',
                'avatar' => 'https://images.unsplash.com/photo-1492562080023-ab3db95bfbce?w=120&h=120&fit=crop',
                'points' => 160,
                'membership_tier' => 'silver',
                'total_spent' => 1600000,
                'total_tickets_bought' => 12,
            ],

            // ==========================================
            // 🎟️ KHÁCH HÀNG THÀNH VIÊN MỚI (MEMBER: 0 - 79 PTS)
            // ==========================================
            [
                'email' => 'user@gmail.com',
                'name' => 'Nguyễn Văn Khách',
                'password' => Hash::make('password'),
                'role' => 'user',
                'phone' => '0912345678',
                'avatar' => 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=120&h=120&fit=crop',
                'points' => 30,
                'membership_tier' => 'member',
                'total_spent' => 190000,
                'total_tickets_bought' => 2,
            ],
            [
                'email' => 'le.thanh.tam@gmail.com',
                'name' => 'Lê Thanh Tâm',
                'password' => Hash::make('password'),
                'role' => 'user',
                'phone' => '0912987654',
                'avatar' => 'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?w=120&h=120&fit=crop',
                'points' => 20,
                'membership_tier' => 'member',
                'total_spent' => 95000,
                'total_tickets_bought' => 1,
            ],
            [
                'email' => 'hoang.duc.anh@gmail.com',
                'name' => 'Hoàng Đức Anh',
                'password' => Hash::make('password'),
                'role' => 'user',
                'phone' => '0912445566',
                'avatar' => 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?w=120&h=120&fit=crop',
                'points' => 20,
                'membership_tier' => 'member',
                'total_spent' => 0,
                'total_tickets_bought' => 0,
            ],
            [
                'email' => 'nguyen.hai.yen@gmail.com',
                'name' => 'Nguyễn Hải Yến',
                'password' => Hash::make('password'),
                'role' => 'user',
                'phone' => '0912778899',
                'avatar' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=120&h=120&fit=crop',
                'points' => 40,
                'membership_tier' => 'member',
                'total_spent' => 240000,
                'total_tickets_bought' => 2,
            ],
            [
                'email' => 'vu.hoang.long@gmail.com',
                'name' => 'Vũ Hoàng Long',
                'password' => Hash::make('password'),
                'role' => 'user',
                'phone' => '0912556677',
                'avatar' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=120&h=120&fit=crop',
                'points' => 20,
                'membership_tier' => 'member',
                'total_spent' => 95000,
                'total_tickets_bought' => 1,
            ],
        ];

        foreach ($users as $u) {
            User::updateOrCreate(
                ['email' => $u['email']],
                $u
            );
        }
    }
}
