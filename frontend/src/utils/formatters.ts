export const formatVnd = (val?: number): string => {
  if (!val) return '0 đ';
  return new Intl.NumberFormat('vi-VN').format(val) + ' đ';
};

export const formatVndPrice = formatVnd;

export const formatDate = (val?: string): string => {
  if (!val) return 'Hôm nay';
  const clean = val.split('T')[0].split(' ')[0];
  const parts = clean.split('-');
  if (parts.length === 3) return `${parts[2]}/${parts[1]}/${parts[0]}`;
  return clean;
};

export const formatDateShort = (val?: string): string => {
  if (!val) return 'Hôm nay';
  const clean = val.split('T')[0].split(' ')[0];
  const parts = clean.split('-');
  if (parts.length === 3) return `${parts[2]}/${parts[1]}`;
  return clean;
};

export const formatStatus = (status: string): string => {
  switch (status) {
    case 'now_showing': return '🟢 Đang Chiếu';
    case 'early_premiere': return '✨ Suất Chiếu Sớm';
    default: return '⏳ Sắp Chiếu';
  }
};

export const getBadgeVariant = (status: string): 'emerald' | 'purple' | 'amber' => {
  switch (status) {
    case 'now_showing': return 'emerald';
    case 'early_premiere': return 'purple';
    default: return 'amber';
  }
};

/**
 * Kiểm tra xem một suất chiếu có bị quá giờ/hết hạn so với thời gian hiện tại hay không.
 * Cho phép du di 10 phút sau giờ bắt đầu (grace period vào rạp trễ).
 */
export const isShowtimeExpired = (showtime: { start_time: string; start_date?: string }, targetDate?: string): boolean => {
  if (!showtime || !showtime.start_time) return false;
  
  const now = new Date();
  const dateStr = targetDate || showtime.start_date || now.toISOString().split('T')[0];
  
  const cleanDate = dateStr.split('T')[0].split(' ')[0];
  const parts = cleanDate.split('-').map(Number);
  if (parts.length !== 3) return false;
  
  const timeParts = showtime.start_time.split(':').map(Number);
  if (timeParts.length < 2) return false;
  
  const showtimeDate = new Date(parts[0], parts[1] - 1, parts[2], timeParts[0], timeParts[1], 0, 0);
  
  // Hết hạn nếu thời gian hiện tại đã vượt quá giờ chiếu + 10 phút
  return now.getTime() > (showtimeDate.getTime() + 10 * 60 * 1000);
};

/**
 * Trả về thông tin nhãn phân loại độ tuổi điện ảnh Việt Nam (T18, T16, T13, K, P)
 */
export const getAgeRatingInfo = (rating?: string) => {
  const code = (rating || 'T18').toUpperCase();
  switch (code) {
    case 'T18':
      return {
        code: 'T18',
        label: '18+ (Cấm khán giả dưới 18 tuổi)',
        shortLabel: 'T18',
        badgeVariant: 'rose' as const,
        description: 'Phim được phổ biến đến người xem từ đủ 18 tuổi trở lên. Yêu cầu kiểm tra CCCD.',
        warningText: '🔞 PHIM DÁN NHÃN T18: Khán giả phải từ đủ 18 tuổi trở lên. Nhân viên vui lòng kiểm tra CCCD bằng mắt thường trước khi cho vào rạp!',
        isRestricted: true,
      };
    case 'T16':
      return {
        code: 'T16',
        label: '16+ (Cấm khán giả dưới 16 tuổi)',
        shortLabel: 'T16',
        badgeVariant: 'amber' as const,
        description: 'Phim được phổ biến đến người xem từ đủ 16 tuổi trở lên.',
        warningText: '⚠️ PHIM DÁN NHÃN T16: Yêu cầu khán giả từ đủ 16 tuổi trở lên.',
        isRestricted: true,
      };
    case 'T13':
      return {
        code: 'T13',
        label: '13+ (Cấm khán giả dưới 13 tuổi)',
        shortLabel: 'T13',
        badgeVariant: 'cyan' as const,
        description: 'Phim được phổ biến đến người xem từ đủ 13 tuổi trở lên.',
        warningText: 'ℹ️ PHIM T13: Khán giả từ đủ 13 tuổi trở lên.',
        isRestricted: false,
      };
    case 'K':
      return {
        code: 'K',
        label: 'K (Khán giả dưới 13 tuổi cần người giám hộ)',
        shortLabel: 'K',
        badgeVariant: 'gold' as const,
        description: 'Phim được phổ biến đến người xem dưới 13 tuổi và có người giám hộ đi kèm.',
        warningText: 'ℹ️ PHIM K: Khán giả dưới 13 tuổi cần có người giám hộ đi cùng.',
        isRestricted: false,
      };
    case 'P':
    default:
      return {
        code: 'P',
        label: 'P (Mọi lứa tuổi)',
        shortLabel: 'P',
        badgeVariant: 'emerald' as const,
        description: 'Phim được phép phổ biến rộng rãi đến người xem ở mọi độ tuổi.',
        warningText: '✅ PHIM P: Phù hợp với mọi độ tuổi khán giả.',
        isRestricted: false,
      };
  }
};
