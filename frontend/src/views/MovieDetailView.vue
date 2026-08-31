<template>
  <div class="min-h-screen bg-cinema-bg pb-24">
    <!-- Global Header -->
    <Navbar />

    <!-- Hero Section with Trailer / Backdrop -->
    <div class="relative w-full h-[480px] md:h-[560px] overflow-hidden">
      <!-- Backdrop Image with Gradient Overlay -->
      <img 
        :src="movie?.backdrop_url || 'https://images.unsplash.com/photo-1509198397868-475647b2a1e5?w=1600&auto=format&fit=crop&q=80'" 
        :alt="movie?.title" 
        class="w-full h-full object-cover object-center filter brightness-[0.55] transform scale-105 transition-transform duration-1000"
      />
      <div class="absolute inset-0 bg-gradient-to-t from-cinema-bg via-cinema-bg/60 to-transparent"></div>
      <div class="absolute inset-0 bg-gradient-to-r from-cinema-bg via-cinema-bg/40 to-transparent"></div>

      <!-- Back button -->
      <div class="absolute top-6 left-6 md:left-12 z-20">
        <router-link 
          to="/" 
          class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-black/40 hover:bg-black/60 backdrop-blur-md text-white text-xs font-bold border border-white/10 transition-colors"
        >
          ← Danh sách phim
        </router-link>
      </div>

      <!-- Movie Header Info Container -->
      <div class="absolute bottom-8 inset-x-0 max-w-7xl mx-auto px-6 md:px-12 flex flex-col md:flex-row md:items-end gap-6">
        <div class="flex-1 space-y-3">
          <!-- Rating & Badges -->
          <div class="flex flex-wrap items-center gap-3">
            <span class="px-2.5 py-1 rounded-md bg-amber-500/20 border border-amber-500/40 text-amber-300 text-xs font-bold flex items-center gap-1.5 shadow-sm">
              ★ IMDb {{ movie?.rating || 8.6 }}
            </span>
            <span class="px-2.5 py-1 rounded-md bg-white/10 text-slate-300 text-xs font-semibold backdrop-blur-md">
              {{ movie?.duration || 166 }} phút
            </span>
            <span 
              v-for="g in movie?.genre" 
              :key="g"
              class="px-2.5 py-1 rounded-md bg-cinema-surface/80 border border-white/10 text-slate-300 text-xs"
            >
              {{ g }}
            </span>
          </div>

          <!-- Movie Title -->
          <h1 class="text-4xl md:text-5xl lg:text-6xl font-black tracking-tight text-white drop-shadow-lg">
            {{ movie?.title || 'Dune: Part Two' }}
          </h1>

          <!-- Tagline / Info -->
          <p class="text-cinema-muted text-xs md:text-sm max-w-3xl line-clamp-3">
            {{ movie?.description }}
          </p>

          <!-- Trailer CTA Button -->
          <div class="pt-2 flex items-center gap-4">
            <button 
              @click="isTrailerOpen = true"
              class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-cinema-accent hover:bg-rose-600 text-white font-semibold text-xs transition-all duration-300 shadow-glow-accent hover:scale-[1.02] cursor-pointer"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
              Xem Trailer Trực Tiếp
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content: Date & Showtime Picker -->
    <div class="max-w-7xl mx-auto px-6 md:px-12 mt-8 space-y-8">
      
      <!-- Date Picker Horizontal Slider -->
      <section class="space-y-3">
        <h2 class="text-xs font-bold uppercase tracking-widest text-cinema-muted">
          1. Chọn ngày chiếu
        </h2>
        <div class="flex items-center gap-3 overflow-x-auto pb-2 scrollbar-none">
          <button 
            v-for="d in dates" 
            :key="d.value"
            @click="store.selectDate(d.value)"
            class="flex flex-col items-center min-w-[76px] py-3 px-4 rounded-2xl border transition-all duration-200 cursor-pointer"
            :class="[
              store.selectedDate === d.value
                ? 'bg-cinema-accent border-cinema-accent text-white shadow-glow-accent scale-105'
                : 'bg-cinema-surface/70 hover:bg-cinema-surface border-cinema-border text-slate-300 hover:border-slate-500'
            ]"
          >
            <span class="text-[11px] font-medium opacity-80">{{ d.dayName }}</span>
            <span class="text-xl font-bold mt-0.5">{{ d.dayNum }}</span>
            <span class="text-[10px] uppercase font-semibold opacity-70">{{ d.month }}</span>
          </button>
        </div>
      </section>

      <!-- Cinemas and Showtimes List -->
      <section class="space-y-6">
        <h2 class="text-xs font-bold uppercase tracking-widest text-cinema-muted">
          2. Lịch chiếu theo cụm rạp
        </h2>

        <!-- Cinema 1: Landmark 81 -->
        <div class="bg-cinema-surface/50 border border-cinema-border rounded-3xl p-6 backdrop-blur-md space-y-4">
          <div class="flex flex-wrap items-center justify-between gap-2 border-b border-white/5 pb-4">
            <div>
              <h3 class="text-base font-bold text-white flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-cinema-gold"></span>
                CineReserve IMAX - Landmark 81
              </h3>
              <p class="text-xs text-cinema-muted mt-0.5">Tầng B1, Vincom Center Landmark 81, Bình Thạnh, TP.HCM</p>
            </div>
            <span class="text-xs px-3 py-1 rounded-full bg-amber-500/10 text-amber-400 border border-amber-500/20 font-bold">
              IMAX Laser
            </span>
          </div>

          <!-- Showtime Slots -->
          <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3">
            <button 
              v-for="st in showtimes" 
              :key="st.id"
              @click="handleSelectShowtime(st)"
              class="group relative flex flex-col items-center justify-center p-3.5 rounded-2xl border transition-all duration-300 cursor-pointer"
              :class="[
                store.selectedShowtime?.id === st.id
                  ? 'bg-cinema-accent/20 border-cinema-accent text-white shadow-glow-accent'
                  : 'bg-cinema-card/60 hover:bg-cinema-card border-cinema-border hover:border-slate-500 text-slate-200'
              ]"
            >
              <span class="text-lg font-extrabold group-hover:text-amber-400 transition-colors">
                {{ st.start_time }}
              </span>
              <span class="text-[11px] text-cinema-muted mt-0.5">
                {{ st.room?.room_type || 'IMAX Laser' }}
              </span>
              <span class="mt-1 text-[10px] font-semibold text-emerald-400 px-2 py-0.5 rounded-full bg-emerald-500/10">
                ${{ st.base_price }} / vé
              </span>
            </button>
          </div>
        </div>

        <!-- Cinema 2: Moonlight Thu Duc -->
        <div class="bg-cinema-surface/50 border border-cinema-border rounded-3xl p-6 backdrop-blur-md space-y-4">
          <div class="flex flex-wrap items-center justify-between gap-2 border-b border-white/5 pb-4">
            <div>
              <h3 class="text-base font-bold text-white flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-cinema-accent"></span>
                CineReserve Moonlight - Thủ Đức
              </h3>
              <p class="text-xs text-cinema-muted mt-0.5">102 Đặng Văn Bi, P. Trường Thọ, TP. Thủ Đức, TP.HCM</p>
            </div>
            <span class="text-xs px-3 py-1 rounded-full bg-white/5 text-slate-300 border border-white/10 font-bold">
              Dolby Atmos
            </span>
          </div>

          <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3">
            <button 
              v-for="st in showtimes.slice(0, 2)" 
              :key="'thu-duc-' + st.id"
              @click="handleSelectShowtime(st)"
              class="flex flex-col items-center justify-center p-3.5 rounded-2xl bg-cinema-card/60 hover:bg-cinema-card border border-cinema-border hover:border-slate-500 text-slate-200 transition-all cursor-pointer"
            >
              <span class="text-lg font-extrabold">{{ st.start_time }}</span>
              <span class="text-[11px] text-cinema-muted mt-0.5">Dolby Atmos</span>
              <span class="mt-1 text-[10px] font-semibold text-emerald-400 px-2 py-0.5 rounded-full bg-emerald-500/10">${{ st.base_price }} / vé</span>
            </button>
          </div>
        </div>

      </section>

    </div>

    <!-- Trailer Modal -->
    <TrailerModal 
      :is-open="isTrailerOpen" 
      :title="movie?.title || ''"
      :video-url="movie?.trailer_url || ''"
      @close="isTrailerOpen = false"
    />

  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useBookingStore } from '../stores/bookingStore';
