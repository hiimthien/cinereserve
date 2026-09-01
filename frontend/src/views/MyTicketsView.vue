<template>
  <div class="min-h-screen bg-cinema-bg flex flex-col justify-between select-none">
    <div>
      <Navbar />

      <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
        
        <!-- Header -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border-b border-white/10 pb-6">
          <div>
            <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight flex items-center gap-3">
              <Ticket class="w-8 h-8 text-cinema-accent" />
              <span>Vé Của Tôi (My Tickets)</span>
            </h1>
            <p class="text-xs sm:text-sm text-cinema-muted mt-1">
              Quản lý danh sách vé đã mua, lọc theo rạp, theo phim, tìm kiếm và xuất mã QR check-in tại quầy soát vé
            </p>
          </div>

          <router-link 
            to="/" 
            class="px-5 py-2.5 rounded-2xl bg-cinema-accent hover:bg-rose-600 text-white text-xs font-bold shadow-glow-accent transition-all flex items-center gap-2 cursor-pointer shrink-0"
          >
            <Film class="w-4 h-4" />
            <span>Đặt Vé Phim Mới</span>
          </router-link>
        </div>

        <!-- 🔍 Filter Toolbar (Tabs, Cinema Filter, Movie Filter, Search) -->
        <div class="bg-cinema-surface/70 border border-cinema-border rounded-3xl p-4 sm:p-5 backdrop-blur-xl shadow-xl space-y-4">
          
          <!-- Top Row: Status Tabs + Clear Filter -->
          <div class="flex flex-wrap items-center justify-between gap-3 border-b border-white/5 pb-3">
            <div class="flex items-center gap-2 overflow-x-auto pb-1 sm:pb-0 scrollbar-none">
              <button
                v-for="tab in statusTabs"
                :key="tab.value"
                @click="selectTab(tab.value)"
                class="px-4 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer whitespace-nowrap"
                :class="[
                  activeStatus === tab.value
                    ? 'bg-cinema-accent text-white shadow-glow-accent'
                    : 'bg-white/5 text-slate-400 hover:text-white hover:bg-white/10'
                ]"
              >
                {{ tab.label }}
              </button>
            </div>

            <!-- Clear Filter Button -->
            <button 
              v-if="selectedMovieId !== 'all' || selectedCinemaId !== 'all' || searchQuery || activeStatus !== 'all'"
              @click="resetFilters"
              class="text-xs text-rose-400 hover:text-rose-300 font-bold flex items-center gap-1.5 transition-colors cursor-pointer"
            >
              <RotateCcw class="w-3.5 h-3.5" />
              <span>Xóa Bộ Lọc</span>
            </button>
          </div>

          <!-- Bottom Row: Dropdown Filters & Search Box -->
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            
            <!-- 1. Filter by Movie -->
            <BaseSelect 
              v-model="selectedMovieId"
              label="🎬 Lọc theo Phim"
              @update:model-value="onFilterChange"
            >
              <option value="all">Tất cả phim</option>
              <option v-for="m in availableMovies" :key="m.id" :value="m.id">
                {{ m.title }}
              </option>
            </BaseSelect>

            <!-- 2. Filter by Cinema -->
            <BaseSelect 
              v-model="selectedCinemaId"
              label="🏢 Lọc theo Cụm Rạp"
              @update:model-value="onFilterChange"
            >
              <option value="all">Tất cả cụm rạp</option>
              <option v-for="c in availableCinemas" :key="c.id" :value="c.id">
                {{ c.name }} ({{ c.city }})
              </option>
            </BaseSelect>

            <!-- 3. Search Input -->
            <div class="space-y-1 sm:col-span-2">
              <label class="block text-[11px] font-bold text-slate-400">🔍 Tìm kiếm mã vé / Tên người đặt</label>
              <div class="relative">
                <Search class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
                <input 
                  v-model="searchQuery"
                  @input="handleSearch"
                  type="text"
                  placeholder="Nhập mã vé (CR-...), tên phim, số điện thoại..."
                  class="w-full bg-slate-900/90 border border-cinema-border rounded-xl pl-9 pr-4 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-cinema-accent transition-colors"
                />
              </div>
            </div>

          </div>

        </div>

        <!-- Loading Skeleton -->
        <div v-if="isLoading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div v-for="i in 6" :key="i" class="p-6 rounded-3xl bg-cinema-surface/50 border border-white/5 animate-pulse space-y-4">
            <div class="h-6 bg-white/10 rounded-lg w-1/3"></div>
            <div class="h-12 bg-white/5 rounded-xl w-full"></div>
            <div class="h-24 bg-white/5 rounded-xl w-full"></div>
          </div>
        </div>

        <!-- Guest Unauthenticated State -->
        <div v-else-if="!authStore.isAuthenticated" class="p-16 text-center bg-cinema-surface/40 border border-cinema-border rounded-3xl space-y-4 shadow-xl">
          <div class="w-16 h-16 rounded-full bg-cinema-accent/10 border border-cinema-accent/20 flex items-center justify-center mx-auto text-cinema-accent">
            <User class="w-8 h-8" />
          </div>
          <h3 class="text-lg font-black text-white">Vui lòng đăng nhập để xem vé của bạn</h3>
          <p class="text-xs text-cinema-muted max-w-sm mx-auto">
            Đăng nhập bằng tài khoản của bạn để xem danh sách vé điện tử đã mua và xuất mã QR vào phòng chiếu.
          </p>
          <div class="pt-2">
            <BaseButton 
              variant="primary" 
              size="lg" 
              @click="authStore.openAuth('login')"
            >
              <template #prefix><LogIn class="w-4 h-4" /></template>
              <span>Đăng Nhập Ngay</span>
            </BaseButton>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else-if="tickets.length === 0" class="p-16 text-center bg-cinema-surface/30 border border-cinema-border rounded-3xl space-y-4 shadow-xl">
          <div class="w-16 h-16 rounded-full bg-white/5 flex items-center justify-center mx-auto text-cinema-muted">
            <Ticket class="w-8 h-8 text-slate-500" />
          </div>
          <h3 class="text-base font-bold text-white">Bạn chưa có vé nào</h3>
          <p class="text-xs text-cinema-muted max-w-sm mx-auto">
            {{ searchQuery || selectedMovieId !== 'all' || selectedCinemaId !== 'all' ? 'Không tìm thấy vé nào phù hợp với bộ lọc đã chọn.' : 'Bạn chưa có giao dịch đặt vé nào với tài khoản này. Hãy đặt vé xem phim ngay hôm nay!' }}
          </p>
          <div class="flex items-center justify-center gap-3">
            <BaseButton 
              v-if="selectedMovieId !== 'all' || selectedCinemaId !== 'all' || searchQuery"
              variant="secondary"
              size="md"
              @click="resetFilters"
            >
              Xem Tất Cả Vé
            </BaseButton>
            <router-link 
              to="/" 
              class="inline-flex items-center gap-2 px-6 py-2.5 rounded-2xl bg-cinema-accent text-white text-xs font-bold shadow-glow-accent hover:bg-rose-600 transition-colors cursor-pointer"
            >
              <Film class="w-4 h-4" />
              <span>Khám Phá Phim Ngay</span>
            </router-link>
          </div>
        </div>

        <!-- Ticket Cards Grid -->
        <div v-else class="space-y-8">
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div 
              v-for="ticket in tickets" 
              :key="ticket.booking_code"
              @click="openTicketDetail(ticket)"
              class="group relative bg-cinema-surface/80 hover:bg-cinema-surface border border-cinema-border hover:border-cinema-accent/50 rounded-3xl overflow-hidden shadow-xl backdrop-blur-md p-6 space-y-4 transition-all duration-300 cursor-pointer hover:-translate-y-1"
            >
              <!-- Ticket Notches -->
              <div class="ticket-notch-left"></div>
              <div class="ticket-notch-right"></div>

              <!-- Top Status & Code -->
              <div class="flex items-start justify-between gap-2">
                <BaseBadge 
                  :variant="ticket.status === 'checked_in' ? 'blue' : 'emerald'"
                  size="xs"
                >
                  <template #prefix><CheckCircle2 class="w-3 h-3" /></template>
                  <span>{{ ticket.status === 'checked_in' ? 'ĐÃ SOÁT VÉ' : 'ĐÃ THANH TOÁN' }}</span>
                </BaseBadge>

                <span class="px-2 py-0.5 rounded-lg bg-amber-500/10 border border-amber-500/30 text-amber-300 text-xs font-bold font-mono">
                  {{ ticket.booking_code }}
                </span>
              </div>

              <!-- Movie Title & Cinema -->
              <div class="space-y-1">
                <h2 class="text-base font-black text-white group-hover:text-amber-400 transition-colors line-clamp-1 leading-snug">
                  {{ ticket.movie?.title || ticket.showtime?.movie?.title || 'Phim Chiếu Rạp' }}
                </h2>
                <p class="text-xs text-cinema-muted line-clamp-1 flex items-center gap-1.5">
                  <MapPin class="w-3.5 h-3.5 text-cinema-accent shrink-0" />
                  <span>{{ ticket.showtime?.cinema?.name || 'CGV Landmark 81' }}</span>
                </p>
              </div>

              <!-- Details Grid -->
              <div class="grid grid-cols-2 gap-2 text-xs border-y border-white/5 py-3">
                <div>
                  <span class="text-cinema-muted block text-[10px] font-semibold">Suất chiếu</span>
                  <span class="font-bold text-white">{{ ticket.showtime?.start_time || '09:00' }} • {{ formatDate(ticket.showtime?.show_date || ticket.created_at) }}</span>
                </div>
                <div>
                  <span class="text-cinema-muted block text-[10px] font-semibold">Phòng & Ghế</span>
                  <span class="font-bold text-emerald-400 truncate block">
                    {{ formatSeats(ticket) }}
                  </span>
                </div>
                <div>
                  <span class="text-cinema-muted block text-[10px] font-semibold">Tổng tiền</span>
                  <span class="font-bold text-amber-400 font-mono">{{ formatVnd(ticket.total_amount) }}</span>
                </div>
                <div>
                  <span class="text-cinema-muted block text-[10px] font-semibold">Khách hàng</span>
                  <span class="font-bold text-slate-300 truncate block">{{ ticket.user_name || 'Khách' }}</span>
                </div>
              </div>

              <!-- QR Pass Footer -->
              <div class="flex items-center justify-between pt-1">
                <div class="text-[11px] text-slate-400 group-hover:text-white transition-colors flex items-center gap-1.5">
                  <Eye class="w-4 h-4 text-cinema-accent" />
                  <span class="font-semibold">Xem chi tiết vé</span>
                </div>
                <img 
                  :src="ticket.qr_code || `https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=CINERESERVE-${ticket.booking_code}`" 
                  class="w-12 h-12 bg-white p-1 rounded-xl shadow-md border border-white/10 shrink-0 group-hover:scale-105 transition-transform"
                  alt="QR Pass"
                />
              </div>

            </div>
          </div>

          <!-- Reusable Base Pagination -->
          <BasePagination 
            v-model:currentPage="currentPage"
            :totalPages="totalPages"
            :totalItems="totalTicketsCount"
            @change="changePage"
          />

        </div>

      </main>
    </div>

    <!-- 🎟️ Full Rich Ticket Detail Modal -->
    <BaseModal 
      v-model="isDetailModalOpen" 
      title="Chi Tiết Vé Điện Tử VIP" 
      maxWidth="lg"
    >
      <div v-if="selectedTicket" class="space-y-5 p-1">
        
        <!-- Movie Header Banner -->
        <div class="relative rounded-2xl overflow-hidden bg-slate-900 border border-white/10 p-4 flex gap-4 items-center">
          <img 
            :src="selectedTicket.movie?.poster_url || 'https://images.unsplash.com/photo-1534447677768-be436bb09401?w=600'" 
            class="w-20 h-28 object-cover rounded-xl border border-white/10 shadow-lg shrink-0" 
          />
          <div class="space-y-1.5 flex-1 min-w-0">
            <div class="flex items-center gap-2">
              <BaseBadge 
                :variant="selectedTicket.status === 'checked_in' ? 'blue' : 'emerald'"
                size="xs"
              >
                {{ selectedTicket.status === 'checked_in' ? 'ĐÃ CHECK-IN TẠI RẠP' : 'HỢP LỆ (CHƯA SOÁT VÉ)' }}
              </BaseBadge>
              <span class="text-xs font-mono font-bold text-amber-400">{{ selectedTicket.booking_code }}</span>
            </div>
            <h3 class="text-lg font-black text-white leading-tight truncate">{{ selectedTicket.movie?.title }}</h3>
            <p class="text-xs text-cinema-muted flex items-center gap-1.5">
              <MapPin class="w-3.5 h-3.5 text-cinema-accent" />
              <span>{{ selectedTicket.showtime?.cinema?.name }}</span>
            </p>
            <p class="text-[11px] text-slate-400 truncate">{{ selectedTicket.showtime?.cinema?.address }}</p>
          </div>
        </div>

        <!-- Big QR Code Check-in Box -->
        <div class="bg-slate-900/90 border border-white/10 rounded-2xl p-5 text-center space-y-3 shadow-inner">
          <span class="text-xs font-black uppercase tracking-widest text-slate-300 block">MÃ QR QUÉT TẠI CỬA PHÒNG CHIẾU</span>
          <img 
            :src="selectedTicket.qr_code || `https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=CINERESERVE-${selectedTicket.booking_code}`" 
            class="w-40 h-40 bg-white p-2.5 rounded-2xl shadow-xl mx-auto border-2 border-white/20" 
            alt="QR Pass"
          />
          <div class="text-xl font-black font-mono text-amber-400 tracking-wider">
            {{ selectedTicket.booking_code }}
          </div>
          <p class="text-[11px] text-cinema-muted">
            Vui lòng đưa mã QR này cho nhân viên soát vé quét khi vào phòng chiếu.
          </p>
        </div>

        <!-- Showtime & Seat Breakdown -->
        <div class="bg-white/5 rounded-2xl p-4 space-y-3 text-xs border border-white/5">
          <div class="flex justify-between border-b border-white/5 pb-2">
            <span class="text-slate-400">Suất chiếu:</span>
            <span class="text-white font-bold">{{ selectedTicket.showtime?.start_time }} • {{ formatDate(selectedTicket.showtime?.show_date || selectedTicket.created_at) }}</span>
          </div>

          <div class="flex justify-between border-b border-white/5 pb-2">
            <span class="text-slate-400">Phòng chiếu:</span>
            <span class="text-white font-bold">{{ selectedTicket.showtime?.room?.name || 'Phòng 1' }} ({{ selectedTicket.showtime?.room?.room_type || '2D Standard' }})</span>
          </div>

          <div class="flex justify-between border-b border-white/5 pb-2">
            <span class="text-slate-400">Danh sách ghế:</span>
            <span class="text-emerald-400 font-bold font-mono">{{ formatSeats(selectedTicket) }}</span>
          </div>

          <div v-if="selectedTicket.combos?.length" class="flex justify-between border-b border-white/5 pb-2">
            <span class="text-slate-400">Bắp nước kèm theo:</span>
            <span class="text-amber-300 font-semibold text-right">
              <span v-for="c in selectedTicket.combos" :key="c.name" class="block">🍿 {{ c.name }} (x{{ c.quantity }})</span>
            </span>
          </div>

          <div v-if="selectedTicket.discount_amount > 0" class="flex justify-between border-b border-white/5 pb-2 text-emerald-400">
            <span>Voucher giảm giá ({{ selectedTicket.voucher_code }}):</span>
            <span class="font-bold">-{{ formatVnd(selectedTicket.discount_amount) }}</span>
          </div>

          <div class="flex justify-between pt-1 text-sm">
            <span class="font-bold text-white">Tổng thanh toán:</span>
            <span class="font-black text-amber-400 font-mono">{{ formatVnd(selectedTicket.total_amount) }}</span>
          </div>
        </div>

        <!-- Customer Details -->
        <div class="flex items-center justify-between text-xs text-cinema-muted px-2">
          <span>Người đặt: <strong class="text-slate-200">{{ selectedTicket.user_name }}</strong></span>
          <span>Email: <strong class="text-slate-200">{{ selectedTicket.user_email }}</strong></span>
        </div>

        <!-- Actions -->
        <div class="flex items-center gap-3 pt-2">
          <BaseButton 
            variant="primary" 
            size="lg" 
            block
            :loading="isDownloading"
            @click="downloadQrCode"
          >
            <template #prefix>
              <Download class="w-4 h-4" />
            </template>
            <span>Lưu Ảnh Mã QR Vé</span>
          </BaseButton>

          <BaseButton 
            variant="secondary" 
            size="lg" 
            @click="isDetailModalOpen = false"
          >
            Đóng
          </BaseButton>
        </div>

      </div>
    </BaseModal>

    <Footer />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { 
  Ticket, 
  Film,
  CheckCircle2, 
  Search,
  Eye,
  MapPin,
  Download,
  RotateCcw,
  User,
  LogIn
} from 'lucide-vue-next';
import { useAuthStore } from '../stores/authStore';
import api from '../services/api';
import Navbar from '../components/Navbar.vue';
import Footer from '../components/Footer.vue';
import BaseModal from '../components/base/BaseModal.vue';
import BaseButton from '../components/base/BaseButton.vue';
import BaseBadge from '../components/base/BaseBadge.vue';
import BaseSelect from '../components/base/BaseSelect.vue';
import BasePagination from '../components/base/BasePagination.vue';

