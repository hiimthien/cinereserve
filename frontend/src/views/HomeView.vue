<template>
  <div class="min-h-screen bg-cinema-bg pb-24">
    <!-- Global Header -->
    <Navbar />

    <!-- 1. Hero Featured Movie Carousel -->
    <section class="relative w-full h-[520px] md:h-[620px] overflow-hidden">
      <transition name="fade" mode="out-in">
        <div :key="activeHeroIndex" class="relative w-full h-full">
          <!-- Backdrop image -->
          <img 
            :src="featuredMovie?.backdrop_url" 
            :alt="featuredMovie?.title"
            class="w-full h-full object-cover filter brightness-[0.55] transition-all duration-1000 scale-105"
          />
          <div class="absolute inset-0 bg-gradient-to-t from-cinema-bg via-cinema-bg/40 to-transparent"></div>
          <div class="absolute inset-0 bg-gradient-to-r from-cinema-bg via-cinema-bg/60 to-transparent"></div>

          <!-- Hero Details -->
          <div class="absolute inset-0 max-w-7xl mx-auto px-6 md:px-12 flex flex-col justify-center space-y-4">
            
            <div class="flex items-center gap-3">
              <span class="px-3 py-1 rounded-full bg-cinema-accent text-white text-xs font-black uppercase tracking-wider shadow-glow-accent">
                Trending #{{ activeHeroIndex + 1 }}
              </span>
              <span class="px-3 py-1 rounded-full bg-amber-500/20 text-amber-300 border border-amber-500/30 text-xs font-bold flex items-center gap-1.5">
                ★ {{ featuredMovie?.rating }} IMDb
              </span>
              <span class="text-xs text-slate-300 font-semibold">
                {{ featuredMovie?.duration }} phút
              </span>
            </div>

            <h1 class="text-4xl sm:text-6xl lg:text-7xl font-black text-white tracking-tight drop-shadow-2xl max-w-3xl">
              {{ featuredMovie?.title }}
            </h1>

            <p class="text-cinema-muted text-sm sm:text-base max-w-2xl line-clamp-3">
              {{ featuredMovie?.description }}
            </p>

            <div class="flex flex-wrap items-center gap-4 pt-4">
              <!-- Book CTA -->
              <button 
                @click="goToMovie(featuredMovie)"
                class="px-8 py-3.5 rounded-2xl bg-cinema-accent hover:bg-rose-600 text-white font-bold text-sm tracking-wide shadow-glow-accent transition-all duration-300 flex items-center gap-2 hover:scale-105 cursor-pointer"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M4 4h16v16H4V4zm2 2v12h12V6H6z"/></svg>
                <span>Đặt vé ngay</span>
              </button>

              <!-- Trailer CTA -->
              <button 
                @click="openTrailer(featuredMovie)"
                class="px-6 py-3.5 rounded-2xl bg-cinema-surface/80 hover:bg-cinema-surface border border-white/10 text-white font-semibold text-sm transition-all duration-300 flex items-center gap-2 hover:border-white/30 cursor-pointer"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 fill-current text-cinema-gold" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                <span>Xem Trailer</span>
              </button>
            </div>

          </div>
        </div>
      </transition>

      <!-- Carousel Dots -->
      <div class="absolute bottom-6 right-6 md:right-12 z-20 flex items-center gap-2">
        <button 
          v-for="(_, index) in heroMovies" 
          :key="index"
          @click="activeHeroIndex = index"
          class="h-2 rounded-full transition-all duration-300"
          :class="[activeHeroIndex === index ? 'w-8 bg-cinema-accent' : 'w-2 bg-white/30 hover:bg-white/60']"
        ></button>
      </div>
    </section>

    <!-- 2. Main Movie Catalog Section -->
    <main id="movies-section" class="max-w-7xl mx-auto px-6 md:px-12 mt-12 space-y-8">
      
      <!-- Tabs: Now Showing vs Coming Soon & Genre Filter -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-cinema-border pb-6">
        
        <!-- Status Tabs -->
        <div class="flex items-center gap-3">
          <button 
            @click="activeTab = 'now_showing'"
            class="px-5 py-2.5 rounded-2xl font-extrabold text-sm transition-all duration-200"
            :class="[
              activeTab === 'now_showing' 
                ? 'bg-cinema-accent text-white shadow-glow-accent' 
                : 'bg-cinema-surface text-cinema-muted hover:text-white border border-cinema-border'
            ]"
          >
            🔥 Đang Chiếu ({{ store.nowShowingMovies.length }})
          </button>

          <button 
            @click="activeTab = 'coming_soon'"
            class="px-5 py-2.5 rounded-2xl font-extrabold text-sm transition-all duration-200"
            :class="[
              activeTab === 'coming_soon' 
                ? 'bg-cinema-accent text-white shadow-glow-accent' 
                : 'bg-cinema-surface text-cinema-muted hover:text-white border border-cinema-border'
            ]"
          >
            🍿 Sắp Chiếu ({{ store.comingSoonMovies.length }})
          </button>
        </div>

        <!-- Search input -->
        <div class="relative w-full md:w-64">
          <input 
            v-model="searchQuery" 
            type="text" 
            placeholder="Tìm tên phim, diễn viên..."
            class="w-full bg-cinema-surface border border-cinema-border rounded-xl pl-9 pr-4 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-cinema-accent"
          />
          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-500 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        </div>

      </div>

      <!-- Movie Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        <div 
          v-for="m in filteredMovies" 
          :key="m.id"
          class="group relative bg-cinema-surface/60 border border-cinema-border rounded-3xl overflow-hidden backdrop-blur-md flex flex-col justify-between hover:border-slate-500 transition-all duration-300 hover:-translate-y-1.5 shadow-lg"
        >
          <!-- Poster Thumbnail with Overlay & Rating Badge -->
          <div class="relative aspect-[2/3] w-full overflow-hidden">
            <img 
              :src="m.poster_url" 
              :alt="m.title"
              class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
            />
            
            <div class="absolute inset-0 bg-gradient-to-t from-cinema-surface via-transparent to-transparent opacity-80"></div>

            <!-- Rating badge -->
            <div class="absolute top-3 left-3 px-2.5 py-1 rounded-lg bg-black/60 backdrop-blur-md border border-white/10 text-amber-300 text-xs font-bold flex items-center gap-1">
              ★ {{ m.rating }}
            </div>

            <!-- Duration badge -->
            <div class="absolute top-3 right-3 px-2.5 py-1 rounded-lg bg-black/60 backdrop-blur-md border border-white/10 text-slate-300 text-[11px] font-semibold">
              {{ m.duration }}m
            </div>

            <!-- Play Trailer Hover Button -->
            <button 
              @click="openTrailer(m)"
              class="absolute inset-0 m-auto w-12 h-12 rounded-full bg-cinema-accent/90 text-white flex items-center justify-center shadow-glow-accent opacity-0 group-hover:opacity-100 transition-all duration-300 hover:scale-110 cursor-pointer"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 fill-current pl-0.5" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
            </button>
          </div>

          <!-- Movie Info & Booking CTA -->
          <div class="p-5 flex-1 flex flex-col justify-between space-y-4">
            <div class="space-y-1.5">
              <div class="flex flex-wrap gap-1.5">
                <span 
                  v-for="g in m.genre?.slice(0, 2)" 
                  :key="g"
                  class="text-[10px] uppercase font-bold text-cinema-muted px-2 py-0.5 rounded bg-white/5 border border-white/5"
                >
                  {{ g }}
                </span>
              </div>
              <h3 class="text-base font-extrabold text-white group-hover:text-amber-400 transition-colors line-clamp-1">
                {{ m.title }}
              </h3>
              <p class="text-xs text-cinema-muted line-clamp-2">
                {{ m.description }}
              </p>
            </div>

            <!-- Action Button -->
            <button 
              @click="goToMovie(m)"
              class="w-full py-2.5 rounded-xl font-bold text-xs tracking-wide transition-all duration-200 flex items-center justify-center gap-1.5 cursor-pointer"
              :class="[
                m.status === 'now_showing'
                  ? 'bg-cinema-accent hover:bg-rose-600 text-white shadow-glow-accent'
                  : 'bg-white/10 hover:bg-white/20 text-slate-300'
              ]"
            >
              <span>{{ m.status === 'now_showing' ? 'Chọn Suất Chiếu' : 'Xem Chi Tiết' }}</span>
              <span>→</span>
            </button>
          </div>

        </div>
      </div>

    </main>

    <!-- Trailer Modal -->
    <TrailerModal 
      :is-open="isTrailerOpen" 
      :title="selectedTrailerMovie?.title || ''"
      :video-url="selectedTrailerMovie?.trailer_url || ''"
      @close="isTrailerOpen = false"
    />

  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import { useBookingStore } from '../stores/bookingStore';
