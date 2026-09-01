import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/authStore';
import { useToast } from '../composables/useToast';

// Eager load only HomeView for instant LCP (Largest Contentful Paint)
import HomeView from '../views/HomeView.vue';

// Dynamic Lazy Loading for all secondary and admin routes (Code Splitting)
const NowShowingView = () => import('../views/NowShowingView.vue');
const ComingSoonView = () => import('../views/ComingSoonView.vue');
const MovieDetailView = () => import('../views/MovieDetailView.vue');
const CinemasView = () => import('../views/CinemasView.vue');
const SeatSelectionView = () => import('../views/SeatSelectionView.vue');
const CheckoutView = () => import('../views/CheckoutView.vue');
const TicketConfirmationView = () => import('../views/TicketConfirmationView.vue');
const MyTicketsView = () => import('../views/MyTicketsView.vue');
const StaffScannerView = () => import('../views/StaffScannerView.vue');

// Admin views (Lazy loaded in a separate admin bundle)
const AdminLayout = () => import('../views/admin/AdminLayout.vue');
const AdminDashboardView = () => import('../views/admin/AdminDashboardView.vue');
const AdminMoviesView = () => import('../views/admin/AdminMoviesView.vue');
const AdminShowtimesView = () => import('../views/admin/AdminShowtimesView.vue');
const AdminRoomsView = () => import('../views/admin/AdminRoomsView.vue');
const AdminCinemasView = () => import('../views/admin/AdminCinemasView.vue');
const AdminUsersView = () => import('../views/admin/AdminUsersView.vue');
const AdminSnacksView = () => import('../views/admin/AdminSnacksView.vue');
const AdminVouchersView = () => import('../views/admin/AdminVouchersView.vue');
const AdminBookingsView = () => import('../views/admin/AdminBookingsView.vue');

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
    path: '/booking/:slug/:showtimeId?',
    name: 'seat-selection',
    component: SeatSelectionView,
    props: (route: any) => ({
      slug: route.params.slug,
      showtimeId: route.params.showtimeId || route.query.showtime || route.query.st,
    }),
    meta: { title: 'Chọn Ghế Ngồi Trực Tiếp | CineReserve' },
  },
  {
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
      requiresRole: 'staff',
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
        path: 'cinemas',
        name: 'admin-cinemas',
        component: AdminCinemasView,
        meta: { title: 'Quản Lý Cụm Rạp Chiếu | CineAdmin', requiresRole: 'admin' },
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
      {
        path: 'users',
        name: 'admin-users',
        component: AdminUsersView,
        meta: { title: 'Quản Lý Người Dùng & Phân Quyền | CineAdmin', requiresRole: 'admin' },
      },
    ],
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior() {
    return { top: 0 };
  },
});

// RBAC Role-Based Navigation Guard
router.beforeEach((to, _from, next) => {
  const authStore = useAuthStore();
  const toast = useToast();
  const requiresRole = to.meta?.requiresRole;

  if (requiresRole === 'admin') {
    if (!authStore.isAdmin) {
      toast.warning('Bạn không có quyền truy cập khu vực Quản trị viên (Admin).', 'Yêu Cầu Quyền Admin');
      authStore.openAuth('login');
      return next({ name: 'home' });
    }
  } else if (requiresRole === 'staff') {
    if (!authStore.isStaff) {
      toast.warning('Bạn không có quyền truy cập khu vực Soát vé Nhân viên.', 'Yêu Cầu Quyền Staff');
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