const authStore = useAuthStore();

watch(() => authStore.user?.email, () => {
  currentPage.value = 1;
  fetchTickets();
});

const tickets = ref<any[]>([]);
const totalTicketsCount = ref(0);
const totalPages = ref(1);
const currentPage = ref(1);
const perPage = ref(6);
const activeStatus = ref('all');
const selectedMovieId = ref<string | number>('all');
const selectedCinemaId = ref<string | number>('all');
const searchQuery = ref('');
const isLoading = ref(false);

const availableMovies = ref<any[]>([]);
const availableCinemas = ref<any[]>([]);

const selectedTicket = ref<any | null>(null);
const isDetailModalOpen = ref(false);

const statusTabs = computed(() => [
  { label: 'Tất Cả Vé', value: 'all' },
  { label: '⏳ Chưa Soát Vé', value: 'confirmed' },
  { label: '✅ Đã Soát Vé', value: 'checked_in' },
]);

const selectTab = (status: string) => {
  activeStatus.value = status;
  currentPage.value = 1;
  fetchTickets();
};

const onFilterChange = () => {
  currentPage.value = 1;
  fetchTickets();
};

let searchDebounce: any = null;
const handleSearch = () => {
  clearTimeout(searchDebounce);
  searchDebounce = setTimeout(() => {
    currentPage.value = 1;
    fetchTickets();
  }, 350);
};