import Navbar from '../components/Navbar.vue';
import TrailerModal from '../components/TrailerModal.vue';
import type { Movie } from '../types';

const router = useRouter();
const store = useBookingStore();

const activeTab = ref<'now_showing' | 'coming_soon'>('now_showing');
const searchQuery = ref('');
const activeHeroIndex = ref(0);
let heroTimer: any = null;

// Trailer modal state
const isTrailerOpen = ref(false);
const selectedTrailerMovie = ref<Movie | null>(null);

onMounted(async () => {
  await store.fetchMovies();
  startHeroTimer();
});

onUnmounted(() => {
  if (heroTimer) clearInterval(heroTimer);
});

const heroMovies = computed(() => {
  return store.movies.slice(0, 4);
});

const featuredMovie = computed(() => {
  return heroMovies.value[activeHeroIndex.value] || store.movies[0];
});

const filteredMovies = computed(() => {
  const source = activeTab.value === 'now_showing' ? store.nowShowingMovies : store.comingSoonMovies;
  if (!searchQuery.value.trim()) return source;
  const q = searchQuery.value.toLowerCase();
  return source.filter(m => 
    m.title.toLowerCase().includes(q) || 
    m.genre.some(g => g.toLowerCase().includes(q))
  );
});

const startHeroTimer = () => {
  heroTimer = setInterval(() => {
    if (heroMovies.value.length > 0) {
      activeHeroIndex.value = (activeHeroIndex.value + 1) % heroMovies.value.length;
    }
  }, 6000);
};

const goToMovie = (movie?: Movie) => {
  if (!movie) return;
  store.selectMovie(movie);
  router.push({ name: 'movie-detail', params: { slug: movie.slug } });
};

const openTrailer = (movie?: Movie) => {
  if (!movie) return;
  selectedTrailerMovie.value = movie;
  isTrailerOpen.value = true;
};
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.6s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
