<template>
  <BaseModal 
    v-model="authStore.showRewardModal"
    title="CineReserve Loyalty Club & Ví Voucher"
    maxWidth="lg"
  >
    <div class="space-y-5 p-1">
      
      <!-- Tier Card & Points Overview Banner -->
      <div class="relative overflow-hidden p-6 rounded-3xl bg-gradient-to-br from-slate-900 via-slate-800 to-amber-950/40 border border-amber-500/30 shadow-2xl space-y-4">
        <div class="flex items-start justify-between">
          <div class="space-y-1">
            <div class="flex items-center gap-2">
              <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Hạng Thành Viên</span>
              <BaseBadge variant="gold" size="sm">
                {{ authStore.user?.membership_tier === 'diamond' ? '💎 CineDiamond' : authStore.user?.membership_tier === 'vip' ? '👑 CineVIP' : '🥈 CineMember' }}
              </BaseBadge>
            </div>
            <h3 class="text-xl font-black text-white">{{ authStore.user?.name || 'Khách Hàng' }}</h3>
            <p class="text-xs text-cinema-muted">{{ authStore.user?.email || 'caoluongthienk1@gmail.com' }}</p>
          </div>

          <!-- Points Balance -->
          <div class="text-right">
            <span class="text-xs font-bold text-amber-400 block uppercase tracking-wider">Điểm Tích Lũy</span>
            <span class="text-3xl font-black text-white font-mono flex items-center justify-end gap-1.5 mt-0.5">
              <Sparkles class="w-6 h-6 text-amber-400 animate-pulse" />
              <span>{{ authStore.user?.points || 0 }}</span>
            </span>
            <span class="text-[10px] text-slate-400">CinePoints</span>
          </div>
        </div>

        <!-- Tier Progress Bar -->
        <div class="space-y-1.5 pt-2 border-t border-white/10">
          <div class="flex justify-between text-xs text-slate-300">
            <span>Tiến trình: <strong class="text-white">{{ authStore.nextTierProgress.text }}</strong></span>
            <span class="text-amber-400 font-bold">{{ authStore.nextTierProgress.percent }}%</span>
          </div>
          <div class="w-full h-2 bg-slate-950 rounded-full overflow-hidden border border-white/10">
            <div 
              class="h-full bg-gradient-to-r from-amber-500 to-rose-500 rounded-full transition-all duration-500"
              :style="{ width: `${authStore.nextTierProgress.percent}%` }"
            ></div>
          </div>
        </div>
      </div>

      <!-- Tab Navigation -->
      <div class="flex rounded-2xl bg-slate-900/80 p-1 border border-white/5">
        <button 
          @click="activeTab = 'redeem'"
          type="button"
          class="flex-1 py-2 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-1.5 cursor-pointer"
          :class="activeTab === 'redeem' ? 'bg-cinema-accent text-white shadow-glow-accent' : 'text-slate-400 hover:text-white'"
        >
          <Gift class="w-4 h-4" />
          <span>Đổi Điểm Thưởng</span>
        </button>

        <button 
          @click="activeTab = 'wallet'"
          type="button"
          class="flex-1 py-2 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-1.5 cursor-pointer"
          :class="activeTab === 'wallet' ? 'bg-cinema-accent text-white shadow-glow-accent' : 'text-slate-400 hover:text-white'"
        >
          <Ticket class="w-4 h-4" />
          <span>Ví Voucher Của Tôi ({{ myVouchers.length }})</span>
        </button>
      </div>

      <!-- Success Notification Banner when redeemed -->
      <div v-if="successMessage" class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-xs flex items-center justify-between shadow-glow-green animate-in fade-in duration-200">
        <div class="flex items-center gap-2">
          <CheckCircle2 class="w-5 h-5 text-emerald-400 shrink-0" />
          <span>{{ successMessage }}</span>
        </div>
        <button @click="successMessage = ''" class="text-slate-400 hover:text-white text-xs cursor-pointer">✕</button>
      </div>

      <!-- Error Message -->
      <div v-if="errorMessage" class="p-3 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs flex items-center gap-2">
        <AlertCircle class="w-4 h-4 shrink-0" />
        <span>{{ errorMessage }}</span>
      </div>

      <!-- TAB 1: Rewards Catalog List -->
      <div v-if="activeTab === 'redeem'" class="space-y-3">
        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider flex items-center gap-1.5">
          <Gift class="w-4 h-4 text-cinema-accent" />
          <span>Danh Sách Ưu Đãi Đổi Điểm Thưởng:</span>
        </h4>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5 max-h-[340px] overflow-y-auto pr-1">
          <div 
            v-for="reward in rewardList" 
            :key="reward.id"
            class="p-4 rounded-2xl bg-cinema-card/60 border border-white/5 hover:border-white/20 transition-all flex flex-col justify-between space-y-3"
          >
            <div>
              <div class="flex items-start justify-between gap-2">
                <h5 class="text-xs font-extrabold text-white leading-snug">{{ reward.title }}</h5>
                <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-amber-500/20 text-amber-300 border border-amber-500/30 whitespace-nowrap">
                  {{ reward.badge }}
                </span>
              </div>
              <p class="text-[11px] text-cinema-muted mt-1 leading-relaxed">{{ reward.description }}</p>
            </div>

            <div class="flex items-center justify-between pt-2 border-t border-white/5">
              <span class="text-xs font-black text-amber-400 font-mono">
                {{ reward.points_required }} CinePoints
              </span>

              <button 
                @click="handleRedeem(reward)"
                :disabled="isRedeeming || (authStore.user?.points || 0) < reward.points_required"
                class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer shadow-sm flex items-center gap-1.5"
                :class="[
                  (authStore.user?.points || 0) >= reward.points_required
                    ? 'bg-cinema-accent hover:bg-rose-600 text-white shadow-glow-accent'
                    : 'bg-slate-800 text-slate-500 cursor-not-allowed opacity-50'
                ]"
              >
                <Loader2 v-if="activeRedeemId === reward.id" class="w-3.5 h-3.5 animate-spin" />
                <span v-else>Đổi Ngay</span>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- TAB 2: My Vouchers Wallet -->
      <div v-else class="space-y-3">
        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider flex items-center justify-between">
          <span class="flex items-center gap-1.5">
            <Ticket class="w-4 h-4 text-emerald-400" />
            <span>Mã Ưu Đãi Đang Sở Hữu:</span>
          </span>
          <button 
            @click="fetchMyVouchers" 
            class="text-[11px] text-cinema-accent hover:underline flex items-center gap-1 cursor-pointer"
          >
            <RefreshCw class="w-3 h-3" :class="{ 'animate-spin': isFetchingVouchers }" />
            <span>Làm mới</span>
          </button>
        </h4>

        <!-- Empty Wallet State -->
        <div v-if="myVouchers.length === 0" class="p-8 rounded-2xl bg-cinema-card/40 border border-white/5 text-center space-y-2">
          <Ticket class="w-8 h-8 text-slate-600 mx-auto" />
          <p class="text-xs text-white font-bold">Bạn chưa có voucher nào trong ví</p>
          <p class="text-[11px] text-cinema-muted">Dùng điểm CinePoints tích lũy để đổi voucher giảm giá xem phim ngay!</p>
          <button 
            @click="activeTab = 'redeem'"
            class="mt-2 px-3 py-1.5 rounded-xl bg-cinema-accent hover:bg-rose-600 text-white text-xs font-bold transition-colors cursor-pointer"
          >
            Đổi Điểm Ngay
          </button>
        </div>

        <!-- Vouchers Grid -->
        <div v-else class="space-y-2.5 max-h-[340px] overflow-y-auto pr-1">
          <div 
            v-for="v in myVouchers" 
            :key="v.id || v.code"
            class="p-4 rounded-2xl bg-slate-900/90 border border-white/10 hover:border-emerald-500/40 transition-all flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 group"
          >
            <div class="space-y-1 min-w-0">
              <div class="flex items-center gap-2">
                <span class="font-mono font-black text-xs px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 uppercase tracking-wider">
                  {{ v.code }}
                </span>
                <span class="text-xs font-extrabold text-white truncate">{{ v.title }}</span>
              </div>
              <p class="text-[11px] text-cinema-muted line-clamp-1">{{ v.description }}</p>
              <p class="text-[10px] text-amber-400 font-medium">
                Mức giảm: <strong>{{ formatVnd(v.discount_value) }}</strong> • Hạn: {{ formatDate(v.expires_at) }}
              </p>
            </div>

            <button 
              @click="copyVoucherCode(v.code)"
              class="px-3 py-2 rounded-xl bg-white/10 hover:bg-emerald-500/20 text-slate-200 hover:text-emerald-300 text-xs font-bold transition-all cursor-pointer flex items-center gap-1.5 shrink-0 w-full sm:w-auto justify-center"
            >
              <Copy class="w-3.5 h-3.5" />
              <span>Sao Chép</span>
            </button>
          </div>
        </div>
      </div>

    </div>
  </BaseModal>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';
