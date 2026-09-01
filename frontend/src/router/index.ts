import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/authStore';
import HomeView from '../views/HomeView.vue';
import NowShowingView from '../views/NowShowingView.vue';
import ComingSoonView from '../views/ComingSoonView.vue';
import MovieDetailView from '../views/MovieDetailView.vue';
import CinemasView from '../views/CinemasView.vue';
import SeatSelectionView from '../views/SeatSelectionView.vue';
import CheckoutView from '../views/CheckoutView.vue';
import TicketConfirmationView from '../views/TicketConfirmationView.vue';
import MyTicketsView from '../views/MyTicketsView.vue';
import StaffScannerView from '../views/StaffScannerView.vue';

// Admin views
import AdminLayout from '../views/admin/AdminLayout.vue';
import AdminDashboardView from '../views/admin/AdminDashboardView.vue';
import AdminMoviesView from '../views/admin/AdminMoviesView.vue';
import AdminShowtimesView from '../views/admin/AdminShowtimesView.vue';
import AdminRoomsView from '../views/admin/AdminRoomsView.vue';
import AdminSnacksView from '../views/admin/AdminSnacksView.vue';
import AdminVouchersView from '../views/admin/AdminVouchersView.vue';
import AdminBookingsView from '../views/admin/AdminBookingsView.vue';


const routes = [
  {
    path: '/',
    name: 'home',
    component: HomeView,
    meta: { title: 'CineReserve - Trang Chủ Đặt Vé Xem Phim Online' },
  },
  {
    path: '/now-showing',
    alias: '/phim-dang-chieu',
    name: 'now-showing',
    component: NowShowingView,
    meta: { title: 'Phim Đang Chiếu Tại Rạp | CineReserve' },
  },
  {
    path: '/coming-soon',
    alias: '/phim-sap-chieu',
    name: 'coming-soon',
    component: ComingSoonView,
    meta: { title: 'Phim Sắp Chiếu - Bom Tấn Sắp Ra Mắt | CineReserve' },
  },
  {
    path: '/cinemas',
    name: 'cinemas',
    component: CinemasView,
    meta: { title: 'Cụm Rạp & Lịch Chiếu Toàn Quốc | CineReserve' },
  },
  {
    path: '/movie/:slug',
    name: 'movie-detail',
    component: MovieDetailView,
    props: true,
    meta: { title: 'Chi Tiết Phim & Suất Chiếu | CineReserve' },
  },
  {
    // Clean SEO & Semantic Booking URL
    path: '/booking/:slug/:showtimeId?',
    name: 'seat-selection',
    component: SeatSelectionView,
    props: (route: any) => ({
      slug: route.params.slug,
      showtimeId: route.params.showtimeId || route.query.showtime || route.query.st
    }),
    meta: { title: 'Chọn Ghế Ngồi Trực Tiếp | CineReserve' },
  },
  {
    // Legacy alias
    path: '/showtime/:showtimeId/seats',
    name: 'legacy-seat-selection',
    component: SeatSelectionView,
    props: true,
    meta: { title: 'Chọn Ghế Ngồi | CineReserve' },
  },
  {
    path: '/checkout',
    name: 'checkout',
    component: CheckoutView,
    meta: { title: 'Xác Nhận & Thanh Toán Vé | CineReserve' },
  },
  {
    path: '/ticket/confirmation',
    name: 'ticket-confirmation',
    component: TicketConfirmationView,
    meta: { title: 'Đặt Vé Thành Công - E-Ticket Pass | CineReserve' },
  },
  {
    path: '/my-tickets',
    name: 'my-tickets',
    component: MyTicketsView,
    meta: { title: 'Vé Của Tôi | CineReserve' },
  },
  {
    path: '/staff/scanner',
    name: 'staff-scanner',
    component: StaffScannerView,
    meta: { 
      title: 'Máy Quét QR Soát Vé Nhân Viên | CineReserve Staff',
      requiresRole: 'staff' 
    },
  },

  // 📊 Admin Dashboard Routes (Protected with Admin Role)
  {
    path: '/admin',
    component: AdminLayout,
    meta: { requiresRole: 'admin' },
    children: [
      {
        path: '',
        name: 'admin-dashboard',
        component: AdminDashboardView,
        meta: { title: 'Tổng Quan & Doanh Thu | CineAdmin', requiresRole: 'admin' },
      },
      {
        path: 'movies',
        name: 'admin-movies',
        component: AdminMoviesView,
        meta: { title: 'Quản Lý Danh Mục Phim | CineAdmin', requiresRole: 'admin' },
      },
      {
        path: 'showtimes',
        name: 'admin-showtimes',
        component: AdminShowtimesView,
        meta: { title: 'Quản Lý Lịch & Suất Chiếu | CineAdmin', requiresRole: 'admin' },
      },
      {
        path: 'rooms',
        name: 'admin-rooms',
        component: AdminRoomsView,
        meta: { title: 'Quản Lý Rạp & Ma Trận Ghế | CineAdmin', requiresRole: 'admin' },
      },
      {
        path: 'snacks',
        name: 'admin-snacks',
        component: AdminSnacksView,
        meta: { title: 'Quản Lý Bắp Nước & Combo | CineAdmin', requiresRole: 'admin' },
      },
      {
        path: 'vouchers',
        name: 'admin-vouchers',
        component: AdminVouchersView,
        meta: { title: 'Quản Lý Mã Giảm Giá & Voucher | CineAdmin', requiresRole: 'admin' },
      },
      {
        path: 'bookings',
        name: 'admin-bookings',
        component: AdminBookingsView,
        meta: { title: 'Quản Lý Toàn Bộ Đơn Vé | CineAdmin', requiresRole: 'admin' },
      },
    ],
  },

];

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior() {
    return { top: 0 };
  }
});

// RBAC Role-Based Navigation Guard
router.beforeEach((to, _from, next) => {
  const authStore = useAuthStore();
  const requiresRole = to.meta?.requiresRole;

  if (requiresRole === 'admin') {
    if (!authStore.isAdmin) {
      alert('⚠️ Bạn không có quyền truy cập khu vực Quản trị viên (Admin). Vui lòng đăng nhập tài khoản có quyền Admin.');
      authStore.openAuth('login');
      return next({ name: 'home' });
    }
  } else if (requiresRole === 'staff') {
    if (!authStore.isStaff) {
      alert('⚠️ Bạn không có quyền truy cập khu vực Soát vé Nhân viên (Staff/Admin).');
      authStore.openAuth('login');
      return next({ name: 'home' });
    }
  }

  next();
});

// Dynamic Document Title Hook
router.afterEach((to) => {
  const defaultTitle = 'CineReserve - Hệ Thống Đặt Vé Phim Realtime';
  if (to.meta && to.meta.title) {
    document.title = String(to.meta.title);
  } else {
    document.title = defaultTitle;
  }
});

export default router;
