<template>
  <div class="min-h-screen bg-cinema-bg flex flex-col justify-between">
    <Navbar />

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full flex-1 space-y-8">
      
      <!-- Page Title -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-cinema-border pb-6">
        <div class="space-y-1">
          <div class="flex items-center gap-2">
            <span class="p-2 rounded-2xl bg-cyan-500/10 border border-cyan-500/30 text-cyan-400">
              <Building2 class="w-5 h-5" />
            </span>
            <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight">Cụm Rạp & Lịch Chiếu</h1>
          </div>
          <p class="text-xs text-cinema-muted">
            Hệ thống rạp chiếu phim chất lượng cao trên toàn quốc với phòng chiếu 2D, 3D, IMAX và Sweetbox VIP
          </p>
        </div>
      </div>

      <!-- Filter Controls: City, Chain & Search -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 p-4 rounded-3xl bg-cinema-card/50 border border-cinema-border">
        <!-- City Filter Tabs -->
        <BaseSelect 
          v-model="selectedCity"
          :options="cityOptions"
          @update:modelValue="fetchCinemas"
        >
          <template #prefix>
            <MapPin class="w-3.5 h-3.5 text-cyan-400" />
          </template>
        </BaseSelect>

        <!-- Cinema Chain Filter -->
        <BaseSelect 
          v-model="selectedChain"
          :options="chainOptions"
          @update:modelValue="fetchCinemas"
        >
          <template #prefix>
            <Building2 class="w-3.5 h-3.5 text-cinema-gold" />
          </template>
        </BaseSelect>

        <!-- Search Input -->
        <div class="relative">
          <input 
            v-model="searchQuery"
            @input="handleSearch"
            type="text"
            placeholder="Tìm theo tên rạp, địa chỉ..."
            class="w-full bg-slate-900/90 border border-cinema-border rounded-xl pl-9 pr-4 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-cinema-accent transition-colors"
          />
          <Search class="w-4 h-4 text-slate-400 absolute left-3 top-3" />
        </div>
      </div>

      <!-- Loading Skeletons -->
      <div v-if="isLoading" class="space-y-4">
        <div v-for="i in 4" :key="i" class="h-28 rounded-3xl bg-cinema-card/40 border border-white/5 animate-pulse"></div>
      </div>

      <!-- Empty State -->
      <div 
        v-else-if="cinemas.length === 0" 
        class="p-16 rounded-3xl bg-cinema-card/30 border border-cinema-border text-center space-y-3"
      >
        <Building2 class="w-10 h-10 text-slate-500 mx-auto" />
        <h3 class="text-base font-bold text-white">Không tìm thấy cụm rạp phù hợp</h3>
        <p class="text-xs text-cinema-muted">Thử chọn thành phố hoặc từ khóa tìm kiếm khác</p>
      </div>

      <!-- Cinemas List -->
      <div v-else class="space-y-4">
        <CinemaCard 
          v-for="cinema in cinemas" 
          :key="cinema.id"
          :cinema="cinema"
          :expandedId="expandedCinemaId"
          :selectedDate="activeDateMap[cinema.id] || todayDate"
          :isLoadingShowtimes="loadingShowtimesId === cinema.id"
          :moviesInCinema="cinemaShowtimesMap[cinema.id] || []"
          @toggle-expand="toggleExpand"
          @change-date="handleDateChange"
          @select-showtime="goToBooking"
        />
      </div>

    </main>

    <Footer />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { Building2, Search, MapPin } from 'lucide-vue-next';
import api from '../services/api';
import { useBookingStore } from '../stores/bookingStore';
import Navbar from '../components/common/Navbar.vue';
import Footer from '../components/common/Footer.vue';
import CinemaCard from '../components/cinema/CinemaCard.vue';
import BaseSelect from '../components/base/BaseSelect.vue';

const router = useRouter();
const store = useBookingStore();

const todayDate = new Date().toISOString().split('T')[0];
const selectedCity = ref('Tất cả');
const selectedChain = ref('Tất cả');
const searchQuery = ref('');
const isLoading = ref(false);

const cinemas = ref<any[]>([]);
const expandedCinemaId = ref<number | null>(null);
const loadingShowtimesId = ref<number | null>(null);
const activeDateMap = ref<Record<number, string>>({});
const cinemaShowtimesMap = ref<Record<number, any[]>>({});

import { CITY_OPTIONS, CINEMA_CHAIN_OPTIONS } from '../constants';

const cityOptions = CITY_OPTIONS;
const chainOptions = CINEMA_CHAIN_OPTIONS;

let searchTimer: any = null;
const handleSearch = () => {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(() => {
    fetchCinemas();
  }, 350);
};

const fetchCinemas = async () => {
  isLoading.value = true;
  try {
    const params: any = {};
    if (selectedCity.value !== 'Tất cả') params.city = selectedCity.value;
    if (selectedChain.value !== 'Tất cả') params.chain = selectedChain.value;
    if (searchQuery.value.trim()) params.search = searchQuery.value.trim();

    const res = await api.get('/cinemas', { params });
    if (res.data?.data) {
      cinemas.value = res.data.data;
    }
  } catch (e) {
    console.warn('Error fetching cinemas:', e);
  } finally {
    isLoading.value = false;
  }
};

const loadShowtimes = async (cinemaId: number, dateStr: string) => {
  loadingShowtimesId.value = cinemaId;
  try {
    const res = await api.get(`/cinemas/${cinemaId}/showtimes`, {
      params: { date: dateStr },
    });
    if (res.data?.movies) {
      cinemaShowtimesMap.value[cinemaId] = res.data.movies;
    }
  } catch (e) {
    console.warn('Failed to load showtimes:', e);
  } finally {
    loadingShowtimesId.value = null;
  }
};

const toggleExpand = async (cinemaId: number) => {
  if (expandedCinemaId.value === cinemaId) {
    expandedCinemaId.value = null;
    return;
  }

  expandedCinemaId.value = cinemaId;
  const curDate = activeDateMap.value[cinemaId] || todayDate;
  activeDateMap.value[cinemaId] = curDate;

  if (!cinemaShowtimesMap.value[cinemaId]) {
    await loadShowtimes(cinemaId, curDate);
  }
};

const handleDateChange = async (cinemaId: number, dateStr: string) => {
  activeDateMap.value[cinemaId] = dateStr;
  await loadShowtimes(cinemaId, dateStr);
};

const goToBooking = (movie: any, showtime: any) => {
  store.selectMovie(movie);
  store.selectShowtime(showtime);
  router.push({
    name: 'seat-selection',
    params: {
      slug: movie.slug || movie.id,
      showtimeId: showtime.id,
    },
  });
};

onMounted(() => {
  fetchCinemas();
});
</script>
