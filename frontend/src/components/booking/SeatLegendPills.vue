<template>
  <div class="space-y-3 max-w-2xl w-full mx-auto my-6 px-4 select-none">
    <!-- Seat Types & Pricing Legend (Dynamic Pricing) -->
    <div class="flex flex-wrap items-center justify-center gap-4 sm:gap-6 py-2.5 px-4 bg-cinema-surface/70 backdrop-blur-md rounded-2xl border border-cinema-border text-xs">
      <!-- Standard -->
      <div class="flex items-center gap-2">
        <div class="w-5 h-5 rounded-t-md rounded-b-sm bg-slate-800 border border-slate-600/80"></div>
        <span class="text-slate-300 font-medium">
          Ghế Thường 
          <span class="text-[11px] text-cinema-muted">({{ formatVndPrice(pricing.priceStandard) }})</span>
        </span>
      </div>

      <!-- VIP -->
      <div class="flex items-center gap-2">
        <div class="w-5 h-5 rounded-t-md rounded-b-sm bg-gradient-to-b from-amber-500/30 to-amber-950/50 border-2 border-amber-400 shadow-glow-gold"></div>
        <span class="text-amber-300 font-bold">
          Ghế VIP Prime 
          <span class="text-[11px] text-amber-400/80">({{ formatVndPrice(pricing.priceVip) }})</span>
        </span>
      </div>

      <!-- Couple / Sweetbox -->
      <div class="flex items-center gap-2">
        <div class="w-8 h-5 rounded-t-md rounded-b-sm bg-gradient-to-r from-pink-900/50 via-rose-900/40 to-pink-900/50 border-2 border-pink-500/80"></div>
        <span class="text-pink-300 font-bold">
          Ghế Đôi Sweetbox 
          <span class="text-[11px] text-pink-400/80">({{ formatVndPrice(pricing.priceCouple) }})</span>
        </span>
      </div>
    </div>

    <!-- Seat Status Legend -->
    <div class="flex flex-wrap items-center justify-center gap-4 sm:gap-6 py-2 px-4 bg-black/30 backdrop-blur-sm rounded-xl border border-white/5 text-[11px] text-cinema-muted">
      <!-- Selected -->
      <div class="flex items-center gap-1.5">
        <div class="w-3.5 h-3.5 rounded bg-emerald-500 shadow-glow-green"></div>
        <span class="text-emerald-400 font-bold">Đang chọn</span>
      </div>

      <!-- Holding -->
      <div class="flex items-center gap-1.5">
        <div class="w-3.5 h-3.5 rounded bg-amber-400 flex items-center justify-center animate-holding">
          <Lock class="w-2.5 h-2.5 text-slate-950" />
        </div>
        <span class="text-amber-300 font-bold">Đang giữ chỗ (10 phút)</span>
      </div>

      <!-- Booked -->
      <div class="flex items-center gap-1.5">
        <div class="w-3.5 h-3.5 rounded bg-red-950/60 border border-red-900/60 flex items-center justify-center">
          <X class="w-2.5 h-2.5 text-red-500/70" />
        </div>
        <span class="text-red-400/70">Đã bán</span>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Lock, X } from 'lucide-vue-next';
import { useBookingStore } from '../../stores/bookingStore';
import { useDynamicPricing } from '../../composables/useDynamicPricing';
import { formatVndPrice } from '../../utils/formatters';

const store = useBookingStore();
const { getDynamicPricing } = useDynamicPricing();

const pricing = computed(() => {
  return getDynamicPricing(store.selectedShowtime, store.selectedDate);
});
</script>
