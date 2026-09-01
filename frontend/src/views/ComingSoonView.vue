<template>
  <div class="min-h-screen bg-cinema-bg flex flex-col justify-between">
    <div>
      <Navbar />

      <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
        
        <!-- Header Banner -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 border-b border-cinema-border pb-6">
          <div class="space-y-2">
            <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-cinema-gold">
              <Sparkles class="w-4 h-4 text-cinema-gold animate-ping" />
              <span>Sắp Ra Mắt & Đặt Vé Sớm</span>
            </div>
            <h1 class="text-3xl md:text-4xl font-black text-white tracking-tight">
              Phim Sắp Chiếu
            </h1>
            <p class="text-sm text-cinema-muted">
              Đón đầu các tác phẩm điện ảnh đỉnh cao sắp đổ bộ vào rạp trong thời gian tới.
            </p>
          </div>

          <!-- Quick Filters & Search -->
          <div class="flex items-center gap-3 flex-wrap">
            <div class="relative">
              <input 
                v-model="searchQuery" 
                type="text" 
                placeholder="Tìm phim sắp chiếu..." 
                class="bg-cinema-surface/90 border border-cinema-border rounded-xl pl-9 pr-4 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-cinema-accent w-48 sm:w-64"
              />
              <Search class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" />
            </div>
          </div>
        </div>

        <!-- Genre Filter Ghost Pills -->
        <div class="flex items-center gap-1.5 overflow-x-auto pb-2 pt-1 scrollbar-none select-none -mt-4">
          <button
            v-for="g in CURATED_GENRES"
            :key="g.id"
            @click="selectedGenre = g.id"
            class="px-3 py-1.5 rounded-full text-xs transition-all duration-200 shrink-0 cursor-pointer flex items-center gap-1.5 border"
            :class="[
              selectedGenre === g.id
                ? 'bg-amber-500/25 text-amber-300 border-amber-400/60 font-bold shadow-sm backdrop-blur-md scale-105'
                : 'bg-white/5 text-slate-400 border-white/5 hover:border-white/20 hover:text-slate-200 hover:bg-white/10 font-medium'
            ]"
          >
            <component :is="genreIcons[g.iconName]" class="w-3.5 h-3.5 shrink-0 opacity-90" />
            <span>{{ g.label }}</span>
          </button>
        </div>

        <!-- Skeletons Loader -->
        <div v-if="store.isLoading" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6">
          <div v-for="i in 10" :key="i" class="space-y-3">
            <BaseSkeleton class="aspect-[2/3] w-full" rounded="3xl" />
            <BaseSkeleton class="w-3/4 h-4" rounded="md" />
            <BaseSkeleton class="w-1/2 h-3" rounded="md" />
          </div>
        </div>

        <!-- Empty State -->
        <div v-else-if="filteredMovies.length === 0" class="p-16 text-center bg-cinema-surface/30 border border-cinema-border rounded-3xl space-y-3">
          <Sparkles class="w-10 h-10 text-amber-500 mx-auto" />
          <h3 class="text-base font-bold text-white">Không tìm thấy phim sắp chiếu phù hợp</h3>
          <p class="text-xs text-cinema-muted">Thử tìm kiếm với từ khóa hoặc thể loại khác</p>
        </div>

        <!-- Movie Catalog Grid -->
        <div v-else class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6">
          <MovieCard 
            v-for="movie in filteredMovies" 
            :key="movie.id"
            :movie="movie"
            @select="goToMovie"
            @trailer="openTrailer"
          />
        </div>

      </main>
    </div>

    <!-- Trailer Modal -->
    <TrailerModal 
      :is-open="isTrailerOpen" 
      :video-url="selectedTrailerUrl" 
      :title="selectedTrailerTitle"
      @close="isTrailerOpen = false" 
    />

    <Footer />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { 
  Sparkles, 
  Search, 
  Clapperboard,
  Flame,
  Rocket,
  Ghost,
  Smile,
  Compass,
  Theater,
  Heart,
  Users
} from 'lucide-vue-next';
import { useBookingStore } from '../stores/bookingStore';
import type { Movie } from '../types';
import Navbar from '../components/common/Navbar.vue';
import MovieCard from '../components/movie/MovieCard.vue';
import TrailerModal from '../components/movie/TrailerModal.vue';
import Footer from '../components/common/Footer.vue';
import BaseSkeleton from '../components/base/BaseSkeleton.vue';
import { CURATED_GENRES } from '../constants';

const genreIcons: Record<string, any> = {
  Clapperboard,
  Flame,
  Sparkles,
  Rocket,
  Ghost,
  Smile,
  Compass,
  Theater,
  Heart,
  Users,
};

const router = useRouter();
const store = useBookingStore();

const searchQuery = ref('');
const selectedGenre = ref('all');

const isTrailerOpen = ref(false);
const selectedTrailerUrl = ref('');
const selectedTrailerTitle = ref('');

const filteredMovies = computed(() => {
  const currentGenreConfig = CURATED_GENRES.find(g => g.id === selectedGenre.value);

  return store.comingSoonMovies.filter(m => {
    let matchesGenre = true;
    if (currentGenreConfig && currentGenreConfig.id !== 'all') {
      const keywords = currentGenreConfig.keywords;
      matchesGenre = Array.isArray(m.genre) && m.genre.some(g => {
        const gl = g.toLowerCase();
        return keywords.some(kw => gl.includes(kw) || kw.includes(gl));
      });
    }

    let matchesSearch = true;
    if (searchQuery.value.trim()) {
      const q = searchQuery.value.toLowerCase();
      matchesSearch = m.title.toLowerCase().includes(q) || 
        Boolean(m.original_title?.toLowerCase().includes(q)) ||
        Boolean(m.director?.toLowerCase().includes(q));
    }

    return matchesGenre && matchesSearch;
  });
});

const goToMovie = (movie: Movie) => {
  store.selectMovie(movie);
  router.push({
    name: 'movie-detail',
    params: { slug: movie.slug }
  });
};

const openTrailer = (movie: Movie) => {
  if (movie.trailer_url) {
    selectedTrailerUrl.value = movie.trailer_url;
    selectedTrailerTitle.value = movie.title;
    isTrailerOpen.value = true;
  }
};

onMounted(async () => {
  if (store.movies.length === 0) {
    await store.fetchMovies();
  }
});
</script>
