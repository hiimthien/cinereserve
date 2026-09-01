<template>
  <div class="min-h-screen bg-cinema-bg py-12 px-4 md:px-6 flex flex-col items-center justify-center">
    <div class="max-w-lg w-full space-y-6">
      
      <!-- Success Badge Header -->
      <div class="text-center space-y-2">
        <div class="w-16 h-16 rounded-full bg-emerald-500/20 border-2 border-emerald-500 flex items-center justify-center mx-auto text-emerald-400 shadow-glow-green animate-bounce">
          <CheckCircle2 class="w-8 h-8 text-emerald-400" />
        </div>
        <h1 class="text-2xl font-black text-white">Đặt vé thành công!</h1>
        <p class="text-xs text-cinema-muted">
          Vé điện tử và mã QR đã được gửi đến email: 
          <span class="text-amber-400 font-bold block sm:inline mt-0.5 sm:mt-0">{{ ticket?.user_email || 'caoluongthienk1@gmail.com' }}</span>
        </p>
      </div>

      <!-- Realistic E-Ticket Card -->
      <div class="relative bg-cinema-surface/90 border border-cinema-border rounded-3xl overflow-hidden shadow-2xl backdrop-blur-xl">
        
        <!-- Ticket Notches (Cutouts) -->
        <div class="ticket-notch-left"></div>
        <div class="ticket-notch-right"></div>

        <!-- Top Part: Movie & Screening Metadata -->
        <div class="p-6 pb-4 space-y-4">
          <div class="flex items-start justify-between gap-3">
            <div>
              <span class="text-[10px] uppercase font-bold tracking-widest text-cinema-accent flex items-center gap-1">
                <Ticket class="w-3 h-3 text-cinema-accent" />
                <span>CineReserve E-Pass</span>
              </span>
              <h2 class="text-xl font-extrabold text-white mt-0.5">{{ ticket?.movie?.title || 'Spider-Man: Across the Spider-Verse' }}</h2>
            </div>
            <span class="px-2.5 py-1 rounded-md bg-amber-500/10 border border-amber-500/30 text-amber-300 text-xs font-bold font-mono shrink-0">
              {{ ticket?.booking_code || 'CR-94821' }}
            </span>
          </div>

          <!-- Cinema Name & Address Banner -->
          <div class="p-3 rounded-2xl bg-cinema-card/50 border border-white/5 space-y-1">
            <div class="flex items-center gap-1.5 text-xs font-bold text-white">
              <Building2 class="w-4 h-4 text-cinema-accent shrink-0" />
              <span>{{ cinemaName }}</span>
            </div>
            <div class="flex items-start gap-1.5 text-[11px] text-cinema-muted">
              <MapPin class="w-3.5 h-3.5 text-emerald-400 shrink-0 mt-0.5" />
              <span class="leading-relaxed">{{ cinemaAddress }}</span>
            </div>
          </div>

          <!-- Metadata Grid -->
          <div class="grid grid-cols-2 gap-3.5 text-xs">
            <div>
              <span class="text-cinema-muted block text-[11px] flex items-center gap-1">
                <Calendar class="w-3 h-3" />
                <span>Ngày chiếu</span>
              </span>
              <span class="font-bold text-white mt-0.5 block">{{ formattedDate }}</span>
            </div>
            <div>
              <span class="text-cinema-muted block text-[11px] flex items-center gap-1">
                <Clock class="w-3 h-3" />
                <span>Giờ chiếu</span>
              </span>
              <span class="font-bold text-white mt-0.5 block">{{ showtimeStart }}</span>
            </div>
            <div>
              <span class="text-cinema-muted block text-[11px]">Phòng chiếu</span>
              <span class="font-bold text-white mt-0.5 block">{{ roomName }}</span>
            </div>
            <div>
              <span class="text-cinema-muted block text-[11px]">Vị trí ghế</span>
              <span class="font-bold text-emerald-400 text-sm mt-0.5 block">
                {{ seatNames }}
              </span>
            </div>
          </div>

          <!-- Selected Combos list (if any) -->
          <div v-if="ticket?.combos && ticket.combos.length > 0" class="pt-2 border-t border-white/5">
            <span class="text-cinema-muted block text-[11px] mb-1">Combo bắp nước đã đặt:</span>
            <div class="flex flex-wrap gap-1.5">
              <span 
                v-for="cb in ticket.combos" 
                :key="cb.id"
                class="px-2 py-0.5 rounded-lg bg-amber-500/10 border border-amber-500/20 text-amber-300 text-[10px] font-semibold"
              >
                🍿 {{ cb.name }} (x{{ cb.quantity }})
              </span>
            </div>
          </div>
        </div>

        <!-- Dashed Divider Line with Notches -->
        <div class="relative py-2">
          <div class="border-t-2 border-dashed border-white/10 mx-6"></div>
        </div>

        <!-- Bottom Part: Scannable QR Code -->
        <div class="p-6 pt-4 text-center space-y-3 bg-cinema-card/30">
          <div class="bg-white p-3 rounded-2xl inline-block shadow-lg mx-auto">
            <img 
              :src="ticket?.qr_code || 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=CINERESERVE-PASS'" 
              alt="Ticket QR Code" 
              class="w-40 h-40 mx-auto"
            />
          </div>
          <p class="text-[11px] text-cinema-muted tracking-wider uppercase font-mono flex items-center justify-center gap-1.5">
            <QrCode class="w-3.5 h-3.5 text-cinema-accent" />
            <span>Vui lòng đưa mã QR cho nhân viên soát vé tại rạp</span>
          </p>
        </div>

      </div>

      <!-- Action Buttons -->
      <div class="space-y-3 pt-2">
        <BaseButton 
          variant="primary"
          size="lg"
          block
          @click="downloadPdf"
        >
          <template #prefix>
            <Download class="w-4 h-4" />
          </template>
          Tải vé PDF điện tử
        </BaseButton>

        <!-- Direct Email Inbox Button -->
        <a 
          href="https://mail.google.com" 
          target="_blank"
          class="w-full py-3.5 rounded-2xl bg-cyan-600/20 hover:bg-cyan-600/30 text-cyan-300 font-bold text-xs border border-cyan-500/30 transition-all flex items-center justify-center gap-2 cursor-pointer"
        >
          <Mail class="w-4 h-4" />
          <span>Mở hộp thư Gmail xác nhận vé ({{ ticket?.user_email || 'caoluongthienk1@gmail.com' }})</span>
        </a>

        <BaseButton 
          variant="secondary"
          size="md"
          block
          @click="router.push('/')"
        >
          <template #prefix>
            <Home class="w-4 h-4" />
          </template>
          Quay về Trang chủ
        </BaseButton>
      </div>

    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { 
  CheckCircle2, 
  Ticket, 
  Building2, 
  MapPin, 
  Calendar, 
  Clock, 
  QrCode, 
  Download, 
  Mail, 
  Home 
} from 'lucide-vue-next';
import { useBookingStore } from '../stores/bookingStore';
import BaseButton from '../components/base/BaseButton.vue';
import confetti from 'canvas-confetti';

