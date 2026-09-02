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

export interface LoyaltyRewardItem {
  id: string;
  points_required: number;
  title: string;
  description: string;
  badge: string;
}

export const DEFAULT_LOYALTY_REWARDS: LoyaltyRewardItem[] = [
  { id: 'voucher_20k', points_required: 50, title: 'Voucher Giảm 20.000 đ', description: 'Áp dụng cho mọi đơn đặt vé từ 95.000 đ', badge: 'Phổ biến' },
  { id: 'free_snack', points_required: 100, title: 'Miễn Phí 1 Solo Combo Bắp Nước', description: 'Tặng 1 Bắp rang bơ nóng hổi + 1 Nước ngọt lớn tại quầy', badge: 'Bắp Nước Free' },
  { id: 'voucher_50k', points_required: 150, title: 'Voucher Giảm 50.000 đ', description: 'Áp dụng cho đơn hàng tổng từ 150.000 đ trở lên', badge: 'Tiết kiệm lớn' },
  { id: 'free_ticket', points_required: 250, title: 'Miễn Phí 1 Vé Xem Phim Tiêu Chuẩn', description: 'Miễn phí 100% 1 vé xem phim 2D/3D bất kỳ trị giá 95.000 đ', badge: 'Vé Miễn Phí' },
  { id: 'vip_couple_pass', points_required: 400, title: 'Gói Trọn Gói Siêu VIP Đôi', description: '2 Vé Phim Ghế VIP/Couple + 1 Couple Combo Bắp Nước lớn', badge: 'Đặc Quyền VVIP' },
];

export interface SnackComboItem {
  id: number | string;
  name: string;
  description: string;
  price: number;
  image_url?: string;
  badge?: string;
}

export const DEFAULT_SNACK_COMBOS: SnackComboItem[] = [
  { id: 1, name: 'Solo Combo', description: '1 Bắp ngọt nóng hổi (60oz) + 1 Ly nước ngọt có ga (22oz)', price: 69000, badge: 'Tiết Kiệm', image_url: 'https://images.unsplash.com/photo-1578849278619-e73505e9610f?w=300' },
  { id: 2, name: 'Couple Combo', description: '1 Bắp phô mai size L (85oz) + 2 Ly nước ngọt (32oz)', price: 109000, badge: 'Phổ Biến Nhất', image_url: 'https://images.unsplash.com/photo-1585647347483-22b66260dfff?w=300' },
  { id: 3, name: 'Party VIP Combo', description: '2 Bắp Caramel lớn + 4 Nước + 1 Khoai tây chiên', price: 169000, badge: 'Ưu Đãi Nhóm', image_url: 'https://images.unsplash.com/photo-1505686994434-e3cc5abf1330?w=300' },
  { id: 4, name: 'Nachos Cheese Combo', description: '1 Khay bánh bắp sốt phô mai & salsa + 1 Nước ngọt lớn', price: 89000, badge: 'Món Mới', image_url: 'https://images.unsplash.com/photo-1513456852971-30c0b8199d4d?w=300' },
];

export const ADMIN_BOOKING_STATUS_TABS = [
  { label: 'Tất Cả Đơn', value: 'all' },
  { label: '⏳ Chưa Soát Vé', value: 'confirmed' },
  { label: '✅ Đã Check-in', value: 'checked_in' },
  { label: '❌ Đã Hủy', value: 'cancelled' },
] as const;

