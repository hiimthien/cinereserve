<template>
  <div class="min-h-screen bg-cinema-bg flex flex-col justify-between">
    <!-- Global Header -->
    <Navbar />

    <!-- 1. Hero Featured Movie Skeleton (Matches Real Hero Layout 100%) -->
    <section v-if="store.isLoading && !featuredMovie" class="relative w-full h-[520px] md:h-[620px] overflow-hidden bg-cinema-bg">
      <div class="absolute inset-0 bg-slate-900/60"></div>
      <div class="absolute inset-0 bg-gradient-to-t from-cinema-bg via-cinema-bg/40 to-transparent"></div>
      <div class="absolute inset-0 bg-gradient-to-r from-cinema-bg via-cinema-bg/60 to-transparent"></div>

      <div class="absolute inset-0 max-w-7xl mx-auto px-6 md:px-12 flex flex-col justify-center space-y-4">
        <!-- Badges & Ratings Skeleton -->
        <div class="flex items-center gap-3 flex-wrap">
          <BaseSkeleton class="w-28 h-7" rounded="full" />
          <BaseSkeleton class="w-24 h-7" rounded="full" />
          <BaseSkeleton class="w-20 h-7" rounded="full" />
          <BaseSkeleton class="w-20 h-7" rounded="full" />
        </div>

        <!-- Title Skeleton (Left aligned) -->
        <BaseSkeleton class="w-full max-w-2xl h-12 sm:h-16" rounded="2xl" />

        <!-- Description Skeleton -->
        <div class="space-y-2 max-w-2xl">
          <BaseSkeleton class="w-full h-4" rounded="md" />
          <BaseSkeleton class="w-4/5 h-4" rounded="md" />
          <BaseSkeleton class="w-3/5 h-4" rounded="md" />
        </div>

        <!-- Action Buttons Skeleton -->
        <div class="flex flex-wrap items-center gap-4 pt-4">
          <BaseSkeleton class="w-36 h-12" rounded="2xl" />
          <BaseSkeleton class="w-36 h-12" rounded="2xl" />
        </div>
      </div>
    </section>

    <section v-else-if="featuredMovie" class="relative w-full h-[520px] md:h-[620px] overflow-hidden">
      <transition name="fade" mode="out-in">
        <div :key="activeHeroIndex" class="relative w-full h-full">
          <!-- Backdrop image -->
          <img 
            :src="featuredMovie.backdrop_url || featuredMovie.poster_url" 
            :alt="featuredMovie.title"
            class="w-full h-full object-cover filter brightness-[0.55] transition-all duration-1000 scale-105"
          />
          <div class="absolute inset-0 bg-gradient-to-t from-cinema-bg via-cinema-bg/40 to-transparent"></div>
          <div class="absolute inset-0 bg-gradient-to-r from-cinema-bg via-cinema-bg/60 to-transparent"></div>

          <!-- Hero Details -->
          <div class="absolute inset-0 max-w-7xl mx-auto px-6 md:px-12 flex flex-col justify-center space-y-4">
            
            <div class="flex items-center gap-3 flex-wrap">
              <BaseBadge variant="accent" size="sm">
                <Flame class="w-3.5 h-3.5 text-cinema-accent" />
                <span>Trending #{{ activeHeroIndex + 1 }}</span>
              </BaseBadge>
              <BaseBadge v-if="featuredMovie.rating" variant="gold" size="sm">
                <Star class="w-3.5 h-3.5 fill-amber-400 text-amber-400" />
                <span>{{ Number(featuredMovie.rating).toFixed(1) }} TMDb</span>
              </BaseBadge>
              <BaseBadge variant="neutral" size="sm">
                <Clock class="w-3.5 h-3.5 text-slate-400" />
                <span>{{ featuredMovie.duration || 120 }} phút</span>
              </BaseBadge>
              <BaseBadge 
                v-for="g in featuredMovie.genre?.slice(0, 3)" 
                :key="g"
                variant="outline"
                size="sm"
              >
                {{ g }}
              </BaseBadge>
            </div>

            <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black text-white tracking-tight drop-shadow-2xl max-w-3xl leading-tight">
              {{ featuredMovie.title }}
            </h1>

            <p class="text-cinema-muted text-xs sm:text-sm max-w-2xl line-clamp-3">
              {{ featuredMovie.description }}
            </p>

            <div class="flex flex-wrap items-center gap-4 pt-4">
              <!-- Book CTA -->
              <BaseButton 
                variant="primary" 
                size="lg"
                @click="goToMovie(featuredMovie)"
              >
                <template #prefix>
                  <Ticket class="w-4 h-4" />
                </template>
                Đặt vé ngay
              </BaseButton>

              <!-- Trailer CTA -->
              <BaseButton 
                v-if="featuredMovie.trailer_url"
                variant="secondary" 
                size="lg"
                @click="openTrailer(featuredMovie)"
              >
                <template #prefix>
                  <Play class="w-4 h-4 fill-cinema-gold text-cinema-gold" />
                </template>
                Xem Trailer
              </BaseButton>
            </div>

          </div>
        </div>
      </transition>

      <!-- Carousel Dots -->
      <div v-if="heroMovies.length > 1" class="absolute bottom-6 right-6 md:right-12 z-20 flex items-center gap-2">
        <button 
          v-for="(_, index) in heroMovies" 
          :key="index"
          @click="activeHeroIndex = index"
          class="h-2 rounded-full transition-all duration-300 cursor-pointer"
          :class="[activeHeroIndex === index ? 'w-8 bg-cinema-accent' : 'w-2 bg-white/30 hover:bg-white/60']"
          aria-label="Chọn banner"
        ></button>
      </div>
    </section>

    <!-- 2. Main Movie Catalog Section -->
    <main id="movies-section" class="max-w-7xl mx-auto px-6 md:px-12 mt-12 space-y-8 flex-1 w-full">
      
      <!-- Tabs: Now Showing vs Coming Soon & Search Filter -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-cinema-border pb-6">
        
        <!-- Status Tabs using BaseButton -->
        <div class="flex items-center gap-3">
          <BaseButton 
            :variant="activeTab === 'now_showing' ? 'primary' : 'secondary'"
            size="md"
            @click="activeTab = 'now_showing'"
          >
            <template #prefix>
              <Flame class="w-4 h-4 text-orange-400" />
            </template>
            <span>Đang Chiếu</span>
            <span v-if="!store.isLoading" class="ml-1 opacity-90 font-mono">({{ store.nowShowingMovies.length }})</span>
            <span v-else class="ml-1 opacity-60 font-mono animate-pulse">(...)</span>
          </BaseButton>

          <BaseButton 
            :variant="activeTab === 'coming_soon' ? 'gold' : 'secondary'"
            size="md"
            @click="activeTab = 'coming_soon'"
          >
            <template #prefix>
              <Sparkles class="w-4 h-4 text-amber-950" />
            </template>
            <span>Sắp Chiếu</span>
            <span v-if="!store.isLoading" class="ml-1 opacity-90 font-mono">({{ store.comingSoonMovies.length }})</span>
            <span v-else class="ml-1 opacity-60 font-mono animate-pulse">(...)</span>
          </BaseButton>
        </div>

        <!-- Search and Quick Link Filters -->
        <div class="flex items-center gap-3">
          <div class="relative">
            <input 
              v-model="searchQuery" 
              type="text" 
              placeholder="Tìm phim theo tên, đạo diễn..." 
              class="bg-cinema-surface/90 border border-cinema-border rounded-xl pl-9 pr-4 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-cinema-accent w-48 sm:w-64"
            />
            <Search class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" />
          </div>

          <!-- Quick Link to Cinemas -->
          <router-link 
            to="/cinemas"
            class="px-4 py-2 rounded-xl bg-cinema-surface border border-cinema-border hover:border-white/20 text-slate-300 hover:text-white text-xs font-semibold flex items-center gap-1.5 transition-all"
          >
            <MapPin class="w-3.5 h-3.5 text-cinema-accent" />
            <span>Chọn theo Rạp</span>
          </router-link>
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
              ? 'bg-white/20 text-white border-white/40 font-bold shadow-sm backdrop-blur-md scale-105'
              : 'bg-white/5 text-slate-400 border-white/5 hover:border-white/20 hover:text-slate-200 hover:bg-white/10 font-medium'
          ]"
        >
          <component :is="genreIcons[g.iconName]" class="w-3.5 h-3.5 shrink-0 opacity-90" />
          <span>{{ g.label }}</span>
        </button>
      </div>

      <!-- Loading Skeleton Grid Animation -->
      <div v-if="store.isLoading" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6">
        <div v-for="i in 10" :key="i" class="space-y-3">
          <BaseSkeleton class="aspect-[2/3] w-full" rounded="3xl" />
          <BaseSkeleton class="w-3/4 h-4" rounded="md" />
          <BaseSkeleton class="w-1/2 h-3" rounded="md" />
        </div>
      </div>

      <!-- Empty State -->
      <div v-else-if="filteredMovies.length === 0" class="p-16 text-center bg-cinema-surface/30 border border-cinema-border rounded-3xl space-y-3">
        <Film class="w-10 h-10 text-slate-500 mx-auto" />
        <h3 class="text-base font-bold text-white">Không tìm thấy phim phù hợp</h3>
        <p class="text-xs text-cinema-muted">Thử tìm kiếm với từ khóa khác</p>
      </div>

      <!-- Movie Card Grid -->
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

    <!-- Trailer Modal -->
    <TrailerModal 
      :is-open="isTrailerOpen" 
      :video-url="selectedTrailerUrl" 
      :title="selectedTrailerTitle"
      @close="isTrailerOpen = false" 
    />

    <!-- Global Footer -->
    <Footer />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import { 
  Flame, 
  Star, 
  Clock, 
  Ticket, 
  Play, 
  Sparkles, 
  Search, 
  MapPin, 
  Film,
  Clapperboard,
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
import BaseButton from '../components/base/BaseButton.vue';
import BaseBadge from '../components/base/BaseBadge.vue';
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

const activeTab = ref<'now_showing' | 'coming_soon'>('now_showing');
const activeHeroIndex = ref(0);
const searchQuery = ref('');
const selectedGenre = ref('all');

const isTrailerOpen = ref(false);
const selectedTrailerUrl = ref('');
const selectedTrailerTitle = ref('');
let autoSlideTimer: any = null;

const heroMovies = computed(() => {
  return store.nowShowingMovies.slice(0, 5);
});

const featuredMovie = computed(() => {
  return heroMovies.value[activeHeroIndex.value] || store.nowShowingMovies[0];
});

const filteredMovies = computed(() => {
  const list = activeTab.value === 'now_showing' 
    ? store.nowShowingMovies 
    : store.comingSoonMovies;

  const currentGenreConfig = CURATED_GENRES.find(g => g.id === selectedGenre.value);

  return list.filter(movie => {
    // 1. Khớp từ khóa tìm kiếm
    let matchesSearch = true;
    if (searchQuery.value.trim()) {
      const q = searchQuery.value.toLowerCase();
      matchesSearch = movie.title.toLowerCase().includes(q) || 
        Boolean(movie.original_title?.toLowerCase().includes(q)) ||
        Boolean(movie.director?.toLowerCase().includes(q));
    }

    // 2. Khớp thể loại phim theo danh mục chuẩn hóa
    let matchesGenre = true;
    if (currentGenreConfig && currentGenreConfig.id !== 'all') {
      const keywords = currentGenreConfig.keywords;
      matchesGenre = Array.isArray(movie.genre) && movie.genre.some(g => {
        const gl = g.toLowerCase();
        return keywords.some(kw => gl.includes(kw) || kw.includes(gl));
      });
    }

    return matchesSearch && matchesGenre;
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
  await store.fetchMovies();
  // Hero auto slide
  autoSlideTimer = setInterval(() => {
    if (heroMovies.value.length > 1) {
      activeHeroIndex.value = (activeHeroIndex.value + 1) % heroMovies.value.length;
    }
  }, 7000);
});

onUnmounted(() => {
  if (autoSlideTimer) {
    clearInterval(autoSlideTimer);
    autoSlideTimer = null;
  }
});
</script>
