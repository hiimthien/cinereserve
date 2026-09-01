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

    <!-- Main Content: Screen Arc + Seat Grid Matrix + Legend -->
    <main class="max-w-6xl w-full mx-auto px-4 md:px-8 pt-6 flex-1 flex flex-col items-center justify-center">
      
      <!-- Cinema Screen Arc Projection Light -->
      <SeatScreenArc />

      <!-- Cinematic Neon Spinner Loading Animation -->
      <div v-if="store.isLoading" class="p-16 text-center">
        <BaseSpinner size="lg" text="Đang đồng bộ sơ đồ ghế thời gian thực..." />
      </div>

      <!-- Seat Map Grid Matrix Component -->
      <SeatGridMap 
        v-else
        :seatRows="seatRows"
        :seats="store.seats"
        :sessionId="store.sessionId"
        @toggle-seat="store.toggleSeat"
      />

      <!-- Legend with 2 Clean Sections -->
      <SeatLegendPills />

    </main>

    <!-- Sticky Bottom Bar: Summary & Checkout CTA -->
    <SeatBookingActionBar 
      :selectedSeats="store.selectedSeats"
      :totalPrice="store.totalPrice"
      @checkout="handleCheckout"
    />

  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, onUnmounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { ArrowLeft, Calendar } from 'lucide-vue-next';
import { useBookingStore } from '../stores/bookingStore';
import { useSeatValidation } from '../composables/useSeatValidation';
import { useToast } from '../composables/useToast';
import SeatScreenArc from '../components/booking/SeatScreenArc.vue';
import SeatGridMap from '../components/booking/SeatGridMap.vue';
import SeatLegendPills from '../components/booking/SeatLegendPills.vue';
import SeatBookingActionBar from '../components/booking/SeatBookingActionBar.vue';
import CountdownTimer from '../components/common/CountdownTimer.vue';
import BaseSpinner from '../components/base/BaseSpinner.vue';

const route = useRoute();
const router = useRouter();
const store = useBookingStore();
const toast = useToast();
const { validateOrphanSeats } = useSeatValidation();

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
    await store.loadShowtimeById(showtimeId);
    
    // Fallback polling every 4s for real-time consistency
    pollingTimer = setInterval(() => {
      store.fetchSeats(showtimeId);
    }, 4000);
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

const handleBack = () => {
  router.push({ name: 'movie-detail', params: { slug: movieSlug } });
};

const handleCheckout = () => {
  if (store.selectedSeats.length === 0) {
    toast.warning('Vui lòng chọn ít nhất 1 ghế ngồi trên sơ đồ!', 'Chưa Chọn Ghế');
    return;
  }

  // Chặn trường hợp chừa lại 1 ghế trống đơn lẻ (Anti-Orphan Seat Rule)
  const validation = validateOrphanSeats(store.seats, store.selectedSeats, store.sessionId);
  if (!validation.isValid) {
    toast.error(
      validation.errorMessage || 'Không thể để trống 1 ghế đơn lẻ. Vui lòng chọn ghế liền kề hoặc chừa trống từ 2 ghế trở lên!',
      'Quy Tắc Chọn Ghế'
    );
    return;
  }

  router.push({ name: 'checkout' });
};
</script>
