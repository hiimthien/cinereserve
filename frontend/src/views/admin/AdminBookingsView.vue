<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-black text-white flex items-center gap-2.5">
          <Ticket class="w-6 h-6 text-cinema-accent" />
          <span>Quản Lý Toàn Bộ Đơn Đặt Vé</span>
        </h1>
        <p class="text-xs text-cinema-muted mt-1">Tra cứu mọi giao dịch đặt vé của khách hàng trên toàn bộ cụm rạp cả nước</p>
      </div>

      <div class="flex items-center gap-2">
        <BaseButton 
          variant="secondary" 
          size="md" 
          @click="fetchBookings"
        >
          <template #prefix><RotateCcw class="w-4 h-4" /></template>
          <span>Làm Mới</span>
        </BaseButton>
      </div>
    </div>

    <!-- Filters & Search Toolbar -->
    <div class="bg-cinema-surface/70 border border-cinema-border rounded-3xl p-4 backdrop-blur-xl shadow-xl space-y-4">
      
      <!-- Top Row: Status Tabs -->
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
                : 'bg-white/5 text-slate-400 hover:text-white'
            ]"
          >
            {{ tab.label }}
          </button>
        </div>

        <button 
          v-if="selectedCinemaId !== 'all' || searchQuery || activeStatus !== 'all'"
          @click="resetFilters"
          class="text-xs text-rose-400 hover:text-rose-300 font-bold flex items-center gap-1.5 transition-colors cursor-pointer"
        >
          <RotateCcw class="w-3.5 h-3.5" />
          <span>Xóa Bộ Lọc</span>
        </button>
      </div>

      <!-- Bottom Row: Cinema Dropdown & Search Box -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <!-- Cinema Filter -->
        <BaseSelect 
          v-model="selectedCinemaId"
          label="🏢 Lọc theo Cụm Rạp"
          @update:model-value="onFilterChange"
        >
          <option value="all">Tất cả cụm rạp</option>
          <option v-for="c in cinemas" :key="c.id" :value="c.id">
            {{ c.name }} ({{ c.city }})
          </option>
        </BaseSelect>

        <!-- Search Input -->
        <div class="space-y-1 sm:col-span-2">
          <label class="block text-[11px] font-bold text-slate-400">🔍 Tìm kiếm mã vé / Tên khách / Số điện thoại / Email</label>
          <div class="relative">
            <Search class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
            <input 
              v-model="searchQuery"
              @input="handleSearch"
              type="text"
              placeholder="Nhập mã vé (CR-...), email, tên người đặt, tên phim..."
              class="w-full bg-slate-900/90 border border-cinema-border rounded-xl pl-9 pr-4 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-cinema-accent transition-colors"
            />
          </div>
        </div>
      </div>

    </div>

    <!-- Bookings Table -->
    <div class="bg-cinema-surface/80 border border-cinema-border rounded-3xl overflow-hidden shadow-xl backdrop-blur-md p-6 space-y-4">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs text-slate-300">
          <thead class="bg-slate-900/90 text-[11px] uppercase tracking-wider text-slate-400 border-b border-white/5">
            <tr>
              <th class="p-3">Mã Vé</th>
              <th class="p-3">Khách Hàng</th>
              <th class="p-3">Phim & Rạp</th>
              <th class="p-3">Suất Chiếu & Ghế</th>
              <th class="p-3">Tổng Tiền</th>
              <th class="p-3">Trạng Thái</th>
              <th class="p-3 text-right">Thao Tác</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-white/5">
            <tr v-if="isLoading" v-for="i in 5" :key="i" class="animate-pulse">
              <td colspan="7" class="p-4"><div class="h-8 bg-white/5 rounded-xl w-full"></div></td>
            </tr>

            <tr v-else-if="bookings.length === 0">
              <td colspan="7" class="p-12 text-center text-slate-500 space-y-2">
                <Ticket class="w-8 h-8 mx-auto text-slate-600" />
                <p>Không tìm thấy đơn đặt vé nào phù hợp</p>
              </td>
            </tr>

            <tr 
              v-else 
              v-for="b in bookings" 
              :key="b.id"
              class="hover:bg-white/5 transition-colors group"
            >
              <td class="p-3 font-mono font-bold text-amber-400">
                {{ b.booking_code }}
              </td>
              <td class="p-3">
                <div class="font-bold text-white">{{ b.user_name }}</div>
                <div class="text-[11px] text-cinema-muted">{{ b.user_phone }}</div>
                <div class="text-[10px] text-slate-500 truncate max-w-[140px]">{{ b.user_email }}</div>
              </td>
              <td class="p-3">
                <div class="font-bold text-white line-clamp-1">{{ b.movie?.title || b.showtime?.movie?.title }}</div>
                <div class="text-[11px] text-cinema-muted line-clamp-1">{{ b.showtime?.cinema?.name }}</div>
              </td>
              <td class="p-3">
                <div class="font-semibold text-white">{{ b.showtime?.start_time }} • {{ formatDate(b.showtime?.show_date || b.created_at) }}</div>
                <div class="font-mono text-emerald-400 mt-0.5">{{ formatSeats(b) }}</div>
              </td>
              <td class="p-3 font-black text-amber-400 font-mono">
                {{ formatVnd(b.total_amount) }}
              </td>
              <td class="p-3">
                <BaseBadge :variant="getBadgeVariant(b.status)" size="xs">
                  {{ formatStatus(b.status) }}
                </BaseBadge>
              </td>
              <td class="p-3 text-right space-x-1.5">
                <button 
                  v-if="b.status === 'confirmed'"
                  @click="handleCheckIn(b.id)"
                  class="px-2.5 py-1.5 rounded-xl bg-emerald-500/15 hover:bg-emerald-500/30 text-emerald-300 font-bold text-[11px] transition-colors cursor-pointer"
                  title="Soát vé nhanh"
                >
                  Soát Vé
                </button>
                <button 
                  v-if="b.status !== 'cancelled'"
                  @click="handleCancel(b.id)"
                  class="px-2.5 py-1.5 rounded-xl bg-rose-500/10 hover:bg-rose-500/25 text-rose-400 font-bold text-[11px] transition-colors cursor-pointer"
                  title="Hủy đơn vé"
                >
                  Hủy Vé
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Reusable Base Pagination -->
      <BasePagination 
        v-model:currentPage="currentPage"
        :totalPages="totalPages"
        :totalItems="totalBookings"
        @change="changePage"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Ticket, Search, RotateCcw } from 'lucide-vue-next';
