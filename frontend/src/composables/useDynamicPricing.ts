import type { Showtime, Seat } from '../types';

export interface DynamicPricingInfo {
  rule: 'happy_wednesday' | 'weekend_surge' | 'early_or_late' | 'standard';
  badge: string | null;
  badgeVariant: 'emerald' | 'rose' | 'amber' | 'neutral';
  iconName: 'Sparkles' | 'Flame' | 'Moon' | null;
  isHappyWednesday: boolean;
  isPeakHour: boolean;
  isEarlyOrLate: boolean;
  priceStandard: number;
  priceVip: number;
  priceCouple: number;
}

export function useDynamicPricing() {
  /**
   * Tính toán biểu giá vé động dựa trên ngày và giờ suất chiếu
   */
  const getDynamicPricing = (showtime?: Showtime | null, targetDateStr?: string): DynamicPricingInfo => {
    if (!showtime) {
      return {
        rule: 'standard',
        badge: null,
        badgeVariant: 'neutral',
        iconName: null,
        isHappyWednesday: false,
        isPeakHour: false,
        isEarlyOrLate: false,
        priceStandard: 95000,
        priceVip: 110000,
        priceCouple: 200000,
      };
    }

    const dateStr = targetDateStr || (showtime as any).date || showtime.show_date || new Date().toISOString().split('T')[0];
    const showDate = new Date(dateStr);
    const dayOfWeek = showDate.getDay(); // 0 = Sunday, 1 = Monday, 2 = Tuesday, 3 = Wednesday, ... 6 = Saturday

    const timeStr = showtime.start_time || '19:00';
    const hour = parseInt(timeStr.split(':')[0], 10) || 19;

    const base = Number(showtime.base_price || 95000);
    const vipBase = (showtime as any).price_vip ? Number((showtime as any).price_vip) : (base + 15000);
    const coupleBase = (showtime as any).price_couple ? Number((showtime as any).price_couple) : (base * 2);

    const isHappyWednesday = dayOfWeek === 3;
    const isWeekend = dayOfWeek === 0 || dayOfWeek === 5 || dayOfWeek === 6; // Fri, Sat, Sun
    const isPeakHour = isWeekend && hour >= 18 && hour <= 23;
    const isEarlyOrLate = !isHappyWednesday && !isWeekend && (hour < 10 || hour >= 23);

    // 1. Thứ 4 Vui Vẻ: Đồng giá 55k ghế thường
    if (isHappyWednesday) {
      return {
        rule: 'happy_wednesday',
        badge: 'Thứ 4 Vui Vẻ 55K',
        badgeVariant: 'emerald',
        iconName: 'Sparkles',
        isHappyWednesday: true,
        isPeakHour: false,
        isEarlyOrLate: false,
        priceStandard: 55000,
        priceVip: 70000,
        priceCouple: 130000,
      };
    }

    // 2. Khung Giờ Vàng Cuối Tuần: Phụ thu +15k/vé
    if (isPeakHour) {
      return {
        rule: 'weekend_surge',
        badge: 'Giờ Vàng Cuối Tuần',
        badgeVariant: 'rose',
        iconName: 'Flame',
        isHappyWednesday: false,
        isPeakHour: true,
        isEarlyOrLate: false,
        priceStandard: base + 15000,
        priceVip: vipBase + 15000,
        priceCouple: coupleBase + 30000,
      };
    }

    // 3. Suất Chiếu Sớm / Khuya: Giảm -15k/vé
    if (isEarlyOrLate) {
      return {
        rule: 'early_or_late',
        badge: 'Suất Sớm/Khuya -15K',
        badgeVariant: 'amber',
        iconName: 'Moon',
        isHappyWednesday: false,
        isPeakHour: false,
        isEarlyOrLate: true,
        priceStandard: Math.max(45000, base - 15000),
        priceVip: Math.max(60000, vipBase - 15000),
        priceCouple: Math.max(100000, coupleBase - 30000),
      };
    }

    // 4. Suất Tiêu Chuẩn
    return {
      rule: 'standard',
      badge: null,
      badgeVariant: 'neutral',
      iconName: null,
      isHappyWednesday: false,
      isPeakHour: false,
      isEarlyOrLate: false,
      priceStandard: base,
      priceVip: vipBase,
      priceCouple: coupleBase,
    };
  };

  /**
   * Tính giá cho từng ghế theo biểu giá động
   */
  const getDynamicSeatPrice = (seat: Seat, showtime?: Showtime | null, targetDateStr?: string): number => {
    const pricing = getDynamicPricing(showtime, targetDateStr);
    if (seat.type === 'couple') return pricing.priceCouple;
    if (seat.type === 'vip') return pricing.priceVip;
    return pricing.priceStandard;
  };

  return {
    getDynamicPricing,
    getDynamicSeatPrice,
  };
}
