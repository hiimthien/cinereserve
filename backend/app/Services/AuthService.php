<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\Voucher;
use App\Repositories\Contracts\UserRepositoryInterface;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AuthService
{
    public function __construct(
        protected UserRepositoryInterface $userRepository
    ) {}

    public function register(array $data): array
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'phone' => $data['phone'] ?? '0388145796',
            'avatar' => 'https://api.dicebear.com/7.x/bottts/svg?seed=' . urlencode($data['name']),
            'points' => 20, // Tặng 20 điểm thưởng gia nhập ban đầu
            'membership_tier' => 'member',
            'total_spent' => 0,
            'total_tickets_bought' => 0,
        ]);

        $token = $user->createToken('cinereserve-auth-token')->plainTextToken;

        // Đồng bộ hóa các đơn đặt vé trước đó của Email này
        try {
            $pastBookings = \App\Models\Booking::where('user_email', $user->email)
                ->where('status', 'confirmed')
                ->get();

            if ($pastBookings->isNotEmpty()) {
                $pastSpent = (float) $pastBookings->sum('total_amount');
                $pastTickets = $pastBookings->count();
                $bonusPoints = (int) floor($pastSpent * 0.05 / 1000);

                $user->update([
                    'total_spent' => $pastSpent,
                    'total_tickets_bought' => $pastTickets,
                    'points' => $user->points + $bonusPoints,
                ]);
            }
        } catch (Exception $syncEx) {
            Log::warning('Không thể đồng bộ vé cũ khi đăng ký: ' . $syncEx->getMessage());
        }

        // Gửi email chào mừng qua Queue Job
        try {
            $welcomeVoucher = Voucher::where('code', 'CHAOBANMOI')->first();
            if ($welcomeVoucher && !empty($user->email)) {
                \App\Jobs\SendWelcomeVoucherEmailJob::dispatch(
                    user: $user,
                    voucher: $welcomeVoucher,
                    badgeText: 'Chào Mừng Thành Viên Mới',
                    customMessage: 'Chào mừng bạn gia nhập CineReserve Loyalty Club! Bạn nhận được 20 Điểm thưởng và Voucher 30.000 đ cho lần đặt vé đầu tiên:',
                    subjectTitle: '🎉 [CineReserve] Chào mừng bạn gia nhập! Tặng bạn Voucher 30.000 đ'
                );
            }
        } catch (Exception $e) {
            Log::error('Lỗi dispatch Queue Job chào mừng: ' . $e->getMessage());
        }

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    public function login(string $email, string $password): ?array
    {
        $user = User::where('email', $email)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return null;
        }

        $token = $user->createToken('cinereserve-auth-token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    public function googleAuth(string $email, string $name, ?string $avatar = null, ?string $googleId = null): array
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make(uniqid('google_')),
                'phone' => '0388145796',
                'avatar' => $avatar ?: 'https://api.dicebear.com/7.x/bottts/svg?seed=' . urlencode($name),
                'points' => 50,
                'membership_tier' => 'member',
                'total_spent' => 0,
                'total_tickets_bought' => 0,
                'google_id' => $googleId,
            ]);

            try {
                $welcomeVoucher = Voucher::where('code', 'CHAOBANMOI')->first();
                if ($welcomeVoucher) {
                    \App\Jobs\SendWelcomeVoucherEmailJob::dispatch(
                        user: $user,
                        voucher: $welcomeVoucher,
                        badgeText: 'Chào Mừng Gia Nhập Qua Google',
                        customMessage: 'Đăng nhập Google thành công! Bạn nhận được 50 Điểm thưởng và Voucher 30.000 đ:',
                        subjectTitle: '🎉 [CineReserve] Chào mừng bạn gia nhập qua Google! Tặng bạn Voucher 30.000 đ'
                    );
                }
            } catch (Exception $e) {
                Log::error('Lỗi dispatch Queue Job google auth: ' . $e->getMessage());
            }
        }

        $token = $user->createToken('cinereserve-auth-token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    public function forgotPassword(string $email): array
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            throw new Exception('Không tìm thấy tài khoản với email này trong hệ thống.');
        }

        $otp = (string) random_int(100000, 999999);
        $cacheKey = "cinereserve:password_reset:{$user->email}";

        Cache::put($cacheKey, $otp, 600);

        try {
            Mail::to($user->email)->send(new \App\Mail\PasswordResetOtpMail(
                user: $user,
                otpCode: $otp
            ));
        } catch (Exception $e) {
            Log::error('Lỗi gửi email OTP đặt lại mật khẩu: ' . $e->getMessage());
        }

        return [
            'email' => $user->email,
            'debug_otp' => app()->environment('local') ? $otp : null,
        ];
    }

    public function resetPassword(string $email, string $otp, string $newPassword): array
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            throw new Exception('Không tìm thấy tài khoản tương ứng.');
        }

        $cacheKey = "cinereserve:password_reset:{$user->email}";
        $cachedOtp = Cache::get($cacheKey);

        if (!$cachedOtp || $cachedOtp !== $otp) {
            throw new Exception('Mã OTP không hợp lệ hoặc đã hết hạn (10 phút).');
        }

        $user->password = Hash::make($newPassword);
        $user->save();

        Cache::forget($cacheKey);

        $token = $user->createToken('cinereserve-auth-token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    public function changePassword(User $user, string $currentPassword, string $newPassword): void
    {
        if (!Hash::check($currentPassword, $user->password)) {
            throw new Exception('Mật khẩu hiện tại không chính xác.');
        }

        $user->password = Hash::make($newPassword);
        $user->save();
    }
}
