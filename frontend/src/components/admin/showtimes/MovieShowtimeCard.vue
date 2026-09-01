<template>
  <div 
    class="bg-cinema-surface/80 border border-cinema-border rounded-3xl overflow-hidden shadow-xl backdrop-blur-md flex flex-col justify-between hover:border-cinema-accent/40 transition-all group"
  >
    <!-- Top Poster & Banner Info -->
    <div class="p-5 flex gap-4 items-start border-b border-white/5 bg-gradient-to-b from-white/5 to-transparent">
      <img 
        :src="movie.poster_url || 'https://images.unsplash.com/photo-1534447677768-be436bb09401?w=400'" 
        class="w-20 h-28 object-cover rounded-2xl border border-white/10 shadow-lg shrink-0 group-hover:scale-105 transition-transform"
      />

      <div class="space-y-1.5 flex-1 min-w-0">
        <div class="flex items-center gap-1.5">
          <BaseBadge :variant="getBadgeVariant(movie.status)" size="xs">
            {{ formatStatus(movie.status) }}
          </BaseBadge>
          <span class="text-[11px] text-amber-400 font-mono font-bold">★ {{ movie.rating || 8.5 }}</span>
        </div>

        <h3 class="font-extrabold text-white text-base leading-snug line-clamp-1 group-hover:text-amber-400 transition-colors">
          {{ movie.title }}
        </h3>

        <p class="text-xs text-cinema-muted line-clamp-1">
          {{ movie.duration || 120 }} phút • Khởi chiếu: {{ formatDate(movie.release_date) }}
        </p>

        <div class="pt-1 flex items-center gap-2 text-[11px] text-slate-400">
          <span class="px-2 py-0.5 rounded-md bg-white/5 border border-white/5 font-semibold">
            {{ showtimesCount }} suất chiếu
          </span>
        </div>
      </div>
    </div>

    <!-- Quick Summary of Next Showtimes -->
    <div class="p-5 space-y-3 flex-1 flex flex-col justify-between">
      <div class="space-y-2">
        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Các suất chiếu gần nhất:</span>
        
        <div v-if="sampleShowtimes.length > 0" class="flex flex-wrap gap-1.5">
          <span 
            v-for="st in sampleShowtimes" 
            :key="st.id"
            class="px-2.5 py-1 rounded-xl text-xs font-mono font-bold border transition-all"
            :class="st.status === 'early_premiere' ? 'bg-purple-500/20 text-purple-300 border-purple-500/40' : 'bg-slate-900/90 text-slate-300 border-cinema-border'"
          >
            {{ st.start_time }} ({{ formatDateShort(st.show_date) }})
          </span>
        </div>

        <div v-else class="text-xs text-slate-500 italic py-1">
          {{ movie.status === 'coming_soon' ? 'Phim sắp chiếu. Bấm "Thêm Suất" để tạo lịch tự động từ ngày khởi chiếu!' : 'Chưa có lịch chiếu nào được tạo cho phim này.' }}
        </div>
      </div>

      <!-- Bottom Action Buttons -->
      <div class="pt-3 border-t border-white/5 flex items-center justify-between gap-2">
        <BaseButton 
          variant="secondary" 
          size="sm"
          @click="$emit('view-detail', movie)"
        >
          <template #prefix><Eye class="w-3.5 h-3.5" /></template>
          <span>Xem Lịch Chiếu</span>
        </BaseButton>

        <BaseButton 
          variant="primary" 
          size="sm"
          @click="$emit('add-showtime', movie)"
        >
          <template #prefix><Plus class="w-3.5 h-3.5" /></template>
          <span>Thêm Suất</span>
        </BaseButton>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { Eye, Plus } from 'lucide-vue-next';
import BaseBadge from '../../base/BaseBadge.vue';
import BaseButton from '../../base/BaseButton.vue';
import { formatDate, formatDateShort, formatStatus, getBadgeVariant } from '../../../utils/formatters';

defineProps<{
  movie: any;
  showtimesCount: number;
  sampleShowtimes: any[];
}>();

defineEmits<{
  (e: 'view-detail', movie: any): void;
  (e: 'add-showtime', movie: any): void;
}>();
</script>
