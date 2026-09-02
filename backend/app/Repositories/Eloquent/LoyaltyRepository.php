<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\LoyaltyReward;
use App\Models\User;
use App\Models\Voucher;
use App\Repositories\Contracts\LoyaltyRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Schema;

class LoyaltyRepository implements LoyaltyRepositoryInterface
{
    public function getActiveRewards(): Collection
    {
        return LoyaltyReward::where('is_active', true)
            ->orderBy('points_required', 'asc')
            ->get();
    }

    public function findRewardByKey(string $key): ?LoyaltyReward
    {
        return LoyaltyReward::where('reward_key', $key)
            ->where('is_active', true)
            ->first();
    }

    public function getUserVouchers(User $user): Collection
    {
        return Voucher::where('is_active', true)
            ->where(function ($q) use ($user) {
                if (Schema::hasColumn('vouchers', 'user_id')) {
                    $q->where('user_id', $user->id);
                }
                $q->orWhere('description', 'like', "%{$user->name}%")
                  ->orWhere('description', 'like', "%{$user->email}%");
            })
            ->orderByDesc('created_at')
            ->get();
    }
}
