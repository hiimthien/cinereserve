/**
 * Centralized Constants for Cinema Domains, Cities, Chains and Booking Configurations
 */

export const CITIES = [
  'Hồ Chí Minh',
  'Hà Nội',
  'Đà Nẵng',
  'Hải Phòng',
  'Cần Thơ',
  'Bình Dương',
  'Đồng Nai',
  'Bà Rịa - Vũng Tàu',
  'Khánh Hòa',
  'Lâm Đồng',
  'Quảng Ninh',
  'Thừa Thiên Huế',
  'Nghệ An',
] as const;

export const CITY_OPTIONS = [
  { label: 'Tất Cả Tỉnh / Thành', value: 'Tất cả' },
  ...CITIES.map(c => ({ label: c, value: c })),
] as const;

export const ADMIN_CITY_OPTIONS = [
  { label: 'Tất Cả Tỉnh / Thành', value: 'all' },
  ...CITIES.map(c => ({ label: c, value: c })),
] as const;

export const CINEMA_CHAINS = [
  { name: 'CGV Cinema', code: 'CGV' },
  { name: 'Lotte Cinema', code: 'Lotte' },
  { name: 'Galaxy Cinema', code: 'Galaxy' },
  { name: 'BHD Star Cineplex', code: 'BHD' },
  { name: 'Cinestar', code: 'Cinestar' },
  { name: 'Beta Cinemas', code: 'Beta' },
  { name: 'Mega GS Cinema', code: 'Mega GS' },
  { name: 'Trung Tâm Chiếu Phim QG', code: 'NCC' },
] as const;

export const CINEMA_CHAIN_OPTIONS = [
  { label: 'Tất Cả Chuỗi Rạp', value: 'Tất cả' },
  ...CINEMA_CHAINS.map(c => ({ label: c.name, value: c.code })),
] as const;

export const MEMBERSHIP_TIERS = [
  { label: 'CineMember (Bạc)', value: 'member', badge: '🥈' },
  { label: 'CineVIP (Vàng)', value: 'vip', badge: '🥇' },
  { label: 'CineDiamond (Kim Cương)', value: 'diamond', badge: '💎' },
] as const;

export const USER_ROLES = [
  { label: '👑 Admin (Quản Trị)', value: 'admin' },
  { label: '🎫 Staff (Nhân Viên)', value: 'staff' },
  { label: '👤 Khách Hàng', value: 'user' },
] as const;

export const BOOKING_STATUS_TABS = [
  { label: 'Tất Cả Vé', value: 'all' },
  { label: '✅ Đã Thanh Toán', value: 'confirmed' },
  { label: '🎟️ Đã Soát Vé', value: 'checked_in' },
  { label: '❌ Đã Hủy', value: 'cancelled' },
] as const;

export const PAYMENT_METHODS = [
  { id: 'vnpay', name: 'VNPay QR / Thẻ nội địa' },
  { id: 'momo', name: 'Ví MoMo' },
  { id: 'credit_card', name: 'Thẻ Visa / Mastercard' },
] as const;

export interface CuratedGenre {
  id: string;
  label: string;
  iconName: string;
  keywords: string[];
}

export const CURATED_GENRES: CuratedGenre[] = [
  { id: 'all', label: 'Tất cả', iconName: 'Clapperboard', keywords: [] },
  { id: 'action', label: 'Hành Động', iconName: 'Flame', keywords: ['action', 'hành động'] },
  { id: 'animation', label: 'Hoạt Hình', iconName: 'Sparkles', keywords: ['animation', 'hoạt hình', 'anime'] },
  { id: 'scifi', label: 'Viễn Tưởng', iconName: 'Rocket', keywords: ['sci-fi', 'viễn tưởng', 'khoa học'] },
  { id: 'horror', label: 'Kinh Dị & Bí Ẩn', iconName: 'Ghost', keywords: ['horror', 'kinh dị', 'bí ẩn', 'mystery', 'giật gân', 'thriller'] },
  { id: 'comedy', label: 'Hài Hước', iconName: 'Smile', keywords: ['comedy', 'hài'] },
  { id: 'adventure', label: 'Phiêu Lưu', iconName: 'Compass', keywords: ['adventure', 'phiêu lưu'] },
  { id: 'drama', label: 'Tâm Lý - Chính Kịch', iconName: 'Theater', keywords: ['drama', 'chính kịch', 'tâm lý', 'biography', 'history'] },
  { id: 'romance', label: 'Lãng Mạn', iconName: 'Heart', keywords: ['romance', 'lãng mạn', 'tình cảm'] },
  { id: 'family', label: 'Gia Đình', iconName: 'Users', keywords: ['family', 'gia đình'] },
];
