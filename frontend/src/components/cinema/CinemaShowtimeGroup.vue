<template>
  <div class="space-y-4">
    <!-- Movie Showtimes Accordion Group -->
    <div 
      v-for="movieGroup in movieGroups" 
      :key="movieGroup.movie.id"
      class="p-4 rounded-2xl bg-white/[0.03] border border-white/5 space-y-3"
    >
      <div class="flex items-center gap-3">
        <img 
          :src="movieGroup.movie.poster_url" 
          :alt="movieGroup.movie.title"
          class="w-10 h-14 object-cover rounded-lg border border-white/10 shrink-0" 
        />
        <div class="min-w-0 flex-1">
          <h4 class="text-xs font-bold text-white leading-snug truncate">{{ movieGroup.movie.title }}</h4>
          <span class="text-[10px] text-cinema-muted">{{ movieGroup.movie.duration || 120 }} phút • {{ movieGroup.movie.genre?.[0] || '2D' }}</span>
        </div>
      </div>

      <!-- Showtime Pills -->
      <div class="flex flex-wrap gap-2 pt-1">
        <button
          v-for="st in movieGroup.showtimes"
          :key="st.id"
          @click="handleSelect(movieGroup.movie, st)"
          :disabled="isShowtimeExpired(st, selectedDate)"
          class="px-3 py-1.5 rounded-xl border transition-all text-xs font-mono font-bold flex items-center gap-1.5 shadow-sm group cursor-pointer"
          :class="[
            isShowtimeExpired(st, selectedDate)
              ? 'opacity-35 grayscale bg-slate-950/40 border-slate-800 text-slate-500 cursor-not-allowed'
              : 'bg-slate-900/90 hover:bg-cinema-accent hover:text-white border-white/10 hover:border-cinema-accent text-slate-200'
          ]"
        >
          <span :class="{ 'line-through text-slate-600': isShowtimeExpired(st, selectedDate) }">{{ st.start_time }}</span>
          
          <ShowtimePricingBadge 
            v-if="!isShowtimeExpired(st, selectedDate)" 
            :showtime="st" 
            :targetDate="selectedDate" 
          />

          <span 
            class="text-[9px] px-1.5 py-0.5 rounded font-sans"
            :class="isShowtimeExpired(st, selectedDate) ? 'bg-slate-800/40 text-slate-600' : 'bg-white/10 group-hover:bg-white/20 text-slate-400 group-hover:text-white'"
          >
            {{ isShowtimeExpired(st, selectedDate) ? 'Hết Hạn' : (st.room?.name || '2D') }}
          </span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { isShowtimeExpired } from '../../utils/formatters';
import { useToast } from '../../composables/useToast';
import ShowtimePricingBadge from '../showtime/ShowtimePricingBadge.vue';

const props = defineProps<{
  movieGroups: any[];
  selectedDate?: string;
}>();

const emit = defineEmits<{
  (e: 'select-showtime', movie: any, showtime: any): void;
}>();

const toast = useToast();

const handleSelect = (movie: any, showtime: any) => {
  if (isShowtimeExpired(showtime, props.selectedDate)) {
    toast.warning('Suất chiếu này đã bắt đầu hoặc kết thúc. Vui lòng chọn suất chiếu kế tiếp!', 'Suất Chiếu Quá Hạn');
    return;
  }
  emit('select-showtime', movie, showtime);
};
</script>
