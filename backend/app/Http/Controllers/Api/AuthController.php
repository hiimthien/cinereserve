<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\LoyaltyVoucherMail;
use App\Models\User;
use App\Models\Voucher;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Đăng ký tài khoản thành viên mới
     */
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|string|email|max:100|unique:users,email',
            'password' => 'required|string|min:6',
            'phone' => 'nullable|string|max:20',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone ?? '0388145796',
            'avatar' => 'https://api.dicebear.com/7.x/bottts/svg?seed=' . urlencode($request->name),
            'points' => 20, // Tặng 20 điểm thưởng gia nhập ban đầu
            'membership_tier' => 'member',
            'total_spent' => 0,
            'total_tickets_bought' => 0,
        ]);

        $token = $user->createToken('cinereserve-auth-token')->plainTextToken;

        // Đồng bộ hóa các đơn đặt vé trước đó của Email này (nếu đã từng mua dạng khách vãng lai)
        try {
            $pastBookings = \App\Models\Booking::where('user_email', $user->email)
                ->where('status', 'confirmed')
                ->get();

            if ($pastBookings->isNotEmpty()) {
                $pastSpent = (float) $pastBookings->sum('total_amount');
                $pastTickets = $pastBookings->count();
                $bonusPoints = (int) floor($pastSpent * 0.05 / 1000); // 5% điểm thưởng tích lũy

                $user->update([
                    'total_spent' => $pastSpent,
                    'total_tickets_bought' => $pastTickets,
                    'points' => $user->points + $bonusPoints,
                ]);
            }
        } catch (Exception $syncEx) {
            Log::warning('Không thể đồng bộ vé cũ khi đăng ký: ' . $syncEx->getMessage());
        }

        // Tự động gửi Email Chào mừng qua Queue Job (Background Worker)
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

        return response()->json([
            'success' => true,
            'message' => 'Đăng ký tài khoản thành công! Tặng bạn 20 điểm thưởng và voucher 30k vào email.',
            'data' => [
                'user' => $user,
                'token' => $token,
            ],
        ], 201);
    }

    /**
     * Đăng nhập thành viên
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Email hoặc mật khẩu không chính xác.',
            ], 401);
        }

        $token = $user->createToken('cinereserve-auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Đăng nhập thành công!',
            'data' => [
                'user' => $user,
                'token' => $token,
            ],
        ]);
    }

    /**
     * Đăng nhập nhanh Google One-Tap / OAuth
     */
    public function googleAuth(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|string|email',
            'name' => 'required|string',
            'google_id' => 'nullable|string',
            'avatar' => 'nullable|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make(uniqid('google_')),
                'phone' => '0388145796',
                'avatar' => $request->avatar ?: 'https://api.dicebear.com/7.x/bottts/svg?seed=' . urlencode($request->name),
                'points' => 50,
                'membership_tier' => 'member',
                'total_spent' => 0,
                'total_tickets_bought' => 0,
                'google_id' => $request->google_id,
            ]);

            // Gửi email chào mừng qua Queue Job (Background Worker)
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

        return response()->json([
            'success' => true,
            'message' => 'Đăng nhập Google thành công!',
            'data' => [
                'user' => $user,
                'token' => $token,
            ],
        ]);
    }

    /**
     * Lấy thông tin tài khoản hiện tại
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            // Demo fallback user if not authenticated
            $user = User::where('email', 'caoluongthienk1@gmail.com')->first();
        }

        return response()->json([
            'success' => true,
            'data' => $user,
        ]);
    }

    /**
     * Đăng xuất tài khoản
     */
    public function logout(Request $request): JsonResponse
    {
        if ($request->user()) {
            $request->user()->currentAccessToken()->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Đăng xuất thành công!',
        ]);
    }
}
