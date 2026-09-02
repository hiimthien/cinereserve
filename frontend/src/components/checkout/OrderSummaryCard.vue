<template>
  <BaseCard class="sticky top-8 space-y-5">
    <template #header>
      <div class="flex items-center gap-2">
        <span class="w-2 h-2 rounded-full bg-cinema-gold"></span>
        <h2 class="text-base font-bold text-white">Tóm tắt đơn hàng</h2>
      </div>
    </template>

    <!-- Movie Thumbnail & Title -->
    <div class="flex items-center gap-4 border-b border-white/5 pb-4">
      <img 
        :src="store.currentMovie?.poster_url || 'https://images.unsplash.com/photo-1534447677768-be436bb09401?w=300'" 
        class="w-16 h-24 object-cover rounded-xl border border-white/10 shrink-0" 
      />
      <div class="space-y-1">
        <h3 class="font-extrabold text-white text-base leading-tight">{{ store.currentMovie?.title }}</h3>
        <p class="text-xs text-cinema-muted">{{ store.selectedShowtime?.cinema?.name || 'CGV Landmark 81' }}</p>
        <p class="text-xs text-cinema-muted">{{ store.selectedShowtime?.room?.name || 'Hall 1 (IMAX)' }} • {{ store.selectedShowtime?.start_time || '18:30' }}</p>
        <p class="text-xs text-amber-400 font-semibold flex items-center gap-1.5">
          <Calendar class="w-3.5 h-3.5 text-amber-400" />
          <span>{{ formatDate(store.selectedDate) }}</span>
        </p>
      </div>
    </div>

    <!-- Breakdown -->
    <div class="space-y-2.5 text-xs text-cinema-muted">
      <div class="flex justify-between">
        <span>Số ghế ({{ store.selectedSeats.length }}x):</span>
        <span class="font-bold text-white">
          {{ store.selectedSeats.map((s: any) => s.row + s.number).join(', ') }}
        </span>
      </div>
      <div class="flex justify-between">
        <span>Tiền vé:</span>
        <span class="text-white font-semibold">{{ formatVnd(seatsTotal) }}</span>
      </div>

      <!-- Itemized Combos if selected -->
      <template v-if="selectedCombos.length > 0">
        <div 
          v-for="c in selectedCombos" 
          :key="c.id"
          class="flex justify-between text-amber-300 pl-2 border-l-2 border-amber-500/40"
        >
          <span class="line-clamp-1">{{ c.name }} (x{{ c.quantity }}):</span>
          <span class="font-bold shrink-0">+{{ formatVnd(c.price * c.quantity) }}</span>
        </div>
      </template>

      <!-- Voucher Discount line if applied -->
      <div v-if="discountAmount > 0" class="flex justify-between text-emerald-400 font-bold pl-2 border-l-2 border-emerald-500">
        <span>Ưu đãi ({{ appliedVoucherCode }}):</span>
        <span class="shrink-0">-{{ formatVnd(discountAmount) }}</span>
      </div>

      <div class="flex justify-between">
        <span>Phí dịch vụ online:</span>
        <BaseBadge variant="emerald" size="xs">MIỄN PHÍ</BaseBadge>
      </div>
    </div>

    <!-- Embedded Voucher Selector directly inside Order Summary -->
    <VoucherSelector 
      :seats-total="seatsTotal"
      :snack-total="snackTotal"
      @applied="$emit('voucher-applied', $event)"
    />

    <!-- Loyalty Points Preview Box (Chỉ hiển thị khi đã đăng nhập) -->
    <div v-if="authStore.isAuthenticated" class="p-3 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-xs text-amber-300 flex items-center justify-between shadow-sm">
      <span class="flex items-center gap-1.5">
        <Sparkles class="w-4 h-4 text-amber-400" />
        <span>Tích lũy sau thanh toán:</span>
      </span>
      <strong class="font-mono text-white">
        +{{ earnedPointsPreview }} CinePoints ({{ authStore.tierBadgeInfo.multiplier }})
      </strong>
    </div>

    <!-- Total -->
    <div class="border-t border-white/10 pt-4 flex items-center justify-between">
      <span class="text-sm font-semibold text-slate-300">Tổng thanh toán:</span>
      <span class="text-2xl font-black text-amber-400">{{ formatVnd(totalPrice) }}</span>
    </div>

    <!-- Pay Button using BaseButton -->
    <BaseButton 
      :disabled="isLoading || !userName || !userEmail"
      :loading="isLoading"
      variant="primary"
      size="lg"
      block
      @click="$emit('pay')"
    >
      {{ (paymentMethod === 'vnpay' || paymentMethod === 'momo') ? 'Tôi đã quét mã thanh toán' : `Xác nhận thanh toán ${formatVnd(totalPrice)}` }}
    </BaseButton>

    <p class="text-[11px] text-center text-cinema-muted flex items-center justify-center gap-1.5">
      <ShieldCheck class="w-3.5 h-3.5 text-emerald-400" />
      <span>Giao dịch bảo mật 256-bit SSL • Vé gửi tức thì về email</span>
    </p>
  </BaseCard>
</template>

<script setup lang="ts">
import { Calendar, Sparkles, ShieldCheck } from 'lucide-vue-next';
import BaseCard from '../base/BaseCard.vue';
import BaseBadge from '../base/BaseBadge.vue';
import BaseButton from '../base/BaseButton.vue';
import VoucherSelector from './VoucherSelector.vue';
import { formatVnd, formatDate } from '../../utils/formatters';

defineProps<{
  store: any;
  authStore: any;
  seatsTotal: number;
  snackTotal: number;
  selectedCombos: any[];
  discountAmount: number;
  appliedVoucherCode: string;
  earnedPointsPreview: number;
  totalPrice: number;
  isLoading: boolean;
  userName: string;
  userEmail: string;
  paymentMethod: string;
}>();

defineEmits<{
  (e: 'voucher-applied', voucher: any): void;
  (e: 'pay'): void;
}>();
</script>
