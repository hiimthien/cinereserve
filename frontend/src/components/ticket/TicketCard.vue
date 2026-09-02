<template>
  <div 
    class="relative rounded-3xl bg-cinema-card/70 border border-cinema-border hover:border-white/20 transition-all duration-300 overflow-hidden shadow-lg hover:shadow-2xl flex flex-col justify-between group"
  >
    <!-- Top Header Badge & Code -->
    <div class="p-5 space-y-4">
      <div class="flex items-center justify-between gap-2 border-b border-white/5 pb-3">
        <div class="flex items-center gap-2">
          <span class="text-xs font-black font-mono tracking-wider px-2.5 py-1 rounded-xl bg-slate-900 border border-white/10 text-white shadow-inner">
            #{{ ticket.booking_code }}
          </span>
          <span class="text-[10px] text-slate-500 font-mono hidden sm:inline">
            {{ formatBookingDate(ticket.created_at) }}
          </span>
        </div>

        <BaseBadge 
          :variant="getStatusBadgeVariant(ticket.status, ticket.check_in_status)" 
          size="sm"
        >
          {{ getStatusLabel(ticket.status, ticket.check_in_status) }}
        </BaseBadge>
      </div>

      <!-- Movie & Cinema Info -->
      <div class="flex gap-4 items-start">
        <img 
          :src="ticket.movie?.poster_url || 'https://images.unsplash.com/photo-1534447677768-be436bb09401?w=600'" 
          :alt="ticket.movie?.title"
          class="w-16 h-24 object-cover rounded-2xl border border-white/10 shadow-md group-hover:scale-105 transition-transform shrink-0" 
        />

        <div class="space-y-1.5 min-w-0 flex-1">
          <h3 class="text-sm font-extrabold text-white leading-snug line-clamp-1 group-hover:text-cinema-accent transition-colors">
            {{ ticket.movie?.title || 'Phim Chiếu Rạp' }}
          </h3>
          
          <p class="text-xs text-cinema-muted flex items-center gap-1.5 truncate">
            <Building2 class="w-3.5 h-3.5 text-cinema-accent shrink-0" />
            <span class="truncate">{{ ticket.cinema?.name || ticket.showtime?.cinema?.name || 'Cụm Rạp CineReserve' }}</span>
          </p>

          <p class="text-[11px] text-slate-400 flex items-center gap-1.5">
            <Clock class="w-3.5 h-3.5 text-cinema-gold shrink-0" />
            <span>{{ ticket.showtime?.start_time || '19:30' }} • {{ formatShowDate(ticket.showtime?.show_date || ticket.showtime?.date) }}</span>
          </p>
        </div>
      </div>

      <!-- Seats & Room Pill Strip -->
      <div class="flex items-center justify-between p-3 rounded-2xl bg-slate-900/80 border border-white/5 text-xs">
        <div class="space-y-0.5">
          <span class="text-[10px] text-slate-500 font-bold uppercase block">Phòng Chiếu</span>
          <span class="font-bold text-white">{{ ticket.room?.name || ticket.showtime?.room?.name || 'Phòng 01' }}</span>
        </div>

        <div class="space-y-0.5 text-right">
          <span class="text-[10px] text-slate-500 font-bold uppercase block">Ghế Ngồi</span>
          <div class="flex gap-1 justify-end flex-wrap">
            <span 
              v-for="s in ticket.seats" 
              :key="getSeatKey(s)" 
              class="font-mono font-bold text-cinema-accent bg-cinema-accent/10 px-1.5 py-0.5 rounded text-xs border border-cinema-accent/30"
            >
              {{ getSeatLabel(s) }}
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Bottom Actions & Total Price Footer -->
    <div class="p-4 bg-white/[0.02] border-t border-white/5 flex items-center justify-between gap-2">
      <div>
        <span class="text-[10px] text-slate-400 block">Tổng thanh toán</span>
        <span class="text-sm font-black text-amber-400 font-mono">
          {{ formatVnd(ticket.total_amount) }}
        </span>
      </div>

      <BaseButton 
        variant="secondary" 
        size="sm"
        @click="$emit('view-detail', ticket)"
      >
        <template #prefix>
          <Eye class="w-3.5 h-3.5 text-cinema-accent" />
        </template>
        Xem Vé & QR
      </BaseButton>
    </div>
  </div>
</template>

<script setup lang="ts">
import { Building2, Clock, Eye } from 'lucide-vue-next';
import BaseBadge from '../base/BaseBadge.vue';
import BaseButton from '../base/BaseButton.vue';
import { formatVnd } from '../../utils/formatters';

defineProps<{
  ticket: any;
}>();

defineEmits<{
  (e: 'view-detail', ticket: any): void;
}>();

const formatBookingDate = (dateStr?: string) => {
  if (!dateStr) return '';
  const d = new Date(dateStr);
  return d.toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric' });
};

const formatShowDate = (dateStr?: string) => {
  if (!dateStr) return 'Hôm nay';
  const clean = dateStr.split('T')[0].split(' ')[0];
  const parts = clean.split('-');
  if (parts.length === 3) {
    return `${parts[2]}/${parts[1]}/${parts[0]}`;
  }
  return clean;
};

const getSeatLabel = (s: any) => {
  if (!s) return '';
  if (typeof s === 'object') {
    return `${s.row || ''}${s.number || ''}`.trim() || 'Ghế';
  }
  return String(s);
};

const getSeatKey = (s: any) => {
  if (!s) return Math.random();
  if (typeof s === 'object') {
    return s.id || `${s.row}-${s.number}`;
  }
  return String(s);
};

const getStatusBadgeVariant = (status: string, checkInStatus?: string) => {
  if (checkInStatus === 'expired') return 'amber';
  if (checkInStatus === 'checked_in') return 'purple';
  if (status === 'confirmed') return 'emerald';
  if (status === 'cancelled') return 'rose';
  return 'neutral';
};

const getStatusLabel = (status: string, checkInStatus?: string) => {
  if (checkInStatus === 'expired') return '⏳ Quá Hạn';
  if (checkInStatus === 'checked_in') return '🎟️ Đã Soát Vé';
  if (status === 'confirmed') return '✅ Đã Thanh Toán';
  if (status === 'cancelled') return '❌ Đã Hủy';
  return 'Chờ xác nhận';
};
</script>
