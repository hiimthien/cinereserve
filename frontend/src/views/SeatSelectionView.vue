<template>
  <div class="min-h-screen bg-cinema-bg flex flex-col justify-between pb-28 select-none">
    
    <!-- Top Bar: Movie Title, Room, Showtime & Back Button -->
    <header class="border-b border-cinema-border bg-cinema-surface/80 backdrop-blur-md sticky top-0 z-30 px-4 md:px-6 py-4">
      <div class="max-w-7xl mx-auto flex items-center justify-between gap-4">
        <button 
          @click="handleBack"
          class="flex items-center gap-2 text-sm text-cinema-muted hover:text-white transition-colors cursor-pointer"
        >
          <ArrowLeft class="w-4 h-4" />
          <span class="hidden sm:inline">Quay lại</span>
        </button>

        <div class="text-center">
          <h1 class="text-base md:text-lg font-extrabold text-white leading-tight">
            {{ store.currentMovie?.title || 'Đang tải...' }}
          </h1>
          <p class="text-xs text-cinema-muted flex items-center justify-center gap-1.5 mt-0.5">
            <span>{{ store.selectedShowtime?.cinema?.name || 'CGV Cinema' }}</span>
            <span>•</span>
            <span>{{ store.selectedShowtime?.room?.name || 'Phòng 1' }}</span>
            <span>•</span>
            <span class="text-white font-bold">{{ store.selectedShowtime?.start_time || '09:00' }}</span>
            <span>•</span>
            <span class="text-amber-400 font-semibold flex items-center gap-1">
              <Calendar class="w-3 h-3" />
              {{ formattedDate }}
            </span>
          </p>
        </div>

        <!-- Live Countdown Timer Component -->
        <CountdownTimer />
      </div>
    </header>

    <!-- Main Content: Screen Curve + Seat Grid Matrix -->
    <main class="max-w-6xl w-full mx-auto px-4 md:px-8 pt-6 flex-1 flex flex-col items-center justify-center">
      
      <!-- Cinema Screen Projection Light -->
      <ScreenLight />

      <!-- Cinematic Neon Spinner Loading Animation -->
      <div v-if="store.isLoading" class="p-16 text-center">
        <BaseSpinner size="lg" text="Đang đồng bộ sơ đồ ghế thời gian thực..." />
      </div>

      <!-- Seat Map Grid -->
      <div v-else class="w-full overflow-x-auto pb-4 flex justify-center">
        <div class="min-w-[640px] max-w-4xl p-6 bg-cinema-surface/40 rounded-3xl border border-cinema-border backdrop-blur-md space-y-3">
          
          <div 
            v-for="row in seatRows" 
            :key="row" 
            class="flex items-center justify-center gap-2 md:gap-3"
          >
            <!-- Row Label Left -->
            <span class="w-5 text-center text-xs font-bold text-cinema-muted">{{ row }}</span>

            <!-- Seats in Row -->
            <div class="flex items-center gap-1.5 md:gap-2">
              <button
                v-for="seat in getSeatsByRow(row)"
                :key="seat.id"
                :disabled="seat.status === 'booked' || (seat.status === 'holding' && seat.held_by !== store.sessionId)"
                @click="store.toggleSeat(seat)"
                class="relative rounded-t-lg rounded-b-sm transition-all duration-200 flex items-center justify-center font-bold text-[10px] cursor-pointer"
                :class="[
                  // Dimensions
                  seat.type === 'couple' ? 'w-16 md:w-20 h-8 md:h-9' : 'w-7 md:w-9 h-7 md:h-8',
                  
                  // Status Colors with High Contrast & Vibrancy
                  seat.status === 'selected' 
                    ? 'bg-gradient-to-b from-emerald-400 to-emerald-600 text-white shadow-glow-green scale-110 z-10 font-black' 
                    : seat.status === 'holding' && seat.held_by !== store.sessionId
                    ? 'bg-amber-400 text-slate-950 cursor-not-allowed shadow-glow-gold animate-holding font-black'
                    : seat.status === 'booked'
                    ? 'bg-red-950/40 border border-red-900/30 text-red-700/50 cursor-not-allowed opacity-40'
                    : seat.type === 'vip'
                    ? 'bg-gradient-to-b from-amber-500/25 to-amber-950/40 border-2 border-amber-400 text-amber-300 font-extrabold shadow-[0_0_10px_rgba(245,158,11,0.2)] hover:border-amber-300 hover:scale-105'
                    : seat.type === 'couple'
                    ? 'bg-gradient-to-r from-pink-900/50 via-rose-900/40 to-pink-900/50 border-2 border-pink-500/80 text-pink-300 font-bold hover:scale-105'
                    : 'bg-slate-800/90 border border-slate-700/80 hover:bg-slate-700 text-slate-300 hover:border-slate-500 hover:scale-105'
                ]"
                :title="getSeatTooltip(seat)"
              >
                <!-- Seat Number text or Lock/X Icon -->
                <template v-if="seat.status === 'holding' && seat.held_by !== store.sessionId">
                  <Lock class="w-3.5 h-3.5 text-slate-950" />
                </template>
                <template v-else-if="seat.status === 'booked'">
                  <X class="w-3.5 h-3.5 text-red-500/60" />
                </template>
                <template v-else-if="seat.type === 'couple'">
                  <span class="text-[10px] tracking-wide">{{ seat.row }}{{ seat.number }} (Đôi)</span>
                </template>
                <template v-else>
                  <span>{{ seat.number }}</span>
                </template>
              </button>
            </div>

            <!-- Row Label Right -->
            <span class="w-5 text-center text-xs font-bold text-cinema-muted">{{ row }}</span>
          </div>

        </div>
      </div>

      <!-- Legend with 2 Clean Sections -->
      <SeatLegend />

    </main>

    <!-- Sticky Bottom Bar: Summary & Checkout CTA -->
    <footer class="fixed bottom-0 inset-x-0 bg-cinema-surface/90 border-t border-cinema-border backdrop-blur-xl z-40 px-6 py-4 shadow-2xl">
      <div class="max-w-6xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4">
        
        <!-- Left: Selected Seats Summary -->
        <div class="flex items-center gap-4">
          <div class="text-left">
            <span class="text-xs text-cinema-muted block">Ghế đã chọn:</span>
            <div class="flex items-center gap-1.5 mt-0.5 min-h-[24px]">
              <template v-if="store.selectedSeats.length > 0">
                <BaseBadge 
                  v-for="s in store.selectedSeats" 
                  :key="s.id"
                  :variant="s.type === 'vip' ? 'gold' : s.type === 'couple' ? 'accent' : 'emerald'"
                  size="sm"
                  rounded="md"
                >
                  {{ s.row }}{{ s.number }} ({{ s.type === 'vip' ? 'VIP' : s.type === 'couple' ? 'ĐÔI' : 'THƯỜNG' }})
                </BaseBadge>
              </template>
              <span v-else class="text-xs text-slate-500 italic">Chưa chọn ghế nào</span>
            </div>
          </div>

          <div class="h-8 w-px bg-white/10 hidden sm:block"></div>

          <!-- Total Price -->
          <div>
            <span class="text-xs text-cinema-muted block">Tổng tạm tính:</span>
            <span class="text-2xl font-black text-amber-400">
              {{ formatVnd(store.totalPrice) }}
            </span>
          </div>
        </div>

        <!-- Right: Action Button using BaseButton -->
        <BaseButton 
          :disabled="store.selectedSeats.length === 0"
          variant="primary"
          size="lg"
          @click="handleCheckout"
        >
          <template #suffix>
            <ChevronRight class="w-4 h-4" />
          </template>
          Tiến hành thanh toán ({{ store.selectedSeats.length }} ghế)
        </BaseButton>

      </div>
    </footer>

  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, onUnmounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { ArrowLeft, Lock, X, ChevronRight, Calendar } from 'lucide-vue-next';
