<template>
  <div 
    @click="$emit('select', movie)"
    class="group relative bg-cinema-surface/60 rounded-3xl overflow-hidden border border-cinema-border hover:border-cinema-accent/50 transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl flex flex-col cursor-pointer"
  >
    <!-- Poster with Ratio 2:3 -->
    <div class="relative aspect-[2/3] w-full overflow-hidden bg-slate-900">
      <img 
        :src="movie.poster_url || FALLBACK_POSTER" 
        :alt="movie.title"
        loading="lazy"
        @error="handleImageError"
        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" 
      />

      <!-- Gradient overlay -->
      <div class="absolute inset-0 bg-gradient-to-t from-cinema-card via-transparent to-black/30 opacity-70 group-hover:opacity-40 transition-opacity"></div>

      <!-- Top Badges: Age Rating (Left) & Star Rating (Right) -->
      <div class="absolute top-3 inset-x-3 flex items-center justify-between pointer-events-none gap-2">
        <!-- Age Rating Badge (T18, T16, T13, P, K) -->
        <span 
          class="px-2 py-0.5 rounded-lg text-[11px] font-black tracking-wider shadow-lg uppercase whitespace-nowrap"
          :class="[
            ageInfo.code === 'T18' ? 'bg-rose-600 text-white shadow-rose-600/30' :
            ageInfo.code === 'T16' ? 'bg-amber-600 text-white shadow-amber-600/30' :
            ageInfo.code === 'T13' ? 'bg-cyan-600 text-white shadow-cyan-600/30' :
            ageInfo.code === 'K' ? 'bg-amber-400 text-slate-950 font-black' :
            'bg-emerald-600 text-white shadow-emerald-600/30'
          ]"
          :title="ageInfo.label"
        >
          {{ ageInfo.shortLabel }}
        </span>

        <!-- Rating TMDb -->
        <span 
          v-if="movie.rating"
          class="px-2 py-0.5 rounded-full bg-black/70 backdrop-blur-md text-amber-400 text-[11px] font-black border border-amber-500/30 flex items-center gap-1 shadow-md whitespace-nowrap"
        >
          <Star class="w-3 h-3 fill-amber-400 text-amber-400" />
          <span>{{ Number(movie.rating).toFixed(1) }}</span>
        </span>
      </div>

      <!-- Play Trailer Floating Button on Hover -->
      <div 
        v-if="movie.trailer_url"
        class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300"
      >
        <button 
          @click.stop="$emit('trailer', movie)"
          class="w-12 h-12 rounded-full bg-cinema-accent/90 hover:bg-cinema-accent text-white flex items-center justify-center shadow-glow-accent hover:scale-110 transition-all cursor-pointer"
          aria-label="Xem trailer"
        >
          <Play class="w-5 h-5 fill-current text-white ml-0.5" />
        </button>
      </div>

      <!-- Bottom Badges: Genre Tags (Left) & Status (Right) -->
      <div class="absolute bottom-3 inset-x-3 flex items-center justify-between gap-1.5 pointer-events-none">
        <div class="flex gap-1 flex-wrap min-w-0">
          <span 
            v-for="g in movie.genre?.slice(0, 2)" 
            :key="g"
            class="px-2 py-0.5 rounded-md bg-black/80 backdrop-blur-md text-slate-200 text-[10px] font-semibold border border-white/10 truncate max-w-[90px]"
          >
            {{ g }}
          </span>
        </div>

        <!-- Showing Status Pill -->
        <span 
          v-if="movie.status === 'now_showing'"
          class="px-2 py-0.5 rounded-md bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-[10px] font-bold backdrop-blur-md flex items-center gap-1 whitespace-nowrap shrink-0"
        >
          <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
          <span>Đang chiếu</span>
        </span>
        <span 
          v-else
          class="px-2 py-0.5 rounded-md bg-amber-500/20 text-amber-300 border border-amber-500/30 text-[10px] font-bold backdrop-blur-md flex items-center gap-1 whitespace-nowrap shrink-0"
        >
          <Sparkles class="w-2.5 h-2.5 text-amber-300" />
          <span>Sắp chiếu</span>
        </span>
      </div>
    </div>

    <!-- Movie Card Body Details -->
    <div class="p-4 space-y-2 flex-1 flex flex-col justify-between">
      <div>
        <h3 class="font-extrabold text-white text-base leading-snug group-hover:text-cinema-accent transition-colors line-clamp-1">
          {{ movie.title }}
        </h3>
        <p v-if="movie.original_title && movie.original_title !== movie.title" class="text-[11px] text-cinema-muted line-clamp-1 italic">
          {{ movie.original_title }}
        </p>
      </div>

      <div class="flex items-center justify-between text-xs text-cinema-muted pt-2 border-t border-white/5 gap-2">
        <div v-if="movie.status === 'coming_soon'" class="text-amber-300/90 font-medium text-[11px] flex items-center gap-1.5 min-w-0">
          <Calendar class="w-3.5 h-3.5 text-amber-400 shrink-0" />
          <span class="truncate whitespace-nowrap">{{ formatReleaseDate(movie.release_date) }}</span>
        </div>
        <div v-else class="flex items-center gap-1.5 text-slate-400 min-w-0">
          <Clock class="w-3.5 h-3.5 text-slate-500 shrink-0" />
          <span class="truncate">{{ movie.duration || 120 }} phút</span>
        </div>

        <span 
          class="font-bold text-[11px] flex items-center gap-1 group-hover:translate-x-0.5 transition-transform shrink-0"
          :class="movie.status === 'coming_soon' ? 'text-cinema-gold' : 'text-cinema-accent'"
        >
          <span>{{ movie.status === 'coming_soon' ? 'Chi tiết' : 'Đặt vé' }}</span>
          <ChevronRight class="w-3.5 h-3.5" />
        </span>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Star, Play, Sparkles, Calendar, Clock, ChevronRight } from 'lucide-vue-next';
import { getAgeRatingInfo } from '../../utils/formatters';
import type { Movie } from '../../types';

const props = defineProps<{
  movie: Movie;
}>();

defineEmits<{
  (e: 'select', movie: Movie): void;
  (e: 'trailer', movie: Movie): void;
}>();

const ageInfo = computed(() => {
  return getAgeRatingInfo(props.movie.age_rating || 'T18');
});

const FALLBACK_POSTER = 'https://images.unsplash.com/photo-1534447677768-be436bb09401?w=600&auto=format&fit=crop&q=80';

const handleImageError = (event: Event) => {
  const target = event.target as HTMLImageElement;
  if (target && target.src !== FALLBACK_POSTER) {
    target.src = FALLBACK_POSTER;
  }
};

const formatReleaseDate = (dateStr?: string) => {
  if (!dateStr) return 'Sắp chiếu';
  const clean = dateStr.split('T')[0].split(' ')[0];
  const parts = clean.split('-');
  if (parts.length === 3) {
    return `${parts[2]}/${parts[1]}/${parts[0]}`;
  }
  return clean;
};
</script>