const resetFilters = () => {
  activeStatus.value = 'all';
  selectedMovieId.value = 'all';
  selectedCinemaId.value = 'all';
  searchQuery.value = '';
  currentPage.value = 1;
  fetchTickets();
};

const changePage = (p: number) => {
  currentPage.value = p;
  fetchTickets();
};

const formatSeats = (ticket: any) => {
  if (ticket.seats?.length) {
    return ticket.seats.map((s: any) => `${s.row}${s.number}`).join(', ');
  }
  if (ticket.booking_seats?.length) {
    return ticket.booking_seats.map((bs: any) => `${bs.seat?.row || ''}${bs.seat?.number || ''}`).filter(Boolean).join(', ');
  }
  return 'Ghế tiêu chuẩn';
};

const formatDate = (dateStr?: string) => {
  if (!dateStr) return 'Hôm nay';
  const clean = dateStr.split('T')[0].split(' ')[0];
  const parts = clean.split('-');
  if (parts.length === 3) {
    return `${parts[2]}/${parts[1]}/${parts[0]}`;
  }
  return clean;
};

const formatVnd = (val?: number) => {
  if (!val) return '95.000 đ';
  return new Intl.NumberFormat('vi-VN').format(val) + ' đ';
};

const openTicketDetail = (ticket: any) => {
  selectedTicket.value = ticket;
  isDetailModalOpen.value = true;
};