import { Sparkles, Gift, CheckCircle2, AlertCircle, Loader2, Ticket, Copy, RefreshCw } from 'lucide-vue-next';
import { useAuthStore } from '../../stores/authStore';
import { useToast } from '../../composables/useToast';
import { DEFAULT_LOYALTY_REWARDS, type LoyaltyRewardItem } from '../../constants';
import BaseModal from '../base/BaseModal.vue';
import BaseBadge from '../base/BaseBadge.vue';
import api from '../../services/api';

const authStore = useAuthStore();
const toast = useToast();

const activeTab = ref<'redeem' | 'wallet'>('redeem');
const isRedeeming = ref(false);
const isFetchingVouchers = ref(false);
const activeRedeemId = ref<string | null>(null);
const successMessage = ref('');
const errorMessage = ref('');

const rewardList = ref<LoyaltyRewardItem[]>([...DEFAULT_LOYALTY_REWARDS]);
const myVouchers = ref<any[]>([]);

const formatVnd = (val: number) => {
  if (!val) return '0 đ';
  return new Intl.NumberFormat('vi-VN').format(val) + ' đ';
};

const formatDate = (dateStr?: string) => {
  if (!dateStr) return 'Vô thời hạn';
  const clean = dateStr.split('T')[0].split(' ')[0];
  const parts = clean.split('-');
  if (parts.length === 3) return `${parts[2]}/${parts[1]}/${parts[0]}`;
  return clean;
};

