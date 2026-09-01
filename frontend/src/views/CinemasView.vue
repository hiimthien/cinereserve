<template>
  <div class="min-h-screen bg-cinema-bg flex flex-col justify-between">
    <div>
      <Navbar />

      <div class="max-w-7xl mx-auto py-10 px-4 md:px-8 space-y-8">
        
        <!-- Page Header -->
        <div class="space-y-2">
          <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-cinema-accent">
            <span class="w-2 h-2 rounded-full bg-cinema-accent animate-ping"></span>
            Hệ Thống Rạp Toàn Quốc
          </div>
          <h1 class="text-3xl md:text-4xl font-black text-white tracking-tight">
            Lịch Chiếu Phim Theo Cụm Rạp
          </h1>
          <p class="text-sm text-cinema-muted">
            Tìm kiếm theo thành phố, chuỗi rạp yêu thích (CGV, Lotte, Galaxy, BHD, Cinestar, Beta) và khám phá các phim đang chiếu hôm nay.
          </p>
        </div>

        <!-- Filter Bar (City + Cinema Chain + Search) -->
        <BaseCard padding="md">
          <!-- City Pills -->
          <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none">
            <span class="text-xs font-bold text-cinema-muted whitespace-nowrap mr-2 flex items-center gap-1">
              <MapPin class="w-3.5 h-3.5 text-cinema-accent" />
              <span>Tỉnh / Thành:</span>
            </span>
            <button 
              v-for="city in cities" 
              :key="city"
              @click="selectedCity = city"
              class="px-4 py-1.5 rounded-full text-xs font-bold transition-all whitespace-nowrap cursor-pointer"
              :class="[
                selectedCity === city
                  ? 'bg-cinema-accent text-white shadow-glow-accent'
                  : 'bg-cinema-card/60 text-slate-300 hover:bg-slate-800 border border-white/5'
              ]"
            >
              {{ city }}
            </button>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-center pt-3 mt-2 border-t border-white/5">
            <!-- Chain Buttons -->
            <div class="md:col-span-8 flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none">
              <span class="text-xs font-bold text-cinema-muted whitespace-nowrap mr-2 flex items-center gap-1">
                <Building2 class="w-3.5 h-3.5 text-cinema-gold" />
                <span>Chuỗi rạp:</span>
              </span>
              <button 
                v-for="chain in chains" 
                :key="chain"
                @click="selectedChain = chain"
                class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all whitespace-nowrap cursor-pointer"
                :class="[
                  selectedChain === chain
                    ? 'bg-cinema-gold text-slate-950 font-black shadow-glow-gold'
                    : 'bg-cinema-card/60 text-slate-300 hover:bg-slate-800 border border-white/5'
                ]"
              >
                {{ chain }}
              </button>
            </div>

            <!-- Search Input -->
            <div class="md:col-span-4">
              <BaseInput 
                v-model="searchQuery" 
                placeholder="Tìm tên rạp, đường, quận..."
              >
                <template #prefix>
                  <Search class="w-4 h-4 text-slate-400" />
                </template>
              </BaseInput>
            </div>
          </div>
        </BaseCard>

        <!-- Main Content: Left List of Cinemas (4 cols), Right Movie Schedule (8 cols) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
          
          <!-- Left: Cinema List (4 cols) -->
          <div class="lg:col-span-4 space-y-3">
            <div class="flex items-center justify-between px-1">
              <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">
                Danh sách rạp ({{ filteredCinemas.length }})
              </span>
            </div>

            <!-- Loading Shimmer Skeletons -->
            <div v-if="isLoadingCinemas" class="space-y-3">
              <div v-for="i in 4" :key="i" class="p-4 rounded-2xl bg-cinema-surface/50 border border-cinema-border space-y-2">
                <BaseSkeleton class="w-3/4 h-5" rounded="md" />
                <BaseSkeleton class="w-full h-3" rounded="md" />
                <div class="flex gap-2 pt-1">
                  <BaseSkeleton class="w-16 h-4" rounded="sm" />
                  <BaseSkeleton class="w-16 h-4" rounded="sm" />
                </div>
              </div>
            </div>

            <div v-else-if="filteredCinemas.length === 0" class="p-8 text-center bg-cinema-surface/40 border border-cinema-border rounded-2xl text-cinema-muted text-xs">
              Không tìm thấy rạp nào phù hợp với bộ lọc.
            </div>

            <div v-else class="space-y-3 max-h-[750px] overflow-y-auto pr-1 scrollbar-thin">
              <div 
                v-for="cinema in filteredCinemas" 
                :key="cinema.id"
                @click="selectCinema(cinema)"
                class="p-4 rounded-2xl border transition-all cursor-pointer space-y-2.5"
                :class="[
                  selectedCinema?.id === cinema.id 
                    ? 'bg-cinema-surface border-cinema-accent shadow-glow-accent' 
                    : 'bg-cinema-surface/50 border-cinema-border hover:border-white/20 hover:bg-cinema-surface/80'
                ]"
              >
                <div class="flex items-start justify-between gap-2">
                  <h3 class="font-extrabold text-sm text-white leading-snug">{{ cinema.name }}</h3>
                  <BaseBadge variant="gold" size="xs">
                    {{ cinema.city }}
                  </BaseBadge>
                </div>

                <p class="text-xs text-cinema-muted line-clamp-2">{{ cinema.address }}</p>

                <!-- Tags for Rooms / Formats -->
                <div class="flex items-center gap-1.5 flex-wrap pt-1">
                  <span 
                    v-for="room in cinema.rooms?.slice(0, 3)" 
                    :key="room.id"
                    class="text-[10px] px-2 py-0.5 rounded bg-cinema-card text-slate-300 border border-white/5 font-mono"
                  >
                    {{ room.room_type || room.name || '2D' }}
                  </span>
                  <span v-if="cinema.rooms && cinema.rooms.length > 3" class="text-[10px] text-cinema-muted">
                    +{{ cinema.rooms.length - 3 }} phòng
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- Right: Selected Cinema Details & Movies Schedule (8 cols) -->
          <div class="lg:col-span-8 space-y-6">
            
            <div v-if="selectedCinema" class="space-y-6">
              
              <!-- Selected Cinema Banner Card -->
              <div class="bg-gradient-to-r from-cinema-surface to-cinema-card border border-cinema-border rounded-3xl p-6 backdrop-blur-xl relative overflow-hidden">
                <div class="absolute right-0 top-0 bottom-0 w-1/3 bg-gradient-to-l from-cinema-accent/10 to-transparent pointer-events-none"></div>
                
                <div class="relative z-10 space-y-2">
                  <div class="flex items-center gap-2">
                    <BaseBadge variant="accent" size="sm">
                      {{ selectedCinema.city }}
                    </BaseBadge>
                    <span class="text-xs text-cinema-muted">
                      {{ selectedCinema.rooms?.length || 3 }} Phòng chiếu hiện đại
                    </span>
                  </div>
                  <h2 class="text-2xl font-black text-white">{{ selectedCinema.name }}</h2>
                  <p class="text-xs text-slate-300 flex items-center gap-1.5">
                    <MapPin class="w-4 h-4 text-cinema-gold shrink-0" />
                    <span>{{ selectedCinema.address }}</span>
                  </p>
                </div>
              </div>

              <!-- 7-Day Date Slider Component -->
              <DateSlider 
                v-model="selectedDate" 
                label="Chọn ngày xem chiếu:" 
                @update:model-value="onDateChange"
              />

              <!-- Movie Schedule List -->
              <div class="space-y-4">
                <div class="flex items-center justify-between">
                  <h3 class="text-base font-bold text-white flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                    Phim đang chiếu ngày {{ formatDateString(selectedDate) }}
                  </h3>
                  <span class="text-xs text-cinema-muted">{{ cinemaMovies.length }} Phim có suất</span>
                </div>

                <!-- Loading Skeletons -->
                <div v-if="isLoadingSchedule" class="space-y-4">
                  <div v-for="i in 3" :key="i" class="p-5 rounded-3xl bg-cinema-surface/50 border border-cinema-border space-y-4">
                    <div class="flex gap-4">
                      <BaseSkeleton class="w-20 h-28 shrink-0" rounded="2xl" />
                      <div class="space-y-2 flex-1">
                        <BaseSkeleton class="w-32 h-5" rounded="md" />
                        <BaseSkeleton class="w-48 h-6" rounded="lg" />
                        <BaseSkeleton class="w-full h-4" rounded="md" />
                      </div>
                    </div>
                    <div class="flex gap-3 pt-2">
                      <BaseSkeleton v-for="j in 4" :key="j" class="w-24 h-12" rounded="xl" />
                    </div>
                  </div>
                </div>

                <!-- Empty Schedule -->
                <div v-else-if="cinemaMovies.length === 0" class="p-12 text-center bg-cinema-surface/40 border border-cinema-border rounded-3xl space-y-3">
                  <Clapperboard class="w-10 h-10 text-slate-500 mx-auto" />
                  <h4 class="text-base font-bold text-white">Chưa có lịch chiếu trong ngày này</h4>
                  <p class="text-xs text-cinema-muted">Vui lòng chọn ngày khác hoặc rạp khác để xem lịch chiếu.</p>
                </div>

                <!-- Movies List with Showtimes -->
                <div v-else class="space-y-4">
                  <BaseCard 
                    v-for="item in cinemaMovies" 
                    :key="item.movie.id"
                    hoverable
                  >
                    <div class="flex items-start gap-4">
                      <!-- Movie Poster -->
                      <img 
                        :src="item.movie.poster_url" 
                        :alt="item.movie.title" 
                        class="w-20 h-28 object-cover rounded-2xl border border-white/10 shrink-0 shadow-lg"
                      />

                      <!-- Movie Info -->
                      <div class="space-y-1.5 flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                          <BaseBadge variant="gold" size="xs">
                            <Star class="w-3 h-3 fill-amber-400 text-amber-400" />
                            <span>{{ Number(item.movie.rating || 8.5).toFixed(1) }}</span>
                          </BaseBadge>
                          <span class="text-xs text-cinema-muted">{{ item.movie.duration || 120 }} phút</span>
                          <BaseBadge 
                            v-for="g in item.movie.genre?.slice(0, 2)" 
                            :key="g"
                            variant="neutral"
                            size="xs"
                          >
                            {{ g }}
                          </BaseBadge>
                        </div>

                        <h4 class="text-lg font-black text-white leading-tight truncate">
                          {{ item.movie.title }}
                        </h4>
                        <p class="text-xs text-cinema-muted line-clamp-1">
                          {{ item.movie.director ? `Đạo diễn: ${item.movie.director}` : item.movie.description }}
                        </p>
                      </div>
                    </div>

                    <!-- Showtimes By Room -->
                    <div class="border-t border-white/5 pt-3 mt-3 space-y-3">
                      <div class="flex items-center gap-2 flex-wrap">
                        <button 
                          v-for="st in item.showtimes" 
                          :key="st.id"
                          @click="goToSeats(item.movie, st)"
                          class="group flex flex-col items-center justify-center px-4 py-2 rounded-xl bg-cinema-card/80 border border-white/10 hover:border-cinema-accent hover:bg-cinema-accent/10 transition-all cursor-pointer text-center"
                        >
                          <span class="text-sm font-black text-white group-hover:text-cinema-accent transition-colors">
                            {{ st.start_time }}
                          </span>
                          <span class="text-[10px] text-cinema-muted group-hover:text-slate-300 font-mono">
                            {{ st.room?.room_type || st.room?.name || 'Hall 1' }}
                          </span>
                        </button>
                      </div>
                    </div>
                  </BaseCard>
                </div>

              </div>

            </div>

          </div>

        </div>

      </div>
    </div>

    <Footer />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { 
  MapPin, 
  Building2, 
  Search, 
  Clapperboard, 
  Star 
} from 'lucide-vue-next';
import api from '../services/api';
import { useBookingStore } from '../stores/bookingStore';
import Navbar from '../components/Navbar.vue';
import DateSlider from '../components/DateSlider.vue';
import Footer from '../components/Footer.vue';
import BaseCard from '../components/base/BaseCard.vue';
import BaseBadge from '../components/base/BaseBadge.vue';
import BaseInput from '../components/base/BaseInput.vue';
import BaseSkeleton from '../components/base/BaseSkeleton.vue';

