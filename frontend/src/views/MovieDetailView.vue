<template>
  <div class="min-h-screen bg-cinema-bg pb-24">
    <!-- Hero Section with Trailer / Backdrop -->
    <div class="relative w-full h-[460px] md:h-[540px] overflow-hidden">
      <!-- Backdrop Image with Gradient Overlay -->
      <img 
        :src="movie?.backdrop_url || 'https://images.unsplash.com/photo-1509198397868-475647b2a1e5?w=1600&auto=format&fit=crop&q=80'" 
        alt="Backdrop" 
        class="w-full h-full object-cover object-center filter brightness-[0.6] transform scale-105 transition-transform duration-1000"
      />
      <div class="absolute inset-0 bg-gradient-to-t from-cinema-bg via-cinema-bg/60 to-transparent"></div>
      <div class="absolute inset-0 bg-gradient-to-r from-cinema-bg via-cinema-bg/40 to-transparent"></div>

      <!-- Movie Header Info Container -->
      <div class="absolute bottom-10 inset-x-0 max-w-7xl mx-auto px-6 md:px-12 flex flex-col md:flex-row md:items-end gap-6">
        <div class="flex-1 space-y-3">
          <!-- Rating & Badges -->
          <div class="flex flex-wrap items-center gap-3">
            <span class="px-2.5 py-1 rounded-md bg-amber-500/20 border border-amber-500/40 text-amber-300 text-xs font-bold flex items-center gap-1.5 shadow-sm">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 fill-current text-amber-400" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
              IMDb {{ movie?.rating || 8.6 }}
            </span>
            <span class="px-2.5 py-1 rounded-md bg-white/10 text-slate-300 text-xs font-semibold backdrop-blur-md">
              {{ movie?.duration || 166 }} mins
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
          <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold tracking-tight text-white drop-shadow-lg">
            {{ movie?.title || 'Dune: Part Two' }}
          </h1>

          <!-- Tagline / Info -->
          <p class="text-cinema-muted text-sm md:text-base max-w-2xl line-clamp-2">
            {{ movie?.description || 'Paul Atreides unites with Chani and the Fremen while seeking revenge against the conspirators who destroyed his family.' }}
          </p>

          <!-- Trailer CTA Button -->
          <div class="pt-2 flex items-center gap-4">
            <a 
              :href="movie?.trailer_url" 
              target="_blank"
              class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-cinema-accent hover:bg-rose-600 text-white font-semibold text-sm transition-all duration-300 shadow-glow-accent hover:scale-[1.02]"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
              Xem Trailer
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content: Date & Showtime Picker -->
    <div class="max-w-7xl mx-auto px-6 md:px-12 mt-6 space-y-8">
      
      <!-- Date Picker Horizontal Slider -->
      <section class="space-y-3">
        <h2 class="text-xs font-bold uppercase tracking-widest text-cinema-muted">
          Chọn ngày chiếu
        </h2>
        <div class="flex items-center gap-3 overflow-x-auto pb-2 scrollbar-none">
          <button 
            v-for="d in dates" 
            :key="d.value"
            @click="store.selectDate(d.value)"
            class="flex flex-col items-center min-w-[76px] py-3 px-4 rounded-2xl border transition-all duration-200"
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
          Lịch chiếu tại Rạp
        </h2>

        <!-- Cinema Card 1 -->
        <div class="bg-cinema-surface/50 border border-cinema-border rounded-3xl p-6 backdrop-blur-md space-y-4">
          <div class="flex flex-wrap items-center justify-between gap-2 border-b border-white/5 pb-4">
            <div>
              <h3 class="text-lg font-bold text-white flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-cinema-gold"></span>
                CineReserve IMAX - Landmark 81
              </h3>
              <p class="text-xs text-cinema-muted mt-0.5">Tầng B1, Vincom Landmark 81, P. 22, Bình Thạnh, TP.HCM</p>
            </div>
            <span class="text-xs px-3 py-1 rounded-full bg-white/5 text-slate-400 border border-white/10">
              Laser Projection
            </span>
          </div>

          <!-- Showtime Slots -->
          <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
            <button 
              v-for="st in showtimes" 
              :key="st.id"
              @click="handleSelectShowtime(st)"
              class="group relative flex flex-col items-center justify-center p-3 rounded-2xl border transition-all duration-300"
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
                {{ st.room?.room_type || 'IMAX' }}
              </span>
              <span class="mt-1 text-[10px] font-semibold text-emerald-400 px-2 py-0.5 rounded-full bg-emerald-500/10">
                ${{ st.base_price }} / vé
              </span>
            </button>
          </div>
        </div>

        <!-- Cinema Card 2 -->
        <div class="bg-cinema-surface/50 border border-cinema-border rounded-3xl p-6 backdrop-blur-md space-y-4">
          <div class="flex flex-wrap items-center justify-between gap-2 border-b border-white/5 pb-4">
            <div>
              <h3 class="text-lg font-bold text-white flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-cinema-accent"></span>
                CineReserve Thủ Đức - Moonlight
              </h3>
              <p class="text-xs text-cinema-muted mt-0.5">Đặng Văn Bi, P. Trường Thọ, TP. Thủ Đức, TP.HCM</p>
            </div>
            <span class="text-xs px-3 py-1 rounded-full bg-white/5 text-slate-400 border border-white/10">
              Dolby Atmos
            </span>
          </div>

          <!-- Showtime Slots -->
          <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
            <button 
              @click="handleSelectShowtime(showtimes[0])"
              class="flex flex-col items-center justify-center p-3 rounded-2xl bg-cinema-card/60 hover:bg-cinema-card border border-cinema-border hover:border-slate-500 text-slate-200 transition-all"
            >
              <span class="text-lg font-extrabold">19:15</span>
              <span class="text-[11px] text-cinema-muted mt-0.5">2D Digital</span>
              <span class="mt-1 text-[10px] font-semibold text-emerald-400 px-2 py-0.5 rounded-full bg-emerald-500/10">$10 / vé</span>
            </button>

            <button 
              @click="handleSelectShowtime(showtimes[1])"
              class="flex flex-col items-center justify-center p-3 rounded-2xl bg-cinema-card/60 hover:bg-cinema-card border border-cinema-border hover:border-slate-500 text-slate-200 transition-all"
            >
              <span class="text-lg font-extrabold">21:00</span>
              <span class="text-[11px] text-cinema-muted mt-0.5">Dolby Atmos</span>
              <span class="mt-1 text-[10px] font-semibold text-emerald-400 px-2 py-0.5 rounded-full bg-emerald-500/10">$12 / vé</span>
            </button>
          </div>
        </div>

      </section>

    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useBookingStore } from '../stores/bookingStore';
import type { Showtime } from '../types';

const router = useRouter();
const store = useBookingStore();

onMounted(async () => {
  await store.fetchMovies();
});

const movie = computed(() => store.currentMovie);
const showtimes = computed(() => store.currentMovie?.showtimes || []);

// Generate 5 days from today
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