import Navbar from '../components/Navbar.vue';
import TrailerModal from '../components/TrailerModal.vue';
import type { Showtime } from '../types';

const route = useRoute();
const router = useRouter();
const store = useBookingStore();

const isTrailerOpen = ref(false);

onMounted(async () => {
  if (store.movies.length === 0) {
    await store.fetchMovies();
  }
  
  const slug = route.params.slug as string;
  if (slug) {
    const found = store.movies.find(m => m.slug === slug);
    if (found) {
      store.selectMovie(found);
    }
  }
});

const movie = computed(() => store.currentMovie);
const showtimes = computed(() => {
  if (store.currentMovie?.showtimes && store.currentMovie.showtimes.length > 0) {
    return store.currentMovie.showtimes;
  }
  // Fallback showtimes
  return [
    {
      id: 1,
      movie_id: store.currentMovie?.id || 1,
      room_id: 1,
      cinema_id: 1,
      start_time: '13:30',
      end_time: '16:00',
      base_price: 11.0,
      room: { id: 1, cinema_id: 1, name: 'Hall 1 (IMAX Laser)', room_type: 'IMAX Laser', total_seats: 118, rows: [] }
    },
    {
      id: 2,
      movie_id: store.currentMovie?.id || 1,
      room_id: 1,
      cinema_id: 1,
      start_time: '16:45',
      end_time: '19:15',
      base_price: 12.5,
      room: { id: 1, cinema_id: 1, name: 'Hall 1 (IMAX Laser)', room_type: 'IMAX Laser', total_seats: 118, rows: [] }
    },
    {
      id: 3,
      movie_id: store.currentMovie?.id || 1,
      room_id: 1,
      cinema_id: 1,
      start_time: '19:30',
      end_time: '22:00',
      base_price: 14.5,
      room: { id: 1, cinema_id: 1, name: 'Hall 1 (IMAX Laser)', room_type: 'IMAX Laser', total_seats: 118, rows: [] }
    },
    {
      id: 4,
      movie_id: store.currentMovie?.id || 1,
      room_id: 1,
      cinema_id: 1,
      start_time: '22:15',
      end_time: '00:45',
      base_price: 11.5,
      room: { id: 1, cinema_id: 1, name: 'Hall 1 (IMAX Laser)', room_type: 'IMAX Laser', total_seats: 118, rows: [] }
    }
  ];
});

const dates = [
  { value: '2026-08-31', dayName: 'Hôm nay', dayNum: '31', month: 'Th08' },
  { value: '2026-09-01', dayName: 'Thứ 3', dayNum: '01', month: 'Th09' },
  { value: '2026-09-02', dayName: 'Thứ 4', dayNum: '02', month: 'Th09' },
  { value: '2026-09-03', dayName: 'Thứ 5', dayNum: '03', month: 'Th09' },
  { value: '2026-09-04', dayName: 'Thứ 6', dayNum: '04', month: 'Th09' },
];

const handleSelectShowtime = async (st: Showtime) => {
  await store.selectShowtime(st);
  router.push({ name: 'seat-selection', params: { showtimeId: st.id } });
};
</script>
