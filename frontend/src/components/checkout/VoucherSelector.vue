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

      <!-- Error Message -->
      <p v-if="errorMessage" class="text-[11px] text-rose-400 font-semibold flex items-center gap-1.5">
        <AlertCircle class="w-3.5 h-3.5 text-rose-400 shrink-0" />
        <span>{{ errorMessage }}</span>
      </p>

      <!-- Loyalty Points & Voucher Shortcut (Chỉ hiển thị khi đã đăng nhập) -->
      <div v-if="authStore.isAuthenticated" class="space-y-2 pt-1 border-t border-white/5">
        <div class="flex items-center justify-between text-[11px]">
          <div class="flex items-center gap-1.5 text-cinema-muted">
            <Gift class="w-3.5 h-3.5 text-amber-400" />
            <span>Điểm: <strong class="text-white font-mono">{{ authStore.user?.points || 0 }} pts</strong></span>
          </div>

          <button 
            @click="authStore.showRewardModal = true"
            type="button"
            class="text-amber-400 hover:text-amber-300 font-bold transition-colors cursor-pointer flex items-center gap-1"
          >
            <span>Đổi điểm lấy voucher</span>
            <span aria-hidden="true">→</span>
          </button>
        </div>

        <!-- Fast My Vouchers Selection Chips (If user has vouchers) -->
        <div v-if="myVouchers.length > 0" class="space-y-1 pt-1">
          <span class="text-[10px] font-bold text-slate-400 uppercase block tracking-wider">
            Voucher trong ví của bạn (1-click áp dụng):
          </span>
          <div class="flex flex-wrap gap-1.5">
            <button 
              v-for="v in myVouchers" 
              :key="v.id || v.code"
              @click="handleApply(v.code)"
              type="button"
              class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl bg-slate-900/90 hover:bg-emerald-500/20 border border-white/10 hover:border-emerald-500/50 text-[11px] transition-all cursor-pointer group"
              :title="v.title"
            >
              <Ticket class="w-3 h-3 text-emerald-400 group-hover:scale-110 transition-transform" />
              <span class="font-mono font-bold text-white group-hover:text-emerald-300">{{ v.code }}</span>
              <span class="text-[10px] text-amber-400 font-semibold">-{{ formatVnd(v.discount_value) }}</span>
            </button>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';
import { Tag, Sparkles, X, Loader2, AlertCircle, Gift, Ticket } from 'lucide-vue-next';
import api from '../../services/api';
import { useAuthStore } from '../../stores/authStore';
import { useToast } from '../../composables/useToast';

const props = defineProps<{
  seatsTotal: number;
  snackTotal: number;
}>();

const emit = defineEmits<{
  (e: 'applied', voucher: { code: string; discount_amount: number; title: string } | null): void;
}>();

const authStore = useAuthStore();
const toast = useToast();
const voucherInput = ref('');
const isLoading = ref(false);
const errorMessage = ref('');
const appliedVoucher = ref<{ code: string; discount_amount: number; title: string } | null>(null);
const myVouchers = ref<any[]>([]);

const formatVnd = (val: number) => {
  return new Intl.NumberFormat('vi-VN').format(val) + ' đ';
};

const fetchMyVouchers = async () => {
  if (!authStore.user) return;
  try {
    const res = await api.get('/loyalty/my-vouchers', {
      params: {
        user_id: authStore.user.id,
        email: authStore.user.email,
      }
    });
    if (res.data?.data) {
      myVouchers.value = res.data.data;
    }
  } catch (err) {
    console.warn('Could not fetch user vouchers', err);
  }
};

const handleApply = async (code: string) => {
  if (!code) return;
  voucherInput.value = code;
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
      toast.success(res.data.message || `Đã áp dụng mã ${appliedVoucher.value.code}`, 'Voucher Hợp Lệ');
    }
  } catch (err: any) {
    const msg = err.response?.data?.message || 'Mã ưu đãi không hợp lệ cho đơn hàng này.';
    errorMessage.value = msg;
    toast.error(msg, 'Không Thể Áp Dụng');
  } finally {
    isLoading.value = false;
  }
};

const removeVoucher = () => {
  appliedVoucher.value = null;
  voucherInput.value = '';
  errorMessage.value = '';
  emit('applied', null);
  toast.info('Đã hủy áp dụng mã voucher', 'Thông báo');
};

watch(() => authStore.isAuthenticated, (isAuth) => {
  if (isAuth) {
    fetchMyVouchers();
  } else {
    myVouchers.value = [];
  }
});

onMounted(() => {
  if (authStore.isAuthenticated) {
    fetchMyVouchers();
  }
});
</script>
