<template>
  <div class="p-4 rounded-2xl bg-cinema-card/50 border border-white/5 space-y-3.5">
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-1.5 text-xs font-bold text-white">
        <Tag class="w-4 h-4 text-emerald-400" />
        <span>Mã ưu đãi / Voucher</span>
      </div>
      <span v-if="appliedVoucher" class="text-[10px] text-emerald-400 font-bold bg-emerald-500/10 px-2 py-0.5 rounded-full border border-emerald-500/20 flex items-center gap-1">
        <Sparkles class="w-3 h-3 text-emerald-400" />
        <span>Đã áp dụng</span>
      </span>
    </div>

    <!-- If voucher is already applied -->
    <div 
      v-if="appliedVoucher" 
      class="flex items-center justify-between p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 shadow-glow-green"
    >
      <div class="space-y-0.5">
        <div class="flex items-center gap-1.5">
          <span class="font-mono font-black text-xs uppercase px-1.5 py-0.5 rounded bg-emerald-500/20 text-emerald-300 border border-emerald-500/40">
            {{ appliedVoucher.code }}
          </span>
          <span class="font-bold text-[11px] text-white">{{ appliedVoucher.title }}</span>
        </div>
        <p class="text-[11px] text-emerald-400 font-semibold">
          Tiết kiệm: <strong class="text-white">{{ formatVnd(appliedVoucher.discount_amount) }}</strong>
        </p>
      </div>

      <button 
        @click="removeVoucher"
        class="px-2.5 py-1 rounded-lg bg-white/10 hover:bg-white/20 text-slate-300 hover:text-white text-[11px] font-semibold transition-colors cursor-pointer flex items-center gap-1"
      >
        <X class="w-3 h-3" />
        <span>Gỡ</span>
      </button>
    </div>

    <!-- Input Form if no voucher applied -->
    <div v-else class="space-y-2.5">
      <div class="flex gap-2">
        <input 
          v-model="voucherInput"
          @keyup.enter="handleApply(voucherInput)"
          type="text"
          placeholder="Nhập mã (vd: CINEMA20)"
          class="flex-1 bg-slate-900/90 border border-cinema-border rounded-xl px-3 py-2 text-xs text-white placeholder:text-cinema-muted uppercase font-mono tracking-wider focus:outline-none focus:border-cinema-accent transition-colors"
        />

        <button 
          :disabled="!voucherInput || isLoading"
          @click="handleApply(voucherInput)"
          class="px-3.5 py-2 rounded-xl bg-cinema-accent hover:bg-rose-600 disabled:opacity-30 text-white text-xs font-bold transition-colors cursor-pointer shrink-0 shadow-sm flex items-center justify-center min-w-[70px]"
        >
          <Loader2 v-if="isLoading" class="w-3.5 h-3.5 animate-spin" />
          <span v-else>Áp dụng</span>
        </button>
      </div>

      <!-- Error Message with Lucide SVG AlertCircle -->
      <p v-if="errorMessage" class="text-[11px] text-rose-400 font-semibold flex items-center gap-1.5">
        <AlertCircle class="w-3.5 h-3.5 text-rose-400 shrink-0" />
        <span>{{ errorMessage }}</span>
      </p>

      <!-- Suggested Hot Vouchers (1-Click Apply) -->
      <div class="space-y-1.5 pt-1">
        <span class="text-[10px] font-bold text-cinema-muted uppercase tracking-wider block">
          Gợi ý mã hot:
        </span>

        <div class="flex flex-wrap gap-1.5">
          <button 
            v-for="v in suggestedVouchers" 
            :key="v.code"
            @click="handleApply(v.code)"
            class="flex items-center gap-1 px-2 py-1 rounded-lg bg-slate-900/80 hover:bg-slate-800 border border-white/5 hover:border-amber-500/40 text-[11px] transition-all cursor-pointer group"
          >
            <Ticket class="w-3 h-3 text-amber-400/70 group-hover:text-amber-300" />
            <span class="font-mono font-bold text-amber-400 group-hover:text-amber-300">{{ v.code }}</span>
            <span class="text-slate-400 text-[10px]">({{ v.label }})</span>
          </button>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Tag, Sparkles, X, Loader2, AlertCircle, Ticket } from 'lucide-vue-next';
import api from '../services/api';

const props = defineProps<{
  seatsTotal: number;
  snackTotal: number;
}>();

const emit = defineEmits<{
  (e: 'applied', voucher: { code: string; discount_amount: number; title: string } | null): void;
}>();

const voucherInput = ref('');
const isLoading = ref(false);
const errorMessage = ref('');
const appliedVoucher = ref<{ code: string; discount_amount: number; title: string } | null>(null);

const suggestedVouchers = [
  { code: 'CINEMA20', label: 'Giảm 20%' },
  { code: 'CHAOBANMOI', label: '-30K' },
  { code: 'BAPNUOCFREE', label: '-50K Bắp' },
  { code: 'FREEVECINE', label: 'Tặng 1 vé' },
  { code: 'VIPCINE50', label: '-50K VIP' },
];

const formatVnd = (val: number) => {
  return new Intl.NumberFormat('vi-VN').format(val) + ' đ';
};

const handleApply = async (code: string) => {
  if (!code) return;
  isLoading.value = true;
  errorMessage.value = '';

  try {
    const res = await api.post('/vouchers/apply', {
      code: code.trim().toUpperCase(),
      seats_total: props.seatsTotal,
      snack_total: props.snackTotal,
    });

    if (res.data?.success && res.data?.data) {
      appliedVoucher.value = {
        code: res.data.data.code,
        title: res.data.data.title,
        discount_amount: res.data.data.discount_amount,
      };
      emit('applied', appliedVoucher.value);
    }
  } catch (err: any) {
    errorMessage.value = err.response?.data?.message || 'Mã ưu đãi không hợp lệ cho đơn hàng này.';
  } finally {
    isLoading.value = false;
  }
};

const removeVoucher = () => {
  appliedVoucher.value = null;
  voucherInput.value = '';
  errorMessage.value = '';
  emit('applied', null);
};
</script>
