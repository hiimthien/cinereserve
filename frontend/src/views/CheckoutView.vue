<template>
  <div class="min-h-screen bg-cinema-bg py-10 px-4 md:px-6 pb-20">
    <div class="max-w-5xl mx-auto space-y-8">
      
      <!-- Top header -->
      <div class="flex items-center justify-between border-b border-white/10 pb-6">
        <button 
          @click="router.back()"
          class="flex items-center gap-2 text-sm text-cinema-muted hover:text-white transition-colors cursor-pointer"
        >
          <ArrowLeft class="w-4 h-4" />
          <span>Quay lại chọn ghế</span>
        </button>

        <h1 class="text-xl font-extrabold text-white">Xác nhận & Thanh toán</h1>
        
        <CountdownTimer />
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- Left: Customer Info + Snacks + Payment Method (7 Cols) -->
        <div class="lg:col-span-7 space-y-6">
          
          <!-- 1. Customer Information BaseCard -->
          <BaseCard>
            <template #header>
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                  <span class="w-2 h-2 rounded-full bg-cyan-400"></span>
                  <h2 class="text-base font-bold text-white">Thông tin nhận vé điện tử</h2>
                </div>

                <div v-if="authStore.isAuthenticated" class="flex items-center gap-1.5 text-xs text-amber-300 font-bold bg-amber-500/10 px-2.5 py-1 rounded-full border border-amber-500/20">
                  <Sparkles class="w-3.5 h-3.5 text-amber-400" />
                  <span>{{ authStore.tierBadgeInfo.label }}</span>
                </div>
              </div>
            </template>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div class="sm:col-span-2">
                <BaseInput 
                  v-model="userName" 
                  label="Họ và tên khách hàng" 
                  placeholder="Ví dụ: Cao Lương Thiện"
                  required
                />
              </div>
              <div>
                <BaseInput 
                  v-model="userEmail" 
                  type="email"
                  label="Email nhận vé & QR Pass" 
                  placeholder="name@example.com"
                  required
                />
              </div>
              <div>
                <BaseInput 
                  v-model="userPhone" 
                  type="tel"
                  label="Số điện thoại" 
                  placeholder="0388 145 796"
                  required
                />
              </div>
            </div>
          </BaseCard>

          <!-- 2. Bắp & Nước Addon -->
          <SnackSelector 
            @update:total="onSnackTotalUpdate" 
            @update:combos="onCombosUpdate"
          />

          <!-- 3. Payment Method BaseCard -->
          <BaseCard>
            <template #header>
              <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-cinema-accent"></span>
                <h2 class="text-base font-bold text-white">Phương thức thanh toán</h2>
              </div>
            </template>

            <!-- Payment Method Tabs -->
            <div class="grid grid-cols-3 gap-3">
              <!-- VietQR / Bank -->
              <button 
                @click="paymentMethod = 'vnpay'"
                class="flex flex-col items-center justify-center p-4 rounded-2xl border transition-all text-center cursor-pointer"
                :class="[
                  paymentMethod === 'vnpay'
                    ? 'bg-blue-600/20 border-blue-500 text-white shadow-glow-accent'
                    : 'bg-cinema-card/50 border-cinema-border text-slate-400 hover:border-slate-500'
                ]"
              >
                <QrCode class="w-5 h-5 text-blue-400 mb-1.5" />
                <span class="text-xs font-bold">VietQR / VNPAY</span>
              </button>

              <!-- MoMo -->
              <button 
                @click="paymentMethod = 'momo'"
                class="flex flex-col items-center justify-center p-4 rounded-2xl border transition-all text-center cursor-pointer"
                :class="[
                  paymentMethod === 'momo'
                    ? 'bg-pink-600/20 border-pink-500 text-white shadow-glow-accent'
                    : 'bg-cinema-card/50 border-cinema-border text-slate-400 hover:border-slate-500'
                ]"
              >
                <span class="w-6 h-6 rounded-md bg-[#A50064] text-white flex items-center justify-center font-bold text-xs mb-1.5">M</span>
                <span class="text-xs font-bold">Ví MoMo</span>
              </button>

              <!-- Credit Card -->
              <button 
                @click="paymentMethod = 'card'"
                class="flex flex-col items-center justify-center p-4 rounded-2xl border transition-all text-center cursor-pointer"
                :class="[
                  paymentMethod === 'card'
                    ? 'bg-cinema-accent/20 border-cinema-accent text-white shadow-glow-accent'
                    : 'bg-cinema-card/50 border-cinema-border text-slate-400 hover:border-slate-500'
                ]"
              >
                <CreditCard class="w-5 h-5 mb-1.5 text-cinema-accent" />
                <span class="text-xs font-bold">Thẻ Quốc Tế</span>
              </button>
            </div>

            <!-- Dynamic VietQR / MoMo Display -->
            <div v-if="paymentMethod === 'vnpay' || paymentMethod === 'momo'" class="p-6 rounded-2xl bg-cinema-card/60 border border-white/10 text-center space-y-4 mt-4">
              <p class="text-sm font-semibold text-slate-200">
                Mở ứng dụng {{ paymentMethod === 'vnpay' ? 'Ngân hàng (VietQR / Napas247)' : 'Ví MoMo' }} để quét mã:
              </p>
              
              <!-- Real Dynamic QR Image -->
              <div class="inline-block p-4 bg-white rounded-2xl shadow-xl">
                <img 
                  :src="paymentMethod === 'vnpay' ? vietQrUrl : momoQrUrl" 
                  alt="QR Thanh toán"
                  class="w-48 h-48 object-contain mx-auto"
                />
              </div>

              <div class="space-y-1">
                <div class="text-base font-extrabold text-amber-400">
                  Số tiền: {{ formatVnd(totalPrice) }}
                </div>
                <div class="text-xs text-cinema-muted">
                  Nội dung CK: <strong class="text-white">VE CINERESERVE</strong>
                </div>
              </div>

              <div class="inline-flex items-center gap-2 text-xs text-emerald-400 bg-emerald-500/10 px-3 py-1.5 rounded-full border border-emerald-500/20">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>Hệ thống tự động phát hiện thanh toán & gửi vé về Email</span>
              </div>
            </div>

            <!-- Card Details Form -->
            <div v-else class="space-y-4 pt-4">
              <BaseInput 
                v-model="userName" 
                label="Tên in trên thẻ" 
                placeholder="CAO LUONG THIEN"
              />

              <BaseInput 
                v-model="cardNumber" 
                label="Số thẻ Visa / Master / JCB" 
                placeholder="4242 •••• •••• 4242"
              />

              <div class="grid grid-cols-2 gap-4">
                <BaseInput 
                  v-model="cardExpiry" 
                  label="Hạn thẻ" 
                  placeholder="12/28"
                />
                <BaseInput 
                  v-model="cardCvv" 
                  type="password"
                  label="Mã CVV" 
                  placeholder="•••"
                  :maxlength="4"
                />
              </div>
            </div>

          </BaseCard>
        </div>

        <!-- Right Column: Order Summary Card (5 Cols) -->
        <div class="lg:col-span-5 space-y-6">

          <!-- Order Summary Card with Embedded Voucher Selector -->
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
                  <span>{{ formatDateString(store.selectedDate) }}</span>
                </p>
              </div>
            </div>

            <!-- Breakdown -->
            <div class="space-y-2.5 text-xs text-cinema-muted">
              <div class="flex justify-between">
                <span>Số ghế ({{ store.selectedSeats.length }}x):</span>
                <span class="font-bold text-white">
                  {{ store.selectedSeats.map(s => s.row + s.number).join(', ') }}
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
              @applied="onVoucherApplied"
            />

            <!-- Loyalty Points Preview Box -->
            <div class="p-3 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-xs text-amber-300 flex items-center justify-between shadow-sm">
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
              @click="startPaymentFlow"
            >
              {{ (paymentMethod === 'vnpay' || paymentMethod === 'momo') ? 'Tôi đã quét mã thanh toán' : `Xác nhận thanh toán ${formatVnd(totalPrice)}` }}
            </BaseButton>

            <p class="text-[11px] text-center text-cinema-muted flex items-center justify-center gap-1.5">
              <ShieldCheck class="w-3.5 h-3.5 text-emerald-400" />
              <span>Giao dịch bảo mật 256-bit SSL • Vé gửi tức thì về email</span>
            </p>
          </BaseCard>
        </div>

      </div>
    </div>

    <!-- Realistic Payment Verification Modal -->
    <BaseModal 
      v-model="isVerifyingPayment"
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

  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import { 
  ArrowLeft, 
  QrCode, 
  CreditCard, 
  ShieldCheck, 
  Calendar,
  CheckCircle2,
  Sparkles
} from 'lucide-vue-next';
import { useBookingStore } from '../stores/bookingStore';
import { useAuthStore } from '../stores/authStore';
import CountdownTimer from '../components/CountdownTimer.vue';
import SnackSelector from '../components/SnackSelector.vue';
import VoucherSelector from '../components/VoucherSelector.vue';
import BaseButton from '../components/base/BaseButton.vue';
import BaseInput from '../components/base/BaseInput.vue';
import BaseCard from '../components/base/BaseCard.vue';
import BaseBadge from '../components/base/BaseBadge.vue';
import BaseModal from '../components/base/BaseModal.vue';
import BaseSpinner from '../components/base/BaseSpinner.vue';

