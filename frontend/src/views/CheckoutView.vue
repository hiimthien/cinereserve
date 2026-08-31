<template>
  <div class="min-h-screen bg-cinema-bg py-12 px-6">
    <div class="max-w-5xl mx-auto space-y-8">
      
      <!-- Top header -->
      <div class="flex items-center justify-between border-b border-white/10 pb-6">
        <button 
          @click="router.back()"
          class="flex items-center gap-2 text-sm text-cinema-muted hover:text-white transition-colors"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
          <span>Quay lại chọn ghế</span>
        </button>

        <h1 class="text-xl font-extrabold text-white">Xác nhận & Thanh toán</h1>
        
        <CountdownTimer />
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- Left: Payment Method Picker & Form (7 Cols) -->
        <div class="lg:col-span-7 space-y-6">
          <div class="bg-cinema-surface/60 border border-cinema-border rounded-3xl p-6 backdrop-blur-md space-y-6">
            <h2 class="text-base font-bold text-white flex items-center gap-2">
              <span class="w-2 h-2 rounded-full bg-cinema-accent"></span>
              Phương thức thanh toán
            </h2>

            <!-- Payment Method Tabs -->
            <div class="grid grid-cols-3 gap-3">
              <!-- Credit Card -->
              <button 
                @click="paymentMethod = 'card'"
                class="flex flex-col items-center justify-center p-4 rounded-2xl border transition-all text-center"
                :class="[
                  paymentMethod === 'card'
                    ? 'bg-cinema-accent/20 border-cinema-accent text-white shadow-glow-accent'
                    : 'bg-cinema-card/50 border-cinema-border text-slate-400 hover:border-slate-500'
                ]"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 mb-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
                <span class="text-xs font-bold">Credit Card</span>
              </button>

              <!-- MoMo -->
              <button 
                @click="paymentMethod = 'momo'"
                class="flex flex-col items-center justify-center p-4 rounded-2xl border transition-all text-center"
                :class="[
                  paymentMethod === 'momo'
                    ? 'bg-pink-600/20 border-pink-500 text-white shadow-glow-accent'
                    : 'bg-cinema-card/50 border-cinema-border text-slate-400 hover:border-slate-500'
                ]"
              >
                <span class="w-6 h-6 rounded-md bg-[#A50064] text-white flex items-center justify-center font-bold text-xs mb-1.5">M</span>
                <span class="text-xs font-bold">Ví MoMo</span>
              </button>

              <!-- VNPay -->
              <button 
                @click="paymentMethod = 'vnpay'"
                class="flex flex-col items-center justify-center p-4 rounded-2xl border transition-all text-center"
                :class="[
                  paymentMethod === 'vnpay'
                    ? 'bg-blue-600/20 border-blue-500 text-white shadow-glow-accent'
                    : 'bg-cinema-card/50 border-cinema-border text-slate-400 hover:border-slate-500'
                ]"
              >
                <span class="w-6 h-6 rounded-md bg-[#005BAA] text-white flex items-center justify-center font-bold text-xs mb-1.5">V</span>
                <span class="text-xs font-bold">VNPAY QR</span>
              </button>
            </div>

            <!-- Card Details Form -->
            <div v-if="paymentMethod === 'card'" class="space-y-4 pt-2">
              <div>
                <label class="block text-xs font-semibold text-cinema-muted mb-1.5">Tên chủ thẻ</label>
                <input 
                  v-model="cardHolder" 
                  type="text" 
                  placeholder="NGUYEN VAN A"
                  class="w-full bg-cinema-card/80 border border-cinema-border rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-cinema-accent uppercase"
                />
              </div>

              <div>
                <label class="block text-xs font-semibold text-cinema-muted mb-1.5">Số thẻ tín dụng / Ghi nợ</label>
                <input 
                  v-model="cardNumber" 
                  type="text" 
                  placeholder="4242 •••• •••• 4242"
                  class="w-full bg-cinema-card/80 border border-cinema-border rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-cinema-accent font-mono"
                />
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs font-semibold text-cinema-muted mb-1.5">Ngày hết hạn</label>
                  <input 
                    v-model="cardExpiry" 
                    type="text" 
                    placeholder="MM/YY"
                    class="w-full bg-cinema-card/80 border border-cinema-border rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-cinema-accent font-mono"
                  />
                </div>
                <div>
                  <label class="block text-xs font-semibold text-cinema-muted mb-1.5">Mã CVV</label>
                  <input 
                    v-model="cardCvv" 
                    type="password" 
                    placeholder="•••"
                    maxlength="4"
                    class="w-full bg-cinema-card/80 border border-cinema-border rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-cinema-accent font-mono"
                  />
                </div>
              </div>
            </div>

            <!-- MoMo / VNPay QR Simulation Info -->
            <div v-else class="p-6 rounded-2xl bg-cinema-card/50 border border-white/5 text-center space-y-3">
              <p class="text-sm text-slate-300">
                Bạn sẽ được chuyển hướng an toàn đến cổng thanh toán {{ paymentMethod === 'vnpay' ? 'VNPAY Sandbox' : 'Ví MoMo' }} để quét mã QR.
              </p>
              <div class="inline-flex items-center gap-2 text-xs text-emerald-400 bg-emerald-500/10 px-3 py-1 rounded-full border border-emerald-500/20">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                Xác thực Idempotent & Chữ ký số an toàn
              </div>
            </div>

          </div>
        </div>

        <!-- Right: Order Summary (5 Cols) -->
        <div class="lg:col-span-5 space-y-6">
          <div class="bg-cinema-surface/60 border border-cinema-border rounded-3xl p-6 backdrop-blur-md space-y-6">
            <h2 class="text-base font-bold text-white flex items-center gap-2">
              <span class="w-2 h-2 rounded-full bg-cinema-gold"></span>
              Tóm tắt đơn hàng
            </h2>

            <!-- Movie Thumbnail & Title -->
            <div class="flex items-center gap-4 border-b border-white/5 pb-4">
              <img 
                :src="store.currentMovie?.poster_url || 'https://images.unsplash.com/photo-1534447677768-be436bb09401?w=300'" 
                class="w-16 h-24 object-cover rounded-xl border border-white/10" 
              />
              <div class="space-y-1">
                <h3 class="font-extrabold text-white text-base">{{ store.currentMovie?.title }}</h3>
                <p class="text-xs text-cinema-muted">{{ store.selectedShowtime?.room?.name }} • {{ store.selectedShowtime?.start_time }}</p>
                <p class="text-xs text-amber-400 font-semibold">{{ store.selectedDate }}</p>
              </div>
            </div>

            <!-- Breakdown -->
            <div class="space-y-3 text-xs text-cinema-muted">
              <div class="flex justify-between">
                <span>Số ghế đã chọn ({{ store.selectedSeats.length }}x):</span>
                <span class="font-bold text-white">
                  {{ store.selectedSeats.map(s => s.row + s.number).join(', ') }}
                </span>
              </div>
              <div class="flex justify-between">
                <span>Giá vé tạm tính:</span>
                <span class="text-white">${{ store.totalPrice }}.00</span>
              </div>
              <div class="flex justify-between">
                <span>Phí tiện ích (Convenience fee):</span>
                <span class="text-emerald-400 font-semibold">MIỄN PHÍ</span>
              </div>
            </div>

            <!-- Total -->
            <div class="border-t border-white/10 pt-4 flex items-center justify-between">
              <span class="text-sm font-semibold text-slate-300">Tổng cộng:</span>
              <span class="text-2xl font-black text-amber-400">${{ store.totalPrice }}.00</span>
            </div>

            <!-- Pay Button -->
            <button 
              :disabled="isLoading"
              @click="handlePay"
              class="w-full py-4 rounded-2xl bg-cinema-accent hover:bg-rose-600 text-white font-bold text-sm tracking-wide shadow-glow-accent transition-all duration-300 flex items-center justify-center gap-2 hover:scale-[1.02] disabled:opacity-50"
            >
              <svg v-if="isLoading" class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <span v-else>Thanh toán ${{ store.totalPrice }}.00</span>
            </button>

            <p class="text-[11px] text-center text-cinema-muted">
              🔒 Giao dịch được mã hóa an toàn 256-bit SSL
            </p>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useBookingStore } from '../stores/bookingStore';
import CountdownTimer from '../components/CountdownTimer.vue';

const router = useRouter();
const store = useBookingStore();

const paymentMethod = ref<'card' | 'momo' | 'vnpay'>('card');
const cardHolder = ref('CAO LUONG THIEN');
const cardNumber = ref('4242 •••• •••• 4242');
const cardExpiry = ref('12/28');
const cardCvv = ref('888');
const isLoading = ref(false);

const handlePay = async () => {
  isLoading.value = true;
  try {
    await store.processCheckout({
      booking_code: '',
      payment_method: paymentMethod.value,
      amount: store.totalPrice,
      card_holder: cardHolder.value,
      card_number: cardNumber.value,
    });
    router.push({ name: 'ticket-confirmation' });
  } catch (e) {
    console.error(e);
  } finally {
    isLoading.value = false;
  }
};
</script>