import { useBookingStore } from '../stores/bookingStore';
import ScreenLight from '../components/ScreenLight.vue';
import SeatLegend from '../components/SeatLegend.vue';
import CountdownTimer from '../components/CountdownTimer.vue';
import BaseButton from '../components/base/BaseButton.vue';
import BaseBadge from '../components/base/BaseBadge.vue';
import BaseSpinner from '../components/base/BaseSpinner.vue';
import type { Seat } from '../types';

const route = useRoute();
const router = useRouter();
const store = useBookingStore();

const showtimeId = Number(route.params.showtimeId);
const movieSlug = String(route.params.slug);

// Polling interval reference for real-time fallback sync
let pollingTimer: any = null;

const formattedDate = computed(() => {
  const raw = store.selectedDate || '01/09/2026';
  const clean = raw.split('T')[0].split(' ')[0];
  const parts = clean.split('-');
  if (parts.length === 3) {
    return `${parts[2]}/${parts[1]}/${parts[0]}`;
  }
  return clean;
});

onMounted(async () => {
  if (showtimeId) {
    await store.fetchSeats(showtimeId);
    
    // Fallback polling every 5s if websocket disconnects
    pollingTimer = setInterval(() => {
      store.fetchSeats(showtimeId);
    }, 5000);
  }
});

onUnmounted(() => {
  if (pollingTimer) {
    clearInterval(pollingTimer);
    pollingTimer = null;
  }
});

const seatRows = computed(() => {
  const rows = new Set<string>();
  store.seats.forEach(s => rows.add(s.row));
  return Array.from(rows).sort();
});

const getSeatsByRow = (row: string) => {
  return store.seats
    .filter(s => s.row === row)
    .sort((a, b) => a.number - b.number);
};

const getSeatTooltip = (seat: Seat) => {
  if (seat.status === 'booked') return `Ghế ${seat.row}${seat.number} - Đã bán`;
  if (seat.status === 'holding') return `Ghế ${seat.row}${seat.number} - Đang giữ chỗ bởi người khác`;
  const typeText = seat.type === 'vip' ? 'VIP Prime' : seat.type === 'couple' ? 'Sweetbox Đôi' : 'Tiêu Chuẩn';
  return `Ghế ${seat.row}${seat.number} (${typeText}) - ${formatVnd(seat.price)}`;
};

const formatVnd = (val: number) => {
  if (!val) return '0 đ';
  return new Intl.NumberFormat('vi-VN').format(val) + ' đ';
};

const handleBack = () => {
  router.push({ name: 'movie-detail', params: { slug: movieSlug } });
};

const handleCheckout = () => {
  if (store.selectedSeats.length > 0) {
    router.push({ name: 'checkout' });
  }
};
</script>
