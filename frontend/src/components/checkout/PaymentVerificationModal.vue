<template>
  <BaseModal 
    :model-value="modelValue"
    @update:model-value="$emit('update:modelValue', $event)"
    title="Xác thực giao dịch thanh toán"
    maxWidth="md"
  >
    <div class="p-4 text-center space-y-5">
      <div v-if="verificationStep === 'checking'" class="space-y-4">
        <div class="w-16 h-16 rounded-full bg-cinema-accent/15 border-2 border-cinema-accent flex items-center justify-center mx-auto animate-spin">
          <BaseSpinner size="md" />
        </div>
        <div class="space-y-1">
          <h3 class="text-base font-black text-white">Đang kiểm tra giao dịch...</h3>
          <p class="text-xs text-cinema-muted">
            Hệ thống đang kết nối đối soát với cổng thanh toán {{ paymentMethod === 'momo' ? 'Ví MoMo' : 'VietQR / Napas247' }}
          </p>
        </div>
        <div class="p-3 rounded-2xl bg-cinema-card text-xs text-slate-300 font-mono">
          Số tiền: <strong class="text-amber-400">{{ formatVnd(totalPrice) }}</strong> • Người nhận: <strong class="text-white">CINERESERVE</strong>
        </div>
      </div>

      <div v-else-if="verificationStep === 'success'" class="space-y-4">
        <div class="w-16 h-16 rounded-full bg-emerald-500/20 border-2 border-emerald-500 flex items-center justify-center mx-auto text-emerald-400 shadow-glow-green animate-bounce">
          <CheckCircle2 class="w-8 h-8 text-emerald-400" />
        </div>
        <div class="space-y-1">
          <h3 class="text-base font-black text-emerald-400">Xác thực thanh toán thành công!</h3>
          <p class="text-xs text-cinema-muted">
            Giao dịch đã được khớp lệnh thành công. Đang tạo mã vé điện tử & cộng điểm thưởng...
          </p>
        </div>
      </div>
    </div>
  </BaseModal>
</template>

<script setup lang="ts">
import { CheckCircle2 } from 'lucide-vue-next';
import BaseModal from '../base/BaseModal.vue';
import BaseSpinner from '../base/BaseSpinner.vue';
import { formatVnd } from '../../utils/formatters';

defineProps<{
  modelValue: boolean;
  verificationStep: 'checking' | 'success';
  paymentMethod: string;
  totalPrice: number;
}>();

defineEmits<{
  (e: 'update:modelValue', val: boolean): void;
}>();
</script>
