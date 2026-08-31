<template>
  <div class="min-h-screen bg-cinema-bg py-12 px-6 flex flex-col items-center justify-center">
    <div class="max-w-md w-full space-y-6">
      
      <!-- Success Badge Header -->
      <div class="text-center space-y-2">
        <div class="w-16 h-16 rounded-full bg-emerald-500/20 border-2 border-emerald-500 flex items-center justify-center mx-auto text-emerald-400 shadow-glow-green animate-bounce">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
          </svg>
        </div>
        <h1 class="text-2xl font-black text-white">Đặt vé thành công!</h1>
        <p class="text-xs text-cinema-muted">Mã vé đã được gửi đến email <span class="text-amber-400 font-medium">thiencao.work@gmail.com</span></p>
      </div>

      <!-- Realistic E-Ticket Card -->
      <div class="relative bg-cinema-surface/90 border border-cinema-border rounded-3xl overflow-hidden shadow-2xl backdrop-blur-xl">
        
        <!-- Ticket Notches (Cutouts) -->
        <div class="ticket-notch-left"></div>
        <div class="ticket-notch-right"></div>

        <!-- Top Part: Movie & Screening Metadata -->
        <div class="p-6 pb-4 space-y-4">
          <div class="flex items-start justify-between">
            <div>
              <span class="text-[10px] uppercase font-bold tracking-widest text-cinema-accent">CineReserve Pass</span>
              <h2 class="text-xl font-extrabold text-white mt-0.5">{{ ticket?.movie?.title || 'Dune: Part Two' }}</h2>
            </div>
            <span class="px-2.5 py-1 rounded-md bg-amber-500/10 border border-amber-500/30 text-amber-300 text-xs font-bold font-mono">
              {{ ticket?.booking_code || 'CR-94821' }}
            </span>
          </div>

          <div class="grid grid-cols-2 gap-4 text-xs">
            <div>
              <span class="text-cinema-muted block text-[11px]">Ngày chiếu</span>
              <span class="font-bold text-white">{{ store.selectedDate || '31/08/2026' }}</span>
            </div>
            <div>
              <span class="text-cinema-muted block text-[11px]">Giờ chiếu</span>
              <span class="font-bold text-white">{{ ticket?.showtime?.start_time || '18:30' }}</span>
            </div>
            <div>
              <span class="text-cinema-muted block text-[11px]">Phòng chiếu</span>
              <span class="font-bold text-white">{{ ticket?.showtime?.room?.name || 'Hall 1 (IMAX Laser)' }}</span>
            </div>
            <div>
              <span class="text-cinema-muted block text-[11px]">Ghế</span>
              <span class="font-bold text-emerald-400 text-sm">
                {{ ticket?.seats?.map(s => s.row + s.number).join(', ') || 'G07, G08' }}
              </span>
            </div>
          </div>
        </div>

        <!-- Dashed Divider Line with Notches -->
        <div class="relative py-2">
          <div class="border-t-2 border-dashed border-white/10 mx-6"></div>
        </div>

        <!-- Bottom Part: Scannable QR Code -->
        <div class="p-6 pt-4 text-center space-y-4 bg-cinema-card/30">
          <div class="bg-white p-3 rounded-2xl inline-block shadow-lg mx-auto">
            <img 
              :src="ticket?.qr_code || 'https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=CINERESERVE-DUNE2-CR-94821'" 
              alt="Ticket QR Code" 
              class="w-36 h-36 mx-auto"
            />
          </div>
          <p class="text-[11px] text-cinema-muted tracking-wider uppercase font-mono">
            Vui lòng đưa mã QR cho nhân viên soát vé tại rạp
          </p>
        </div>

      </div>

      <!-- Action Buttons -->
      <div class="space-y-3 pt-2">
        <button 
          @click="downloadPdf"
          class="w-full py-3.5 rounded-2xl bg-cinema-accent hover:bg-rose-600 text-white font-bold text-sm shadow-glow-accent transition-all duration-300 flex items-center justify-center gap-2 hover:scale-[1.02]"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
          </svg>
          <span>Tải vé PDF điện tử</span>
        </button>

        <button 
          @click="router.push({ name: 'movie-detail' })"
          class="w-full py-3.5 rounded-2xl bg-cinema-surface hover:bg-cinema-card text-slate-300 font-semibold text-sm border border-cinema-border transition-colors text-center block"
        >
          Quay về Trang chủ
        </button>
      </div>

    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useBookingStore } from '../stores/bookingStore';
import confetti from 'canvas-confetti';

const router = useRouter();
const store = useBookingStore();

const ticket = computed(() => store.activeTicket);

onMounted(() => {
  // Fire celebration confetti
  confetti({
    particleCount: 80,
    spread: 70,
    origin: { y: 0.6 }
  });
});

const downloadPdf = () => {
  alert('Đang tải file PDF vé điện tử (Mock Queue Worker)...');
};
</script>