const router = useRouter();
const store = useBookingStore();

const cities = ['Tất cả', 'TP. Hồ Chí Minh', 'Hà Nội', 'Đà Nẵng', 'Hải Phòng', 'Cần Thơ', 'Lâm Đồng'];
const chains = ['Tất cả', 'CGV', 'Lotte', 'Galaxy', 'BHD', 'Cinestar', 'Beta'];

const selectedCity = ref('Tất cả');
const selectedChain = ref('Tất cả');
const searchQuery = ref('');

const cinemas = ref<any[]>([]);
const selectedCinema = ref<any>(null);
const cinemaMovies = ref<any[]>([]);

const isLoadingCinemas = ref(false);
const isLoadingSchedule = ref(false);

const selectedDate = ref(new Date().toISOString().split('T')[0]);

const filteredCinemas = computed(() => {
  return cinemas.value.filter(c => {
    const matchCity = selectedCity.value === 'Tất cả' || c.city?.includes(selectedCity.value);
    const matchChain = selectedChain.value === 'Tất cả' || c.name?.toUpperCase().includes(selectedChain.value.toUpperCase());
    const matchSearch = !searchQuery.value || 
      c.name?.toLowerCase().includes(searchQuery.value.toLowerCase()) || 
      c.address?.toLowerCase().includes(searchQuery.value.toLowerCase());
    return matchCity && matchChain && matchSearch;
  });
});