const isDownloading = ref(false);

const downloadQrCode = async () => {
  if (!selectedTicket.value) return;
  const qrUrl = selectedTicket.value.qr_code || `https://api.qrserver.com/v1/create-qr-code/?size=500x500&data=CINERESERVE-${selectedTicket.value.booking_code}`;
  
  isDownloading.value = true;
  try {
    const response = await fetch(qrUrl);
    const blob = await response.blob();
    const blobUrl = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = blobUrl;
    link.download = `CineReserve_${selectedTicket.value.booking_code}_QRCode.png`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(blobUrl);
  } catch (err) {
    window.open(qrUrl, '_blank');
  } finally {
    isDownloading.value = false;
  }
};

const loadMetadata = async () => {
  try {
    const [moviesRes, cinemasRes] = await Promise.all([
      api.get('/movies'),
      api.get('/cinemas')
    ]);
    if (moviesRes.data?.data) availableMovies.value = moviesRes.data.data;
    if (cinemasRes.data?.data) availableCinemas.value = cinemasRes.data.data;
  } catch (e) {
    console.warn('Error loading filter metadata:', e);
  }
};

const fetchTickets = async () => {
  isLoading.value = true;
  try {
    const email = authStore.user?.email || '';
    const params: any = {
      page: currentPage.value,
      per_page: perPage.value,
      status: activeStatus.value,
      movie_id: selectedMovieId.value !== 'all' ? selectedMovieId.value : undefined,
      cinema_id: selectedCinemaId.value !== 'all' ? selectedCinemaId.value : undefined,
      search: searchQuery.value.trim() || undefined,
    };
    if (email) {
      params.email = email;
    }

    const res = await api.get('/bookings', { params });
    if (res.data?.data) {
      tickets.value = res.data.data;
      if (res.data.meta) {
        totalTicketsCount.value = res.data.meta.total;
        totalPages.value = res.data.meta.last_page;
        currentPage.value = res.data.meta.current_page;
      }
    }
  } catch (e) {
    console.warn('Error fetching bookings:', e);
  } finally {
    isLoading.value = false;
  }
};

onMounted(async () => {
  await Promise.all([loadMetadata(), fetchTickets()]);
});
</script>

<style scoped>
.ticket-notch-left,
.ticket-notch-right {
  position: absolute;
  top: 50%;
  width: 20px;
  height: 20px;
  background-color: var(--color-cinema-bg, #0B0F17);
  border-radius: 50%;
  z-index: 10;
}
.ticket-notch-left {
  left: -10px;
  border-right: 1px solid var(--color-cinema-border, #1e293b);
}
.ticket-notch-right {
  right: -10px;
  border-left: 1px solid var(--color-cinema-border, #1e293b);
}
</style>
