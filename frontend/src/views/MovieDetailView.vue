<template>
  <div class="min-h-screen bg-cinema-bg flex flex-col justify-between select-none">
    <!-- Global Header -->
    <Navbar />

    <main class="flex-1 pb-24">
      <!-- 1. Hero Backdrop & Movie Header Details -->
      <section class="relative w-full h-[460px] md:h-[540px] overflow-hidden">
        <!-- Backdrop Image with Cinematic Gradients -->
        <img 
          :src="movie?.backdrop_url || movie?.poster_url || 'https://images.unsplash.com/photo-1534447677768-be436bb09401?w=1600'" 
          :alt="movie?.title"
          class="w-full h-full object-cover filter brightness-[0.45] scale-105"
        />
        <div class="absolute inset-0 bg-gradient-to-t from-cinema-bg via-cinema-bg/60 to-transparent"></div>
        <div class="absolute inset-0 bg-gradient-to-r from-cinema-bg via-cinema-bg/40 to-transparent"></div>

        <!-- Movie Header Content -->
        <div class="absolute inset-0 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-end pb-8">
          <div class="space-y-4 max-w-4xl">
            <!-- Badges & Ratings -->
            <div class="flex flex-wrap items-center gap-2.5">
              <!-- Age Rating Badge -->
              <span 
                class="px-2.5 py-1 rounded-full text-xs font-black tracking-wider shadow-md uppercase"
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

              <span class="px-3 py-1 rounded-full bg-amber-500/20 border border-amber-500/30 text-amber-400 text-xs font-bold flex items-center gap-1">
                <Star class="w-3.5 h-3.5 fill-amber-400" />
                <span>{{ movie?.rating || '8.5' }} TMDb</span>
              </span>

              <span class="px-3 py-1 rounded-full bg-white/5 border border-white/10 text-slate-300 text-xs font-medium flex items-center gap-1">
                <Clock class="w-3.5 h-3.5 text-slate-400" />
                <span>{{ movie?.duration || 120 }} phút</span>
              </span>

              <span 
                v-for="g in movie?.genre?.slice(0, 4)" 
                :key="g"
                class="px-2.5 py-0.5 rounded-full bg-white/5 border border-white/5 text-[11px] font-semibold text-slate-300"
              >
                {{ g }}
              </span>
            </div>

            <!-- Title -->
            <h1 class="text-2xl sm:text-4xl lg:text-5xl font-black text-white tracking-tight leading-tight">
              {{ movie?.title }}
            </h1>

            <!-- Age Advisory Banner -->
            <div 
              v-if="ageInfo.isRestricted"
              class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs font-semibold"
            >
              <AlertCircle class="w-4 h-4 text-rose-400 shrink-0" />
              <span>{{ ageInfo.description }}</span>
            </div>

            <!-- Description -->
            <p class="text-xs sm:text-sm text-cinema-muted line-clamp-3 leading-relaxed max-w-3xl">
              {{ movie?.description }}
            </p>

            <!-- Director & Cast -->
            <div class="text-xs text-slate-400 space-y-1">
              <p v-if="movie?.director">
                <strong class="text-slate-200">Đạo diễn:</strong> {{ movie.director }}
              </p>
              <p v-if="movie?.cast?.length">
                <strong class="text-slate-200">Diễn viên:</strong> {{ movie.cast.slice(0, 5).join(', ') }}
              </p>
            </div>

            <!-- Trailer Button -->
            <div v-if="movie?.trailer_url" class="pt-2">
              <button 
                @click="openTrailer"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-cinema-accent hover:bg-rose-600 text-white text-xs font-bold shadow-glow-accent transition-all cursor-pointer"
              >
                <Play class="w-4 h-4 fill-white" />
                <span>Xem Trailer Trực Tiếp</span>
              </button>
            </div>
          </div>
        </div>
      </section>

      <!-- 2. Showtime Booking Section -->
      <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
        
        <!-- Date Slider Picker -->
        <DateSlider 
          v-model="selectedDate" 
          label="1. CHỌN NGÀY CHIẾU" 
        />

        <!-- City Filter & Cinemas List -->
        <div class="space-y-6">
          <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border-b border-cinema-border pb-4">
            <h2 class="text-xs font-black uppercase tracking-widest text-slate-300 flex items-center gap-2">
              <span>2. LỊCH CHIẾU THEO CỤM RẠP TẠI VIỆT NAM</span>
            </h2>

            <!-- City Filter Pills -->
            <div class="flex items-center gap-2 overflow-x-auto pb-1 max-w-full">
              <button
                v-for="city in availableCities"
                :key="city"
                @click="selectedCity = city"
                class="px-3.5 py-1.5 rounded-full text-xs font-bold transition-all cursor-pointer whitespace-nowrap"
                :class="[
                  selectedCity === city
                    ? 'bg-white text-slate-950 shadow-md font-black'
                    : 'bg-cinema-surface text-slate-400 hover:text-white border border-white/5'
                ]"
              >
                {{ city }}
              </button>
            </div>
          </div>

          <!-- Cinema Groups Loop -->
          <div v-if="filteredCinemaGroups.length > 0" class="space-y-5">
            <div 
              v-for="group in filteredCinemaGroups" 
              :key="group.cinema.id"
              class="bg-cinema-surface/70 border border-cinema-border rounded-3xl p-6 backdrop-blur-md space-y-4 hover:border-white/20 transition-all shadow-xl"
            >
              <div class="flex flex-wrap items-center justify-between gap-2 border-b border-white/5 pb-4">
                <div>
                  <h3 class="text-base font-extrabold text-white flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full" :class="getCinemaDotColor(group.cinema.name)"></span>
                    {{ group.cinema.name }}
                  </h3>
                  <p class="text-xs text-cinema-muted mt-0.5">{{ group.cinema.address }}</p>
                </div>
                <span class="text-xs px-3 py-1 rounded-full bg-white/5 text-amber-300 border border-white/10 font-bold">
                  {{ group.cinema.city }}
                </span>
              </div>

              <!-- Showtime Slots -->
              <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
                <button 
                  v-for="st in group.showtimes" 
                  :key="st.id"
                  @click="handleSelectShowtime(st)"
                  :disabled="isShowtimeExpired(st, selectedDate)"
                  class="group relative flex flex-col items-center justify-center p-3.5 rounded-2xl border transition-all duration-300"
                  :class="[
                    isShowtimeExpired(st, selectedDate)
                      ? 'opacity-35 grayscale bg-slate-950/40 border-slate-800 cursor-not-allowed text-slate-500'
                      : store.selectedShowtime?.id === st.id
                        ? 'bg-cinema-accent text-white shadow-glow-accent border-cinema-accent cursor-pointer'
                        : 'bg-slate-900/80 hover:bg-slate-800 border-cinema-border hover:border-slate-500 text-slate-200 cursor-pointer'
                  ]"
                >
                  <!-- Dynamic Pricing Badge (Happy Wednesday / Weekend Surge) -->
                  <ShowtimePricingBadge 
                    v-if="!isShowtimeExpired(st, selectedDate)" 
                    :showtime="st" 
                    :targetDate="selectedDate" 
                    class="mb-1" 
                  />

                  <span class="text-lg font-black font-mono transition-colors" :class="isShowtimeExpired(st, selectedDate) ? 'line-through text-slate-600' : 'group-hover:text-amber-400'">
                    {{ st.start_time }}
                  </span>
                  <span class="text-[11px] mt-0.5 font-semibold" :class="isShowtimeExpired(st, selectedDate) ? 'text-slate-600' : 'text-slate-400'">
                    {{ isShowtimeExpired(st, selectedDate) ? 'Đã qua giờ' : (st.room?.room_type || st.room?.name || '2D Standard') }}
                  </span>
                  <span 
                    class="mt-1 text-[10px] font-bold px-2 py-0.5 rounded-full font-mono"
                    :class="isShowtimeExpired(st, selectedDate) ? 'bg-slate-800/40 text-slate-600' : 'bg-emerald-500/10 text-emerald-400'"
                  >
                    {{ isShowtimeExpired(st, selectedDate) ? 'Hết Hạn' : formatVndPrice(getDynamicPricing(st, selectedDate).priceStandard) }}
                  </span>
                </button>
              </div>
            </div>
          </div>

          <!-- Empty State -->
          <div v-else class="text-center py-16 bg-cinema-surface/40 rounded-3xl border border-cinema-border space-y-3">
            <Film class="w-12 h-12 text-slate-600 mx-auto" />
            <h4 class="text-base font-bold text-white">Chưa có suất chiếu cho ngày hoặc thành phố này</h4>
            <p class="text-xs text-cinema-muted max-w-sm mx-auto">
              Vui lòng chọn ngày chiếu khác trên thanh lịch hoặc bấm vào thành phố khác để xem lịch rạp gần bạn nhất.
            </p>
          </div>
        </div>

      </section>

      <!-- 3. Movie Reviews & Star Ratings Community Section -->
      <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <MovieReviewsSection 
          :movieId="movie?.id" 
          :currentRating="movie?.rating"
          @review-added="handleReviewAdded"
        />
      </section>
    </main>

    <!-- Trailer Modal -->
    <TrailerModal 
      :is-open="isTrailerOpen" 
      :video-url="movie?.trailer_url || ''" 
      :title="movie?.title || 'Trailer'" 
      @close="isTrailerOpen = false" 
    />

    <!-- Global Footer -->
    <Footer />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { Star, Clock, Play, Film, AlertCircle } from 'lucide-vue-next';
