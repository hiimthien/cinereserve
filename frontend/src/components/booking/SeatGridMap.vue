<template>
  <div class="w-full overflow-x-auto pb-4 flex justify-center select-none">
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
            v-memo="[seat.status, seat.held_by, seat.type, seat.price]"
            :disabled="seat.status === 'booked' || (seat.status === 'holding' && seat.held_by !== sessionId)"
            @click="$emit('toggle-seat', seat)"
            class="cinema-seat-btn relative rounded-t-lg rounded-b-sm transition-all duration-200 flex items-center justify-center font-bold text-[10px] cursor-pointer"
            :class="[
              // Dimensions
              seat.type === 'couple' ? 'w-16 md:w-20 h-8 md:h-9' : 'w-7 md:w-9 h-7 md:h-8',
              
              // Status Colors with High Contrast & Vibrancy
              seat.status === 'selected' 
                ? 'bg-gradient-to-b from-emerald-400 to-emerald-600 text-white shadow-glow-green scale-110 z-10 font-black' 
                : seat.status === 'holding' && seat.held_by !== sessionId
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
            <template v-if="seat.status === 'holding' && seat.held_by !== sessionId">
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
</template>

<script setup lang="ts">
import { onMounted, nextTick } from 'vue';
import { Lock, X } from 'lucide-vue-next';
import type { Seat } from '../../types';
import { useAnime } from '../../composables/useAnime';

const props = defineProps<{
  seatRows: string[];
  seats: Seat[];
  sessionId: string;
}>();

defineEmits<{
  (e: 'toggle-seat', seat: Seat): void;
}>();

const { animateStaggerGrid } = useAnime();

const getSeatsByRow = (row: string) => {
  return props.seats
    .filter(s => s.row === row)
    .sort((a, b) => a.number - b.number);
};

const formatVnd = (val: number) => {
  if (!val) return '0 đ';
  return new Intl.NumberFormat('vi-VN').format(val) + ' đ';
};

const getSeatTooltip = (seat: Seat) => {
  if (seat.status === 'booked') return `Ghế ${seat.row}${seat.number} - Đã bán`;
  if (seat.status === 'holding') return `Ghế ${seat.row}${seat.number} - Đang giữ chỗ bởi người khác`;
  const typeText = seat.type === 'vip' ? 'VIP Prime' : seat.type === 'couple' ? 'Sweetbox Đôi' : 'Tiêu Chuẩn';
  return `Ghế ${seat.row}${seat.number} (${typeText}) - ${formatVnd(seat.price)}`;
};

onMounted(() => {
  nextTick(() => {
    animateStaggerGrid('.cinema-seat-btn');
  });
});
</script>
