<template>
  <div 
    class="rounded-3xl bg-cinema-card/70 border border-cinema-border hover:border-white/20 transition-all duration-300 overflow-hidden space-y-4 p-6 shadow-xl"
  >
    <!-- Cinema Header -->
    <div class="flex items-start justify-between gap-4">
      <div class="space-y-1 min-w-0">
        <div class="flex items-center gap-2">
          <h3 class="text-base font-extrabold text-white">{{ cinema.name }}</h3>
          <BaseBadge variant="cyan" size="xs">{{ cinema.city }}</BaseBadge>
        </div>
        <p class="text-xs text-cinema-muted flex items-center gap-1.5 leading-relaxed">
          <MapPin class="w-3.5 h-3.5 text-cinema-accent shrink-0" />
          <span>{{ cinema.address }}</span>
        </p>
      </div>

      <button
        @click="$emit('toggle-expand', cinema.id)"
        class="px-3.5 py-1.5 rounded-xl border text-xs font-bold transition-all cursor-pointer shrink-0 flex items-center gap-1.5"
        :class="[
          expandedId === cinema.id 
            ? 'bg-cinema-accent text-white border-cinema-accent shadow-glow-accent' 
            : 'bg-white/5 border-white/10 text-slate-300 hover:text-white hover:bg-white/10'
        ]"
      >
        <span>{{ expandedId === cinema.id ? 'Thu Gọn' : 'Xem Lịch Chiếu' }}</span>
        <ChevronDown 
          class="w-3.5 h-3.5 transition-transform"
          :class="{ 'rotate-180': expandedId === cinema.id }"
        />
      </button>
    </div>

    <!-- Expanded Showtimes Section -->
    <div 
      v-if="expandedId === cinema.id" 
      class="pt-4 border-t border-white/5 space-y-4 animate-in fade-in duration-200"
    >
      <!-- Date Selector Slider for this Cinema -->
      <DateSlider 
        :modelValue="selectedDate" 
        @update:modelValue="$emit('change-date', cinema.id, $event)" 
      />

      <!-- Loading State -->
      <div v-if="isLoadingShowtimes" class="py-8 text-center text-xs text-cinema-muted animate-pulse">
        Đang tải danh sách suất chiếu của rạp...
      </div>

      <!-- Empty State -->
      <div 
        v-else-if="!moviesInCinema || moviesInCinema.length === 0" 
        class="p-6 rounded-2xl bg-white/5 text-center text-xs text-slate-500 italic"
      >
        Chưa có suất chiếu nào được lên lịch cho ngày này tại rạp.
      </div>

      <!-- Showtimes List Grouped by Movie -->
      <CinemaShowtimeGroup 
        v-else
        :movieGroups="moviesInCinema"
        :selectedDate="selectedDate"
        @select-showtime="(movie, st) => $emit('select-showtime', movie, st)"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { MapPin, ChevronDown } from 'lucide-vue-next';
import BaseBadge from '../base/BaseBadge.vue';
import DateSlider from '../common/DateSlider.vue';
import CinemaShowtimeGroup from './CinemaShowtimeGroup.vue';

defineProps<{
  cinema: any;
  expandedId: number | null;
  selectedDate: string;
  isLoadingShowtimes: boolean;
  moviesInCinema: any[];
}>();

defineEmits<{
  (e: 'toggle-expand', cinemaId: number): void;
  (e: 'change-date', cinemaId: number, date: string): void;
  (e: 'select-showtime', movie: any, showtime: any): void;
}>();
</script>
