<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\LoyaltyReward;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Database\Eloquent\Collection;

interface LoyaltyRepositoryInterface
{
    /**
     * @return Collection<int, LoyaltyReward>
     */
    public function getActiveRewards(): Collection;

    public function findRewardByKey(string $key): ?LoyaltyReward;

    /**
     * @return Collection<int, Voucher>
     */
    public function getUserVouchers(User $user): Collection;
}