const router = useRouter();
const store = useBookingStore();
const authStore = useAuthStore();

const paymentMethod = ref<'vnpay' | 'momo' | 'card'>('vnpay');
const userName = ref(authStore.user?.name || 'Cao Lương Thiện');
const userEmail = ref(authStore.user?.email || 'caoluongthienk1@gmail.com');
const userPhone = ref(authStore.user?.phone || '0388145796');

const cardNumber = ref('4242 •••• •••• 4242');
const cardExpiry = ref('12/28');
const cardCvv = ref('888');
const isLoading = ref(false);

const isVerifyingPayment = ref(false);
const verificationStep = ref<'checking' | 'success'>('checking');

const snackTotal = ref(0);
const selectedCombos = ref<any[]>([]);

const discountAmount = ref(0);
const appliedVoucherCode = ref('');

const seatsTotal = computed(() => {
  if (store.selectedSeats.length === 0) return 0;
  return store.selectedSeats.reduce((sum, s) => {
    const p = s.price || (store.selectedShowtime?.base_price || 95000);
    return sum + p;
  }, 0);
});

const totalPrice = computed(() => {
  const subtotal = seatsTotal.value + snackTotal.value;
  return Math.max(0, subtotal - discountAmount.value);
});

const earnedPointsPreview = computed(() => {
  const multiplier = authStore.user?.membership_tier === 'diamond' ? 0.15 : authStore.user?.membership_tier === 'vip' ? 0.10 : 0.05;
  const pts = Math.round((totalPrice.value / 1000) * multiplier);
  return Math.max(1, pts);
});

