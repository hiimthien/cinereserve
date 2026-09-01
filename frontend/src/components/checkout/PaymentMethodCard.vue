<template>
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
        @click="$emit('update:paymentMethod', 'vnpay')"
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
        @click="$emit('update:paymentMethod', 'momo')"
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
        @click="$emit('update:paymentMethod', 'card')"
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
        :model-value="userName" 
        @update:model-value="$emit('update:userName', $event)"
        label="Tên in trên thẻ" 
        placeholder="CAO LUONG THIEN"
      />

      <BaseInput 
        :model-value="cardNumber" 
        @update:model-value="$emit('update:cardNumber', $event)"
        label="Số thẻ Visa / Master / JCB" 
        placeholder="4242 •••• •••• 4242"
      />

      <div class="grid grid-cols-2 gap-4">
        <BaseInput 
          :model-value="cardExpiry" 
          @update:model-value="$emit('update:cardExpiry', $event)"
          label="Hạn thẻ" 
          placeholder="12/28"
        />
        <BaseInput 
          :model-value="cardCvv" 
          @update:model-value="$emit('update:cardCvv', $event)"
          type="password"
          label="Mã CVV" 
          placeholder="•••"
          :maxlength="4"
        />
      </div>
    </div>

  </BaseCard>
</template>

<script setup lang="ts">
import { QrCode, CreditCard } from 'lucide-vue-next';
import BaseCard from '../base/BaseCard.vue';
import BaseInput from '../base/BaseInput.vue';
import { formatVnd } from '../../utils/formatters';

defineProps<{
  paymentMethod: 'vnpay' | 'momo' | 'card';
  vietQrUrl: string;
  momoQrUrl: string;
  totalPrice: number;
  userName: string;
  cardNumber: string;
  cardExpiry: string;
  cardCvv: string;
}>();

defineEmits<{
  (e: 'update:paymentMethod', val: 'vnpay' | 'momo' | 'card'): void;
  (e: 'update:userName', val: string): void;
  (e: 'update:cardNumber', val: string): void;
  (e: 'update:cardExpiry', val: string): void;
  (e: 'update:cardCvv', val: string): void;
}>();
</script>
