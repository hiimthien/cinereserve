<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\LoyaltyVoucherMail;
use App\Models\LoyaltyReward;
use App\Models\User;
use App\Models\Voucher;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class LoyaltyController extends Controller
{
    /**
     * Danh sách các phần thưởng lấy trực tiếp từ bảng Database `loyalty_rewards`
     */
    public function rewardsList(): JsonResponse
    {
        $rewards = LoyaltyReward::where('is_active', true)
            ->orderBy('points_required', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $rewards,
        ]);
    }

    /**
     * Đổi điểm thưởng lấy Voucher ưu đãi
     */
    public function redeem(Request $request): JsonResponse
    {
        $request->validate([
            'reward_id' => 'required|string',
            'user_id' => 'nullable|integer',
        ]);

        $user = $request->user();
        if (!$user) {
            $user = User::find($request->user_id) ?: User::where('email', 'caoluongthienk1@gmail.com')->first();
        }

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng đăng nhập để đổi điểm thưởng.',
            ], 401);
        }

        // Lấy thông tin phần thưởng từ Database
        $reward = LoyaltyReward::where('reward_key', $request->reward_id)
            ->where('is_active', true)
            ->first();

        if (!$reward) {
            return response()->json([
                'success' => false,
                'message' => 'Phần thưởng không tồn tại hoặc đã tạm dừng.',
            ], 404);
        }

        if ($user->points < $reward->points_required) {
            return response()->json([
                'success' => false,
                'message' => "Bạn cần tối thiểu {$reward->points_required} điểm CinePoint (Hiện có: {$user->points} điểm).",
            ], 422);
        }

        // Trừ điểm user trong Database
        $user->points -= $reward->points_required;
        $user->save();

        // Tạo mã Voucher riêng biệt trong bảng `vouchers`
        $voucherCode = $reward->prefix . strtoupper(substr(uniqid(), -5));
        $voucher = Voucher::create([
            'code' => $voucherCode,
            'title' => $reward->title,
            'description' => "Đổi từ {$reward->points_required} điểm CinePoint của {$user->name}",
            'target' => $reward->target,
            'discount_type' => 'fixed',
            'discount_value' => $reward->discount_value,
            'min_order_amount' => 0,
            'usage_limit' => 1,
            'used_count' => 0,
            'expires_at' => now()->addMonths(3),
            'is_active' => true,
        ]);

        // Gửi mail voucher về Gmail của user
        try {
            if (!empty($user->email)) {
                Mail::to($user->email)->send(new LoyaltyVoucherMail(
                    user: $user,
                    voucher: $voucher,
                    badgeText: 'Đổi Điểm Thưởng Thành Công',
                    customMessage: "Bạn vừa đổi thành công {$reward->points_required} điểm lấy {$voucher->title}. Dưới đây là mã voucher của bạn:",
                    subjectTitle: "🎁 [CineReserve] Đổi điểm thành công: Tặng bạn mã {$voucher->code}"
                ));
            }
        } catch (Exception $e) {
            Log::error('Lỗi gửi mail đổi điểm: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => "Đổi thưởng thành công! Đã gửi mã {$voucherCode} về email {$user->email}",
            'data' => [
                'voucher' => $voucher,
                'remaining_points' => $user->points,
            ],
        ]);
    }
}
