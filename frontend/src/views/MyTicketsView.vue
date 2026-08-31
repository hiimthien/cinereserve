<template>
  <div class="min-h-screen bg-cinema-bg pb-24">
    <Navbar />

    <main class="max-w-5xl mx-auto px-6 py-10 space-y-8">
      <div class="flex items-center justify-between border-b border-white/10 pb-6">
        <div>
          <h1 class="text-2xl font-black text-white">Vé của tôi (My Tickets)</h1>
          <p class="text-xs text-cinema-muted mt-1">Danh sách vé xem phim đã đặt và mã QR check-in tại rạp</p>
        </div>

        <router-link 
          to="/" 
          class="px-4 py-2 rounded-xl bg-cinema-surface hover:bg-cinema-card border border-cinema-border text-xs text-white transition-colors"
        >
          ← Đặt vé mới
        </router-link>
      </div>

      <!-- If no tickets -->
      <div v-if="tickets.length === 0" class="p-12 text-center bg-cinema-surface/40 border border-cinema-border rounded-3xl space-y-4">
        <div class="w-16 h-16 rounded-full bg-white/5 flex items-center justify-center mx-auto text-3xl">
          🎟️
        </div>
        <h3 class="text-base font-bold text-white">Bạn chưa có vé nào</h3>
        <p class="text-xs text-cinema-muted max-w-sm mx-auto">
          Hãy khám phá các siêu phẩm điện ảnh đang chiếu và đặt vé ngay hôm nay!
        </p>
        <router-link 
          to="/" 
          class="inline-block px-6 py-3 rounded-xl bg-cinema-accent text-white text-xs font-bold shadow-glow-accent"
        >
          Khám phá phim ngay
        </router-link>
      </div>

      <!-- Ticket Cards Grid -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div 
          v-for="ticket in tickets" 
          :key="ticket.booking_code"
          class="relative bg-cinema-surface/90 border border-cinema-border rounded-3xl overflow-hidden shadow-xl backdrop-blur-md space-y-4 p-6"
        >
          <!-- Notch cutouts -->
          <div class="ticket-notch-left"></div>
          <div class="ticket-notch-right"></div>

          <div class="flex items-start justify-between">
            <div>
              <span class="text-[10px] font-bold text-emerald-400 uppercase tracking-widest px-2 py-0.5 rounded bg-emerald-500/10 border border-emerald-500/20">
                ĐÃ THANH TOÁN
              </span>
              <h2 class="text-lg font-black text-white mt-2">{{ ticket.movie?.title }}</h2>
              <p class="text-xs text-cinema-muted">{{ ticket.showtime?.cinema?.name || 'CineReserve Landmark 81' }}</p>
            </div>
            <span class="px-2.5 py-1 rounded-md bg-amber-500/10 border border-amber-500/30 text-amber-300 text-xs font-bold font-mono">
              {{ ticket.booking_code }}
            </span>
          </div>

          <div class="grid grid-cols-2 gap-3 text-xs border-y border-white/5 py-3">
            <div>
              <span class="text-cinema-muted block text-[10px]">Giờ chiếu</span>
              <span class="font-bold text-white">{{ ticket.showtime?.start_time }} ({{ store.selectedDate }})</span>
            </div>
            <div>
              <span class="text-cinema-muted block text-[10px]">Phòng & Ghế</span>
              <span class="font-bold text-emerald-400">
                {{ ticket.seats?.map(s => s.row + s.number).join(', ') }}
              </span>
            </div>
          </div>

          <!-- QR code -->
          <div class="flex items-center justify-between pt-2">
            <div class="bg-white p-2 rounded-xl">
              <img :src="ticket.qr_code" alt="QR" class="w-16 h-16" />
            </div>
            <div class="text-right">
              <span class="text-[10px] text-cinema-muted block">Tổng tiền:</span>
              <span class="text-base font-black text-amber-400">${{ ticket.total_amount }}.00</span>
            </div>
          </div>

        </div>
      </div>

    </main>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useBookingStore } from '../stores/bookingStore';
import Navbar from '../components/Navbar.vue';

const store = useBookingStore();

const tickets = computed(() => {
  if (store.activeTicket && store.bookingHistory.length === 0) {
    return [store.activeTicket];
  }
  return store.bookingHistory;
});
</script>