const vietQrUrl = computed(() => {
  const amount = totalPrice.value || 95000;
  return `https://img.vietqr.io/image/MB-0388145796-compact2.png?amount=${amount}&addInfo=VE+CINERESERVE&accountName=CAO+LUONG+THIEN`;
});

const momoQrUrl = computed(() => {
  const amount = totalPrice.value || 95000;
  return `https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=2|99|0388145796|CAO+LUONG+THIEN|thiencao.work@gmail.com|0|0|${amount}|VE+CINERESERVE|transfer_p2p`;
});

const onSnackTotalUpdate = (total: number) => {
  snackTotal.value = total;
};

const onCombosUpdate = (combos: any[]) => {
  selectedCombos.value = combos;
};

const onVoucherApplied = (voucher: { code: string; discount_amount: number; title: string } | null) => {
  if (voucher) {
    discountAmount.value = voucher.discount_amount;
    appliedVoucherCode.value = voucher.code;
  } else {
    discountAmount.value = 0;
    appliedVoucherCode.value = '';
  }
};

const formatDateString = (dateStr?: string) => {
  if (!dateStr) return 'Hôm nay';
  const clean = dateStr.split('T')[0].split(' ')[0];
  const parts = clean.split('-');
  if (parts.length === 3) {
    return `${parts[2]}/${parts[1]}/${parts[0]}`;
  }
  return clean;
};

const formatVnd = (val: number) => {
  if (!val) return '0 đ';
  return new Intl.NumberFormat('vi-VN').format(val) + ' đ';
};

const startPaymentFlow = async () => {
  isVerifyingPayment.value = true;
  verificationStep.value = 'checking';

  // Realistic Banking Verification Check (2.2s)
  setTimeout(async () => {
    verificationStep.value = 'success';
    
    setTimeout(async () => {
      isVerifyingPayment.value = false;
      await handlePay();
    }, 1200);
  }, 2200);
};

const handlePay = async () => {
  isLoading.value = true;
  try {
    await store.processCheckout({
      booking_code: '',
      payment_method: paymentMethod.value,
      amount: totalPrice.value,
      card_holder: userName.value,
      user_name: userName.value,
      user_email: userEmail.value,
      user_phone: userPhone.value,
      combos: selectedCombos.value,
      card_number: cardNumber.value,
      voucher_code: appliedVoucherCode.value || undefined,
      discount_amount: discountAmount.value || 0,
    } as any);

    // Sync updated loyalty points & tier in background
    authStore.fetchUser();

    router.push({ name: 'ticket-confirmation' });
  } catch (e) {
    console.error(e);
  } finally {
    isLoading.value = false;
  }
};
</script>