const fetchCinemas = async () => {
  isLoadingCinemas.value = true;
  try {
    const res = await api.get('/cinemas');
    cinemas.value = res.data.data || res.data || [];
    if (cinemas.value.length > 0) {
      selectCinema(cinemas.value[0]);
    }
  } catch (err) {
    console.error('Failed to fetch cinemas', err);
  } finally {
    isLoadingCinemas.value = false;
  }
};

const selectCinema = (cinema: any) => {
  selectedCinema.value = cinema;
  fetchCinemaShowtimes(cinema.id, selectedDate.value);
};

const onDateChange = (date: string) => {
  if (selectedCinema.value) {
    fetchCinemaShowtimes(selectedCinema.value.id, date);
  }
};

const fetchCinemaShowtimes = async (cinemaId: number, date: string) => {
  isLoadingSchedule.value = true;
  try {
    const res = await api.get(`/cinemas/${cinemaId}/showtimes`, {
      params: { date }
    });
    cinemaMovies.value = res.data.movies || [];
  } catch (err) {
    console.error('Failed to load cinema showtimes', err);
    cinemaMovies.value = [];
  } finally {
    isLoadingSchedule.value = false;
  }
};

const formatDateString = (dateStr: string) => {
  if (!dateStr) return '';
  const parts = dateStr.split('-');
  if (parts.length === 3) {
    return `${parts[2]}/${parts[1]}/${parts[0]}`;
  }
  return dateStr;
};

const goToSeats = (movie: any, showtime: any) => {
  store.selectMovie(movie);
  store.selectShowtime(showtime);
  store.selectedDate = selectedDate.value;
  router.push({
    name: 'seat-selection',
    params: { 
      slug: movie.slug || 'phim',
      showtimeId: showtime.id 
    }
  });
};

onMounted(() => {
  fetchCinemas();
});
</script>
