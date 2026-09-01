<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Seat;
use App\Models\Showtime;
use Carbon\Carbon;

class PricingService
{
    /**
     * Tính toán giá vé động và huy hiệu ưu đãi cho suất chiếu
     */
    public function calculateDynamicPricing(Showtime $showtime): array
    {
        $dateStr = $showtime->date ?? $showtime->show_date;
        $showDate = $dateStr ? Carbon::parse($dateStr) : Carbon::today();
        
        $startTimeStr = $showtime->start_time ?: '19:00';
        $hour = (int) explode(':', $startTimeStr)[0];

        $base = (float) ($showtime->base_price ?: 95000);
        $vipBase = isset($showtime->price_vip) && (float)$showtime->price_vip > 0 
            ? (float) $showtime->price_vip 
            : ($base + 15000);
        $coupleBase = isset($showtime->price_couple) && (float)$showtime->price_couple > 0 
            ? (float) $showtime->price_couple 
            : ($base * 2);

        $isHappyWednesday = $showDate->isWednesday();
        $isWeekend = in_array($showDate->dayOfWeek, [Carbon::FRIDAY, Carbon::SATURDAY, Carbon::SUNDAY]);
        $isPeakHour = $isWeekend && ($hour >= 18 && $hour <= 23);
        $isEarlyOrLate = !$isHappyWednesday && !$isWeekend && ($hour < 10 || $hour >= 23);

        $badge = null;
        $badgeVariant = 'neutral';
        $rule = 'standard';

        // 1. Thứ 4 Vui Vẻ: Đồng giá 55k ghế thường, VIP 70k, Couple 130k
        if ($isHappyWednesday) {
            $rule = 'happy_wednesday';
            $badge = '🎉 Thứ 4 Vui Vẻ - 55K';
            $badgeVariant = 'emerald';
            $calculatedStandard = 55000.0;
            $calculatedVip = 70000.0;
            $calculatedCouple = 130000.0;
        }
        // 2. Khung Giờ Vàng Cuối Tuần: Phụ thu +15k/vé
        elseif ($isPeakHour) {
            $rule = 'weekend_surge';
            $badge = '🔥 Giờ Vàng Cuối Tuần (+15K)';
            $badgeVariant = 'rose';
            $calculatedStandard = $base + 15000.0;
            $calculatedVip = $vipBase + 15000.0;
            $calculatedCouple = $coupleBase + 30000.0;
        }
        // 3. Suất Chiếu Sớm / Khuya: Giảm -15k/vé
        elseif ($isEarlyOrLate) {
            $rule = 'early_or_late';
            $badge = '🌙 Suất Sáng / Khuya (-15K)';
            $badgeVariant = 'amber';
            $calculatedStandard = max(45000.0, $base - 15000.0);
            $calculatedVip = max(60000.0, $vipBase - 15000.0);
            $calculatedCouple = max(100000.0, $coupleBase - 30000.0);
        }
        // 4. Suất Chiếu Tiêu Chuẩn
        else {
            $calculatedStandard = $base;
            $calculatedVip = $vipBase;
            $calculatedCouple = $coupleBase;
        }

        return [
            'rule' => $rule,
            'badge' => $badge,
            'badge_variant' => $badgeVariant,
            'is_happy_wednesday' => $isHappyWednesday,
            'is_peak_hour' => $isPeakHour,
            'is_early_or_late' => $isEarlyOrLate,
            'price_standard' => $calculatedStandard,
            'price_vip' => $calculatedVip,
            'price_couple' => $calculatedCouple,
            'original_standard' => $base,
            'original_vip' => $vipBase,
            'original_couple' => $coupleBase,
        ];
    }

    /**
     * Tính toán giá chính xác cho từng ghế theo suất chiếu
     */
    public function getSeatPrice(Showtime $showtime, Seat $seat): float
    {
        $pricing = $this->calculateDynamicPricing($showtime);

        if ($seat->type === 'couple') {
            return (float) $pricing['price_couple'];
        }

        if ($seat->type === 'vip') {
            return (float) $pricing['price_vip'];
        }

        return (float) $pricing['price_standard'];
    }
}
