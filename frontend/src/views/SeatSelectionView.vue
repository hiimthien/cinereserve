<template>
  <div class="min-h-screen bg-cinema-bg flex flex-col justify-between pb-28 select-none">
    
    <!-- Top Bar: Movie Title, Room, Showtime & Back Button -->
    <header class="border-b border-cinema-border bg-cinema-surface/80 backdrop-blur-md sticky top-0 z-30 px-6 py-4">
      <div class="max-w-7xl mx-auto flex items-center justify-between">
        <button 
          @click="router.push({ name: 'movie-detail' })"
          class="flex items-center gap-2 text-sm text-cinema-muted hover:text-white transition-colors"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
          <span>Chọn lại phim</span>
        </button>

        <div class="text-center">
          <h1 class="text-base md:text-lg font-extrabold text-white">
            {{ store.currentMovie?.title || 'Dune: Part Two' }}
          </h1>
          <p class="text-xs text-cinema-muted">
            {{ store.selectedShowtime?.room?.name || 'Hall 1 (IMAX)' }} • {{ store.selectedShowtime?.start_time || '18:30' }} • {{ store.selectedDate }}
          </p>
        </div>

        <!-- Live Countdown Timer Component -->
        <CountdownTimer />
      </div>
    </header>

    <!-- Main Content: Screen Curve + Seat Grid Matrix -->
    <main class="max-w-6xl w-full mx-auto px-4 md:px-8 pt-8 flex-1 flex flex-col items-center justify-center">
      
      <!-- Cinema Screen Projection Light -->
      <ScreenLight />

      <!-- Seat Map Grid -->
      <div class="w-full overflow-x-auto pb-4 flex justify-center">
        <div class="min-w-[640px] max-w-4xl p-6 bg-cinema-surface/40 rounded-3xl border border-cinema-border backdrop-blur-md space-y-3">
          
          <div 
            v-for="row in seatRows" 
            :key="row" 
            class="flex items-center justify-center gap-2 md:gap-3"
          >
            <!-- Row Label Left -->
            <span class="w-5 text-center text-xs font-bold text-cinema-muted">{{ row }}</span>

            <!-- Seats in Row -->
            <div class="flex items-center gap-1.5 md:gap-2">
              <button
                v-for="seat in getSeatsByRow(row)"
                :key="seat.id"
                :disabled="seat.status === 'booked' || (seat.status === 'holding' && seat.held_by !== store.sessionId)"
                @click="store.toggleSeat(seat)"
                class="relative rounded-t-lg rounded-b transition-all duration-200 flex items-center justify-center font-bold text-[10px]"
                :class="[
                  // Dimensions
                  seat.type === 'couple' ? 'w-16 md:w-20 h-8 md:h-9' : 'w-7 md:w-9 h-7 md:h-8',
                  
                  // Status Colors
                  seat.status === 'selected' 
                    ? 'bg-seat-selected text-white shadow-glow-green scale-105 z-10' 
                    : seat.status === 'holding' && seat.held_by !== store.sessionId
                    ? 'bg-seat-holding text-slate-900 cursor-not-allowed opacity-80 animate-holding'
                    : seat.status === 'booked'
                    ? 'bg-seat-booked border border-seat-booked-border text-red-700/50 cursor-not-allowed'
                    : seat.type === 'vip'
                    ? 'bg-seat-available border-2 border-amber-500/80 hover:bg-seat-available-hover text-slate-300 hover:scale-105'
                    : 'bg-seat-available hover:bg-seat-available-hover text-slate-400 border border-slate-700 hover:scale-105'
                ]"
              >
                <!-- Seat Number text or Lock Icon -->
                <template v-if="seat.status === 'holding' && seat.held_by !== store.sessionId">
                  <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 fill-current text-amber-950" viewBox="0 0 24 24"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/></svg>
                </template>
                <template v-else-if="seat.status === 'booked'">
                  <span class="opacity-40">✕</span>
                </template>
                <template v-else>
                  <span>{{ seat.number }}</span>
                </template>
              </button>
            </div>

            <!-- Row Label Right -->
            <span class="w-5 text-center text-xs font-bold text-cinema-muted">{{ row }}</span>
          </div>

        </div>
      </div>

      <!-- Legend -->
      <SeatLegend />

    </main>

    <!-- Sticky Bottom Bar: Summary & Checkout CTA -->
    <footer class="fixed bottom-0 inset-x-0 bg-cinema-surface/90 border-t border-cinema-border backdrop-blur-xl z-40 px-6 py-4 shadow-2xl">
      <div class="max-w-6xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4">
        
        <!-- Left: Selected Seats Summary -->
        <div class="flex items-center gap-4">
          <div class="text-left">
            <span class="text-xs text-cinema-muted block">Ghế đã chọn:</span>
            <div class="flex items-center gap-1.5 mt-0.5 min-h-[24px]">
              <template v-if="store.selectedSeats.length > 0">
                <span 
                  v-for="s in store.selectedSeats" 
                  :key="s.id"
                  class="px-2 py-0.5 rounded-md bg-cinema-accent/20 border border-cinema-accent text-white text-xs font-bold"
                >
                  {{ s.row }}{{ s.number }}
                </span>
              </template>
              <span v-else class="text-xs text-slate-500 italic">Chưa chọn ghế nào</span>
            </div>
          </div>

          <div class="h-8 w-px bg-white/10 hidden sm:block"></div>

          <!-- Total Price -->
          <div>
            <span class="text-xs text-cinema-muted block">Tổng thanh toán:</span>
            <span class="text-2xl font-black text-amber-400">
              ${{ store.totalPrice }}.00
            </span>
          </div>
        </div>

        <!-- Right: Action Button -->
        <button 
          :disabled="store.selectedSeats.length === 0"
          @click="router.push({ name: 'checkout' })"
          class="w-full sm:w-auto px-8 py-3.5 rounded-2xl font-bold text-sm tracking-wide transition-all duration-300 flex items-center justify-center gap-2"
          :class="[
            store.selectedSeats.length > 0
              ? 'bg-cinema-accent hover:bg-rose-600 text-white shadow-glow-accent cursor-pointer hover:scale-[1.02]'
              : 'bg-slate-800 text-slate-500 cursor-not-allowed border border-white/5'
          ]"
        >
          <span>Tiến hành thanh toán</span>
          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
          </svg>
        </button>

      </div>
    </footer>

  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useRouter } from 'vue-router';
import { useBookingStore } from '../stores/bookingStore';
import ScreenLight from '../components/ScreenLight.vue';
import SeatLegend from '../components/SeatLegend.vue';
import CountdownTimer from '../components/CountdownTimer.vue';

const router = useRouter();
const store = useBookingStore();

const seatRows = computed(() => {
  const rowsSet = new Set(store.seats.map(s => s.row));
  return Array.from(rowsSet);
});

const getSeatsByRow = (row: string) => {
  return store.seats.filter(s => s.row === row);
};
</script>
