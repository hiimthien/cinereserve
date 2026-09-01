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
          <CustomerInfoCard 
            v-model:userName="userName"
            v-model:userEmail="userEmail"
            v-model:userPhone="userPhone"
            :authStore="authStore"
          />

          <SnackSelector 
            @update:total="onSnackTotalUpdate" 
            @update:combos="onCombosUpdate"
          />

          <PaymentMethodCard 
            v-model:paymentMethod="paymentMethod"
            v-model:userName="userName"
            v-model:cardNumber="cardNumber"
            v-model:cardExpiry="cardExpiry"
            v-model:cardCvv="cardCvv"
            :vietQrUrl="vietQrUrl"
            :momoQrUrl="momoQrUrl"
            :totalPrice="totalPrice"
          />
        </div>

        <!-- Right Column: Order Summary Card (5 Cols) -->
        <div class="lg:col-span-5 space-y-6">
          <OrderSummaryCard 
            :store="store"
            :authStore="authStore"
            :seatsTotal="seatsTotal"
            :snackTotal="snackTotal"
            :selectedCombos="selectedCombos"
            :discountAmount="discountAmount"
            :appliedVoucherCode="appliedVoucherCode"
            :earnedPointsPreview="earnedPointsPreview"
            :totalPrice="totalPrice"
            :isLoading="isLoading"
            :userName="userName"
            :userEmail="userEmail"
            :paymentMethod="paymentMethod"
            @voucher-applied="onVoucherApplied"
            @pay="startPaymentFlow"
          />
        </div>

      </div>
    </div>

    <!-- Realistic Payment Verification Modal -->
    <PaymentVerificationModal 
      v-model="isVerifyingPayment"
      :verificationStep="verificationStep"
      :paymentMethod="paymentMethod"
      :totalPrice="totalPrice"
    />
  </div>
</template>

<script setup lang="ts">
import { useRouter } from 'vue-router';
import { ArrowLeft } from 'lucide-vue-next';
import { useCheckout } from '../composables/useCheckout';
import CountdownTimer from '../components/common/CountdownTimer.vue';
import SnackSelector from '../components/checkout/SnackSelector.vue';
import CustomerInfoCard from '../components/checkout/CustomerInfoCard.vue';
import PaymentMethodCard from '../components/checkout/PaymentMethodCard.vue';
import OrderSummaryCard from '../components/checkout/OrderSummaryCard.vue';
import PaymentVerificationModal from '../components/checkout/PaymentVerificationModal.vue';

const router = useRouter();
const {
  store,
  authStore,
  paymentMethod,
  userName,
  userEmail,
  userPhone,
  cardNumber,
  cardExpiry,
  cardCvv,
  isLoading,
  isVerifyingPayment,
  verificationStep,
  snackTotal,
  selectedCombos,
  discountAmount,
  appliedVoucherCode,
  seatsTotal,
  totalPrice,
  earnedPointsPreview,
  vietQrUrl,
  momoQrUrl,
  onSnackTotalUpdate,
  onCombosUpdate,
  onVoucherApplied,
  startPaymentFlow,
} = useCheckout();
</script>