import { useBookingStore } from '../stores/bookingStore';
import { useToast } from '../composables/useToast';
import { isShowtimeExpired, getAgeRatingInfo } from '../utils/formatters';
import { useDynamicPricing } from '../composables/useDynamicPricing';
import api from '../services/api';
import Navbar from '../components/common/Navbar.vue';
import DateSlider from '../components/common/DateSlider.vue';
import TrailerModal from '../components/movie/TrailerModal.vue';
import MovieReviewsSection from '../components/movie/MovieReviewsSection.vue';
import ShowtimePricingBadge from '../components/showtime/ShowtimePricingBadge.vue';
import Footer from '../components/common/Footer.vue';
import type { Showtime } from '../types';

const route = useRoute();
const router = useRouter();
const store = useBookingStore();
const toast = useToast();
const { getDynamicPricing } = useDynamicPricing();

const isTrailerOpen = ref(false);
const selectedCity = ref('Tất cả');
const selectedDate = ref(new Date().toISOString().split('T')[0]);

const handleReviewAdded = async () => {
  await loadMovieDetails();
};

const movie = computed(() => store.currentMovie);

const ageInfo = computed(() => {
  return getAgeRatingInfo(movie.value?.age_rating || 'T18');
});

const availableCities = ['Tất cả', 'Hồ Chí Minh', 'Hà Nội', 'Đà Nẵng', 'Cần Thơ', 'Hải Phòng', 'Lâm Đồng'];

