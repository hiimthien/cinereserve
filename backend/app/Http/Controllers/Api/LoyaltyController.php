<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RedeemRewardRequest;
use App\Models\User;
use App\Services\LoyaltyService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoyaltyController extends Controller
{
    public function __construct(
        protected LoyaltyService $loyaltyService
    ) {}

    /**
     * Danh sách các phần thưởng lấy trực tiếp từ bảng Database `loyalty_rewards`
     */
    public function rewardsList(): JsonResponse
    {
        $rewards = $this->loyaltyService->getActiveRewards();

        return response()->json([
            'success' => true,
            'data' => $rewards,
        ]);
    }

    /**
     * Đổi điểm thưởng lấy Voucher ưu đãi
     */
    public function redeem(RedeemRewardRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = $request->user();
        if (!$user && isset($validated['user_id'])) {
            $user = User::find($validated['user_id']);
        }
        if (!$user) {
            $user = User::where('email', 'caoluongthienk1@gmail.com')->first();
        }

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng đăng nhập để đổi điểm thưởng.',
            ], 401);
        }

        try {
            $result = $this->loyaltyService->redeemReward($user, (string) $validated['reward_id']);

            return response()->json([
                'success' => true,
                'message' => "Đổi thưởng thành công! Đã gửi mã {$result['voucher']->code} về email {$user->email}",
                'data' => $result,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Lấy danh sách voucher thuộc sở hữu của người dùng hiện tại
     */
    public function myVouchers(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user && $request->has('user_id')) {
            $user = User::find($request->user_id);
        }
        if (!$user && $request->has('email')) {
            $user = User::where('email', $request->email)->first();
        }

        if (!$user) {
            return response()->json([
                'success' => true,
                'data' => [],
            ]);
        }

        $vouchers = $this->loyaltyService->getUserVouchers($user);

        return response()->json([
            'success' => true,
            'data' => $vouchers,
        ]);
    }
}
