<template>
  <BaseModal 
    v-model="authStore.showRewardModal"
    title="CineReserve Loyalty Club • Đổi Thưởng"
    maxWidth="lg"
  >
    <div class="space-y-6 p-1">
      
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

      <!-- Success Notification Banner when redeemed -->
      <div v-if="successMessage" class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-xs flex items-center justify-between shadow-glow-green">
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

      <!-- Rewards Catalog List -->
      <div class="space-y-3">
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

    </div>
  </BaseModal>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Sparkles, Gift, CheckCircle2, AlertCircle, Loader2 } from 'lucide-vue-next';
import { useAuthStore } from '../stores/authStore';
import BaseModal from './base/BaseModal.vue';
import BaseBadge from './base/BaseBadge.vue';
import api from '../services/api';

const authStore = useAuthStore();

const isRedeeming = ref(false);
const activeRedeemId = ref<string | null>(null);
const successMessage = ref('');
const errorMessage = ref('');

const rewardList = ref([
  { id: 'voucher_20k', points_required: 50, title: 'Voucher Giảm 20.000 đ', description: 'Áp dụng cho mọi đơn đặt vé từ 95.000 đ', badge: 'Phổ biến' },
  { id: 'free_snack', points_required: 100, title: 'Miễn Phí 1 Solo Combo Bắp Nước', description: 'Tặng 1 Bắp rang bơ nóng hổi + 1 Nước ngọt lớn tại quầy', badge: 'Bắp Nước Free' },
  { id: 'voucher_50k', points_required: 150, title: 'Voucher Giảm 50.000 đ', description: 'Áp dụng cho đơn hàng tổng từ 150.000 đ trở lên', badge: 'Tiết kiệm lớn' },
  { id: 'free_ticket', points_required: 250, title: 'Miễn Phí 1 Vé Xem Phim Tiêu Chuẩn', description: 'Miễn phí 100% 1 vé xem phim 2D/3D bất kỳ trị giá 95.000 đ', badge: 'Vé Miễn Phí' },
  { id: 'vip_couple_pass', points_required: 400, title: 'Gói Trọn Gói Siêu VIP Đôi', description: '2 Vé Phim Ghế VIP/Couple + 1 Couple Combo Bắp Nước lớn', badge: 'Đặc Quyền VVIP' },
]);

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
      successMessage.value = `Đổi thành công mã ${res.data.data.voucher.code}! Mã ưu đãi đã được gửi về email ${authStore.user.email}.`;
    }
  } catch (err: any) {
    errorMessage.value = err.response?.data?.message || 'Đổi điểm thưởng không thành công.';
  } finally {
    isRedeeming.value = false;
    activeRedeemId.value = null;
  }
};

onMounted(async () => {
  try {
    const res = await api.get('/loyalty/rewards');
    if (res.data?.data) {
      rewardList.value = res.data.data;
    }
  } catch (e) {
    // Keep fallback list
  }
});
</script>