import api from '../../services/api';
import BaseSelect from '../../components/base/BaseSelect.vue';
import BaseBadge from '../../components/base/BaseBadge.vue';
import BaseButton from '../../components/base/BaseButton.vue';
import BasePagination from '../../components/base/BasePagination.vue';

const bookings = ref<any[]>([]);
const cinemas = ref<any[]>([]);
const totalBookings = ref(0);
const totalPages = ref(1);
const currentPage = ref(1);
const perPage = ref(10);
const activeStatus = ref('all');
const selectedCinemaId = ref<string | number>('all');
const searchQuery = ref('');
const isLoading = ref(false);

const statusTabs = [
  { label: 'Tất Cả Đơn', value: 'all' },
  { label: '⏳ Chưa Soát Vé', value: 'confirmed' },
  { label: '✅ Đã Check-in', value: 'checked_in' },
  { label: '❌ Đã Hủy', value: 'cancelled' },
];

const selectTab = (status: string) => {
  activeStatus.value = status;
  currentPage.value = 1;
  fetchBookings();
};

const onFilterChange = () => {
  currentPage.value = 1;
  fetchBookings();
};

let searchDebounce: any = null;
const handleSearch = () => {
  clearTimeout(searchDebounce);
  searchDebounce = setTimeout(() => {
    currentPage.value = 1;
    fetchBookings();
  }, 350);
};

const resetFilters = () => {
  activeStatus.value = 'all';
  selectedCinemaId.value = 'all';
  searchQuery.value = '';
  currentPage.value = 1;
  fetchBookings();
};

const changePage = (p: number) => {
  currentPage.value = p;
  fetchBookings();
};

const formatVnd = (val: number) => {
  if (!val) return '0 đ';
  return new Intl.NumberFormat('vi-VN').format(val) + ' đ';
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

const formatSeats = (booking: any) => {
  if (booking.seats?.length) {
    return booking.seats.map((s: any) => `${s.row}${s.number}`).join(', ');
  }
  return 'Ghế chuẩn';
};

const formatStatus = (status: string) => {
  switch (status) {
    case 'checked_in': return 'ĐÃ CHECK-IN';
    case 'cancelled': return 'ĐÃ HỦY';
    default: return 'CHƯA SOÁT VÉ';
  }
};

const getBadgeVariant = (status: string): 'blue' | 'rose' | 'emerald' => {
  switch (status) {
    case 'checked_in': return 'blue';
    case 'cancelled': return 'rose';
    default: return 'emerald';
  }
};

const fetchCinemas = async () => {
  try {
    const res = await api.get('/cinemas');
    if (res.data?.data) cinemas.value = res.data.data;
  } catch (e) {}
};

const fetchBookings = async () => {
  isLoading.value = true;
  try {
    const params: any = {
      page: currentPage.value,
      per_page: perPage.value,
      status: activeStatus.value,
      cinema_id: selectedCinemaId.value !== 'all' ? selectedCinemaId.value : undefined,
      search: searchQuery.value.trim() || undefined,
    };

    const res = await api.get('/admin/bookings', { params });
    if (res.data?.data) {
      bookings.value = res.data.data;
      if (res.data.meta) {
        totalBookings.value = res.data.meta.total;
        totalPages.value = res.data.meta.last_page;
        currentPage.value = res.data.meta.current_page;
      }
    }
  } catch (e) {
    console.warn('Error fetching admin bookings:', e);
  } finally {
    isLoading.value = false;
  }
};

const handleCheckIn = async (id: number) => {
  try {
    await api.post(`/admin/bookings/${id}/check-in`);
    await fetchBookings();
  } catch (e: any) {
    alert(e.response?.data?.message || 'Không thể check-in vé.');
  }
};

const handleCancel = async (id: number) => {
  if (!confirm('Bạn có chắc chắn muốn hủy đơn vé này không?')) return;
  try {
    await api.delete(`/admin/bookings/${id}/cancel`);
    await fetchBookings();
  } catch (e: any) {
    alert(e.response?.data?.message || 'Không thể hủy vé.');
  }
};

onMounted(() => {
  fetchCinemas();
  fetchBookings();
});
</script>