watch(selectedDate, (newDate) => {
  store.selectDate(newDate);
});

// Normalize city names to match (e.g. 'TP. Hồ Chí Minh' <-> 'Hồ Chí Minh')
const matchesCity = (cinemaCity: string, filterCity: string) => {
  if (filterCity === 'Tất cả') return true;
  const c1 = cinemaCity.replace(/^TP\.?\s*/i, '').trim().toLowerCase();
  const c2 = filterCity.replace(/^TP\.?\s*/i, '').trim().toLowerCase();
  return c1.includes(c2) || c2.includes(c1);
};

// Gom nhóm lịch chiếu theo Rạp & lọc theo Ngày + Thành phố
const filteredCinemaGroups = computed(() => {
  const allShowtimes = store.currentMovie?.showtimes || [];
  const currentDate = selectedDate.value;

  // 1. Lọc theo ngày chiếu
  let dateFiltered = allShowtimes.filter(st => {
    return !st.show_date || st.show_date === currentDate || st.show_date.startsWith(currentDate);
  });

  // Nếu ngày hiện tại chưa có, fallback lấy các suất chiếu đầu tiên
  if (dateFiltered.length === 0 && allShowtimes.length > 0) {
    dateFiltered = allShowtimes.slice(0, 15);
  }

  // 2. Gom nhóm theo Cinema
  const cinemaMap = new Map<number, { cinema: any, showtimes: Showtime[] }>();

  dateFiltered.forEach(st => {
    const cinema = st.cinema || { id: 1, name: 'CGV Vincom Landmark 81', address: 'Bình Thạnh, TP.HCM', city: 'Hồ Chí Minh' };
    
    if (!matchesCity(cinema.city || 'Hồ Chí Minh', selectedCity.value)) {
      return;
    }

    if (!cinemaMap.has(cinema.id)) {
      cinemaMap.set(cinema.id, {
        cinema,
        showtimes: []
      });
    }
    cinemaMap.get(cinema.id)!.showtimes.push(st);
  });

  return Array.from(cinemaMap.values());
});

const getCinemaDotColor = (name: string) => {
  if (name.includes('CGV')) return 'bg-rose-500';
  if (name.includes('Lotte')) return 'bg-red-600';
  if (name.includes('Galaxy')) return 'bg-amber-400';
  if (name.includes('BHD')) return 'bg-emerald-500';
  if (name.includes('Beta')) return 'bg-cyan-400';
  return 'bg-cinema-accent';
};

const formatVndPrice = (price: number) => {
  if (!price) return '95.000 đ';
  return new Intl.NumberFormat('vi-VN').format(price) + ' đ';
};

const openTrailer = () => {
  if (movie.value?.trailer_url) {
    isTrailerOpen.value = true;
  }
};

const handleSelectShowtime = (st: Showtime) => {
  if (isShowtimeExpired(st, selectedDate.value)) {
    toast.warning('Suất chiếu này đã bắt đầu hoặc kết thúc. Vui lòng chọn suất chiếu kế tiếp!', 'Suất Chiếu Quá Hạn');
    return;
  }
  store.selectedShowtime = st;
  store.selectedDate = selectedDate.value;
  const currentSlug = store.currentMovie?.slug || (route.params.slug as string) || 'spider-man-across-the-spider-verse';
  router.push({ 
    name: 'seat-selection', 
    params: { 
      slug: currentSlug,
      showtimeId: String(st.id) 
    } 
  });
};

const loadMovieDetails = async () => {
  const slug = route.params.slug as string;
  if (!slug) return;

  try {
    const res = await api.get(`/movies/${slug}`);
    if (res.data?.data) {
      store.selectMovie(res.data.data);
      document.title = `${res.data.data.title} - Lịch Chiếu & Đặt Vé | CineReserve`;
    }
  } catch (e) {
    // Fallback find in store
    const found = store.movies.find(m => m.slug === slug || String(m.id) === slug);
    if (found) {
      store.selectMovie(found);
    }
  }
};

onMounted(async () => {
  store.selectedShowtime = null;
  if (store.movies.length === 0) {
    await store.fetchMovies();
  }
  await loadMovieDetails();

  if (store.selectedDate) {
    selectedDate.value = store.selectedDate;
  }
});
</script>
