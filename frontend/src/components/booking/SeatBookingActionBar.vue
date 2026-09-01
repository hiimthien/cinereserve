<template>
  <footer class="fixed bottom-0 inset-x-0 bg-cinema-surface/90 border-t border-cinema-border backdrop-blur-xl z-40 px-6 py-4 shadow-2xl">
    <div class="max-w-6xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4">
      
      <!-- Left: Selected Seats Summary -->
      <div class="flex items-center gap-4">
        <div class="text-left">
          <span class="text-xs text-cinema-muted block">Ghế đã chọn:</span>
          <div class="flex items-center gap-1.5 mt-0.5 min-h-[24px]">
            <template v-if="selectedSeats.length > 0">
              <BaseBadge 
                v-for="s in selectedSeats" 
                :key="s.id"
                :variant="s.type === 'vip' ? 'gold' : s.type === 'couple' ? 'accent' : 'emerald'"
                size="sm"
                rounded="md"
              >
                {{ s.row }}{{ s.number }} ({{ s.type === 'vip' ? 'VIP' : s.type === 'couple' ? 'ĐÔI' : 'THƯỜNG' }})
              </BaseBadge>
            </template>
            <span v-else class="text-xs text-slate-500 italic">Chưa chọn ghế nào</span>
          </div>
        </div>

        <div class="h-8 w-px bg-white/10 hidden sm:block"></div>

        <!-- Total Price -->
        <div>
          <span class="text-xs text-cinema-muted block">Tổng tạm tính:</span>
          <span class="text-2xl font-black text-amber-400">
            {{ formatVnd(totalPrice) }}
          </span>
        </div>
      </div>

      <!-- Right: Action Button -->
      <BaseButton 
        :disabled="selectedSeats.length === 0"
        variant="primary"
        size="lg"
        @click="$emit('checkout')"
      >
        <template #suffix>
          <ChevronRight class="w-4 h-4" />
        </template>
        Tiến hành thanh toán ({{ selectedSeats.length }} ghế)
      </BaseButton>

    </div>
  </footer>
</template>

<script setup lang="ts">
import { ChevronRight } from 'lucide-vue-next';
import BaseBadge from '../base/BaseBadge.vue';
import BaseButton from '../base/BaseButton.vue';
import type { Seat } from '../../types';

defineProps<{
  selectedSeats: Seat[];
  totalPrice: number;
}>();

defineEmits<{
  (e: 'checkout'): void;
}>();

const formatVnd = (val: number) => {
  if (!val) return '0 đ';
  return new Intl.NumberFormat('vi-VN').format(val) + ' đ';
};
</script>
