<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\Voucher;
use App\Repositories\Contracts\LoyaltyRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Contracts\VoucherRepositoryInterface;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class LoyaltyService
{
    public function __construct(
        protected LoyaltyRepositoryInterface $loyaltyRepository,
        protected UserRepositoryInterface $userRepository,
        protected VoucherRepositoryInterface $voucherRepository
    ) {}

    public function getActiveRewards(): Collection
    {
        return $this->loyaltyRepository->getActiveRewards();
    }

    public function getUserVouchers(User $user): Collection
    {
        return $this->loyaltyRepository->getUserVouchers($user);
    }

    public function redeemReward(User $user, string $rewardKey): array
    {
        $reward = $this->loyaltyRepository->findRewardByKey($rewardKey);

        if (!$reward) {
            throw new Exception('Phần thưởng này hiện không khả dụng hoặc đã hết.');
        }

        if ($user->points < $reward->points_required) {
            throw new Exception("Bạn không đủ điểm để đổi phần thưởng này (Hiện có: {$user->points} pts, Yêu cầu: {$reward->points_required} pts).");
        }

        // Trừ điểm thưởng của người dùng
        $user->points -= $reward->points_required;
        $user->save();

        // Sinh mã voucher duy nhất
        $prefix = match ($reward->target) {
            'ticket' => 'FREETICKET',
            'combo' => 'FREESNACK',
            default => 'REWARD',
        };
        $voucherCode = strtoupper($prefix . '-' . substr(md5(uniqid((string) mt_rand(), true)), 0, 6));

        $voucherData = [
            'code' => $voucherCode,
            'title' => $reward->title,
            'description' => "Đổi từ {$reward->points_required} điểm CinePoint của {$user->name}",
            'target' => $reward->target,
            'discount_type' => $reward->discount_type,
            'discount_value' => $reward->discount_value,
            'min_order_amount' => $reward->min_order_amount ?? 0,
            'usage_limit' => 1,
            'used_count' => 0,
            'expires_at' => Carbon::now()->addMonths(3),
            'is_active' => true,
        ];

        if (Schema::hasColumn('vouchers', 'user_id')) {
            $voucherData['user_id'] = $user->id;
        }

        $voucher = Voucher::create($voucherData);

        // Gửi email xác nhận qua Queue Job
        try {
            if (!empty($user->email)) {
                \App\Jobs\SendWelcomeVoucherEmailJob::dispatch(
                    user: $user,
                    voucher: $voucher,
                    badgeText: 'Đổi Thưởng CinePoints',
                    customMessage: "Bạn đã đổi thành công phần thưởng [{$reward->title}] bằng {$reward->points_required} điểm CinePoints:",
                    subjectTitle: "🎁 [CineReserve] Quà tặng đổi thưởng: Mã {$voucherCode} dành cho bạn"
                );
            }
        } catch (Exception $e) {
            Log::error('Lỗi dispatch Queue Job đổi thưởng: ' . $e->getMessage());
        }

        return [
            'voucher' => $voucher,
            'remaining_points' => $user->points,
        ];
    }
}
