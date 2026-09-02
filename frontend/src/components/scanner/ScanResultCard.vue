<template>
  <div v-if="result" class="space-y-4 animate-in fade-in zoom-in-95 duration-200">
    <!-- Status Banner -->
    <div 
      class="p-5 rounded-3xl border flex items-start gap-4 shadow-xl"
      :class="[
        result.status === 'VALID' 
          ? 'bg-emerald-500/10 border-emerald-500/30 text-emerald-300 shadow-emerald-500/10' 
          : result.status === 'ALREADY_USED' 
          ? 'bg-amber-500/10 border-amber-500/30 text-amber-300 shadow-amber-500/10' 
          : 'bg-rose-500/10 border-rose-500/30 text-rose-300 shadow-rose-500/10'
      ]"
    >
      <component 
        :is="result.status === 'VALID' ? CheckCircle2 : AlertTriangle" 
        class="w-8 h-8 shrink-0 mt-0.5" 
      />

      <div class="space-y-1 flex-1">
        <h3 class="text-base font-black tracking-wide">
          {{ result.status === 'VALID' ? 'VÉ HỢP LỆ • CHO PHÉP VÀO RẠP' : result.status === 'ALREADY_USED' ? 'CẢNH BÁO: VÉ ĐÃ ĐƯỢC SOÁT VÉ' : 'VÉ KHÔNG HỢP LỆ' }}
        </h3>
        <p class="text-xs leading-relaxed font-medium opacity-90">{{ result.message }}</p>
      </div>
    </div>

    <!-- Age Rating Visual Warning for Staff -->
    <div 
      v-if="result.ticket && ageInfo?.isRestricted" 
      class="p-4 rounded-2xl bg-rose-500/15 border-2 border-rose-500/50 flex items-center gap-3 animate-pulse shadow-lg shadow-rose-500/10"
    >
      <div class="w-10 h-10 rounded-xl bg-rose-600 text-white font-black text-sm flex items-center justify-center shrink-0 shadow-md">
        {{ ageInfo.shortLabel }}
      </div>
      <div class="space-y-0.5 min-w-0 flex-1">
        <p class="text-xs font-black text-rose-300 tracking-wide uppercase">Cảnh Báo Độ Tuổi Khán Giả</p>
        <p class="text-[11px] text-rose-200 font-medium leading-tight">
          {{ ageInfo.warningText }}
        </p>
      </div>
    </div>

    <!-- Ticket Data Sheet (if available) -->
    <div v-if="result.ticket" class="p-5 rounded-3xl bg-cinema-card/70 border border-cinema-border space-y-4">
      <div class="flex gap-4 items-center border-b border-white/5 pb-4">
        <img 
          :src="result.ticket.movie_poster || 'https://images.unsplash.com/photo-1534447677768-be436bb09401?w=600'" 
          class="w-16 h-24 object-cover rounded-2xl border border-white/10 shrink-0" 
        />
        <div class="space-y-1 min-w-0 flex-1">
          <div class="flex items-center gap-2">
            <span class="font-mono text-xs font-black px-2 py-0.5 rounded bg-white/10 text-white">#{{ result.ticket.booking_code }}</span>
            <BaseBadge :variant="ageInfo?.badgeVariant || 'rose'" size="xs">
              {{ ageInfo?.shortLabel || 'T18' }}
            </BaseBadge>
          </div>
          <h4 class="text-sm font-bold text-white truncate">{{ result.ticket.movie_title }}</h4>
          <p class="text-xs text-cinema-muted truncate">{{ result.ticket.cinema_name }} • {{ result.ticket.room_name }}</p>
          <p class="text-xs text-amber-300 font-bold">{{ result.ticket.start_time }} • {{ result.ticket.show_date }}</p>
        </div>
      </div>

      <div class="grid grid-cols-2 gap-3 text-xs">
        <div class="p-3 rounded-2xl bg-white/5 space-y-0.5">
          <span class="text-[10px] text-slate-500 font-bold uppercase block">Ghế Khách Đặt</span>
          <span class="font-bold text-cinema-accent">{{ formatSeats(result.ticket.seats) }}</span>
        </div>
        <div class="p-3 rounded-2xl bg-white/5 space-y-0.5 text-right">
          <span class="text-[10px] text-slate-500 font-bold uppercase block">Khách Hàng</span>
          <span class="font-bold text-white truncate block">{{ result.ticket.user_name }}</span>
        </div>
      </div>

      <button 
        @click="$emit('clear')"
        class="w-full py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white font-bold text-xs transition-colors cursor-pointer"
      >
        Tiếp tục soát vé tiếp theo
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { CheckCircle2, AlertTriangle } from 'lucide-vue-next';
import BaseBadge from '../base/BaseBadge.vue';
import { getAgeRatingInfo } from '../../utils/formatters';
import type { ScanResultData } from '../../composables/useStaffScanner';

const props = defineProps<{
  result: ScanResultData | null;
}>();

defineEmits<{
  (e: 'clear'): void;
}>();

const ageInfo = computed(() => {
  return getAgeRatingInfo(props.result?.ticket?.age_rating || 'T18');
});

const formatSeats = (seats: any) => {
  if (!seats) return '';
  if (Array.isArray(seats)) {
    return seats
      .map(s => (typeof s === 'object' ? `${s.row || ''}${s.number || ''}`.trim() : String(s)))
      .filter(Boolean)
      .join(', ');
  }
  return String(seats);
};
</script>
