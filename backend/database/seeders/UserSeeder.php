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
            [
                'email' => 'admin@admin.com',
                'name' => 'Quản Trị Viên (Admin Master)',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'phone' => '0900000001',
                'avatar' => 'https://api.dicebear.com/7.x/bottts/svg?seed=AdminMaster',
                'points' => 500,
                'membership_tier' => 'diamond',
                'total_spent' => 5000000,
                'total_tickets_bought' => 50,
            ],
            [
                'email' => 'caoluongthienk1@gmail.com',
                'name' => 'Cao Lương Thiện (Admin)',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'phone' => '0388145796',
                'avatar' => 'https://api.dicebear.com/7.x/bottts/svg?seed=CaoLuongThien',
                'points' => 200,
                'membership_tier' => 'vip',
                'total_spent' => 1200000,
                'total_tickets_bought' => 12,
            ],
            [
                'email' => 'staff@cinereserve.vn',
                'name' => 'Nhân Viên Soát Vé',
                'password' => Hash::make('staff123'),
                'role' => 'staff',
                'phone' => '0900000002',
                'avatar' => 'https://api.dicebear.com/7.x/bottts/svg?seed=StaffScanner',
                'points' => 50,
                'membership_tier' => 'member',
                'total_spent' => 100000,
                'total_tickets_bought' => 1,
            ],
            [
                'email' => 'user@gmail.com',
                'name' => 'Trần Văn Khách (User)',
                'password' => Hash::make('user123'),
                'role' => 'user',
                'phone' => '0912345678',
                'avatar' => 'https://api.dicebear.com/7.x/bottts/svg?seed=TranVanKhach',
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