const router = useRouter();
const store = useBookingStore();

const ticket = computed(() => store.activeTicket);

const cinemaName = computed(() => {
  return ticket.value?.showtime?.cinema?.name || store.selectedShowtime?.cinema?.name || 'CGV Landmark 81';
});

const cinemaAddress = computed(() => {
  return ticket.value?.showtime?.cinema?.address || store.selectedShowtime?.cinema?.address || 'Tầng B1, TTTM Vincom Landmark 81, 720A Điện Biên Phủ, P. 22, Q. Bình Thạnh, TP.HCM';
});

const formattedDate = computed(() => {
  const raw = ticket.value?.showtime?.show_date || store.selectedDate || '01/09/2026';
  const clean = raw.split('T')[0].split(' ')[0];
  const parts = clean.split('-');
  if (parts.length === 3) {
    return `${parts[2]}/${parts[1]}/${parts[0]}`;
  }
  return clean;
});

const showtimeStart = computed(() => {
  return ticket.value?.showtime?.start_time || store.selectedShowtime?.start_time || '09:00';
});

const roomName = computed(() => {
  return ticket.value?.showtime?.room?.name || store.selectedShowtime?.room?.name || 'Phòng chiếu 1 (IMAX Laser)';
});

const seatNames = computed(() => {
  if (ticket.value?.seats && ticket.value.seats.length > 0) {
    return ticket.value.seats.map(s => `${s.row}${s.number}`).join(', ');
  }
  if (store.selectedSeats.length > 0) {
    return store.selectedSeats.map(s => `${s.row}${s.number}`).join(', ');
  }
  return 'E7';
});

onMounted(() => {
  // Fire celebration confetti
  confetti({
    particleCount: 80,
    spread: 70,
    origin: { y: 0.6 }
  });
});

const downloadPdf = () => {
  alert('Đang tải file PDF vé điện tử...');
};
</script>

<style scoped>
.ticket-notch-left,
.ticket-notch-right {
  position: absolute;
  top: calc(50% + 24px);
  width: 24px;
  height: 24px;
  background-color: var(--color-cinema-bg, #0B0F19);
  border-radius: 50%;
  z-index: 10;
}
.ticket-notch-left {
  left: -12px;
  border-right: 1px solid var(--color-cinema-border, rgba(255, 255, 255, 0.08));
}
.ticket-notch-right {
  right: -12px;
  border-left: 1px solid var(--color-cinema-border, rgba(255, 255, 255, 0.08));
}
</style>