const copyVoucherCode = async (code: string) => {
  try {
    await navigator.clipboard.writeText(code);
    toast.success(`Đã sao chép mã ${code} vào bộ nhớ tạm!`, 'Sao Chép Thành Công');
  } catch {
    toast.info(`Mã voucher của bạn: ${code}`, 'Mã Voucher');
  }
};

const fetchMyVouchers = async () => {
  if (!authStore.user) return;
  isFetchingVouchers.value = true;
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
  } finally {
    isFetchingVouchers.value = false;
  }
};

const handleRedeem = async (reward: any) => {
  if (!authStore.user) {
    authStore.openAuth('login');
    return;
  }

  isRedeeming.value = true;
  activeRedeemId.value = reward.id;
  errorMessage.value = '';
  successMessage.value = '';

  try {
    const res = await api.post('/loyalty/redeem', {
      reward_id: reward.id,
      user_id: authStore.user.id,
    });

    if (res.data?.success && res.data?.data) {
      if (authStore.user) {
        authStore.user.points = res.data.data.remaining_points;
        localStorage.setItem('cinereserve_user', JSON.stringify(authStore.user));
      }
      const voucherCode = res.data.data.voucher.code;
      const msg = `Đổi thành công mã ${voucherCode}! Đã thêm vào Ví Voucher của bạn.`;
      successMessage.value = msg;
      toast.success(msg, 'Đổi Quà Thành Công');
      
      // Auto refresh user vouchers and switch to wallet tab
      await fetchMyVouchers();
      activeTab.value = 'wallet';
    }
  } catch (err: any) {
    const errText = err.response?.data?.message || 'Đổi điểm thưởng không thành công.';
    errorMessage.value = errText;
    toast.error(errText);
  } finally {
    isRedeeming.value = false;
    activeRedeemId.value = null;
  }
};

watch(() => authStore.showRewardModal, (isOpen) => {
  if (isOpen && authStore.isAuthenticated) {
    fetchMyVouchers();
  }
});

onMounted(async () => {
  try {
    const res = await api.get('/loyalty/rewards');
    if (res.data?.data && res.data.data.length > 0) {
      rewardList.value = res.data.data;
    }
  } catch (e) {}

  if (authStore.isAuthenticated) {
    fetchMyVouchers();
  }
});
</script>
