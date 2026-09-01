<template>
  <BaseModal 
    :modelValue="modelValue" 
    title="Chi Tiết Vé Điện Tử VIP" 
    maxWidth="lg"
    @update:modelValue="$emit('update:modelValue', $event)"
  >
    <div v-if="ticket" class="space-y-5 p-1">
      <!-- Movie Banner Header -->
      <div class="relative rounded-2xl overflow-hidden bg-slate-900 border border-white/10 p-4 flex gap-4 items-center">
        <img 
          :src="ticket.movie?.poster_url || 'https://images.unsplash.com/photo-1534447677768-be436bb09401?w=600'" 
          class="w-20 h-28 object-cover rounded-xl border border-white/10 shadow-lg shrink-0" 
        />
        <div class="space-y-1.5 flex-1 min-w-0">
          <div class="flex items-center gap-2">
            <span class="font-mono text-xs font-black px-2 py-0.5 rounded bg-cinema-accent/20 text-cinema-accent border border-cinema-accent/30">
              #{{ ticket.booking_code }}
            </span>
            <BaseBadge :variant="ticket.check_in_status === 'checked_in' ? 'purple' : 'emerald'" size="xs">
              {{ ticket.check_in_status === 'checked_in' ? 'Đã soát vé' : 'Hợp lệ' }}
            </BaseBadge>
          </div>
          <h3 class="text-base font-black text-white leading-tight truncate">{{ ticket.movie?.title }}</h3>
          <p class="text-xs text-cinema-muted truncate">{{ ticket.cinema?.name }} • {{ ticket.room?.name }}</p>
          <p class="text-xs text-amber-300 font-bold flex items-center gap-1.5 pt-0.5">
            <Clock class="w-3.5 h-3.5" />
            <span>{{ ticket.showtime?.start_time }} - {{ ticket.showtime?.date }}</span>
          </p>
        </div>
      </div>

      <!-- Live QR Pass Section -->
      <div class="p-6 rounded-3xl bg-slate-950/90 border border-white/10 text-center space-y-3 shadow-2xl">
        <div class="inline-block p-3 rounded-2xl bg-white shadow-glow-accent">
          <img 
            :src="`https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=CINERESERVE-${ticket.booking_code}`" 
            :alt="ticket.booking_code"
            class="w-36 h-36 mx-auto block"
          />
        </div>
        <div>
          <span class="text-xs font-mono font-bold tracking-widest text-slate-400 block uppercase">Mã Vé Điện Tử</span>
          <span class="text-xl font-black text-white font-mono tracking-widest">{{ ticket.booking_code }}</span>
        </div>
        <p class="text-[11px] text-slate-400 flex items-center justify-center gap-1.5">
          <CheckCircle2 class="w-3.5 h-3.5 text-emerald-400" />
          <span>Đưa mã QR này cho nhân viên tại quầy soát vé rạp</span>
        </p>
      </div>

      <!-- Details Grid -->
      <div class="grid grid-cols-2 gap-3 text-xs">
        <div class="p-3 rounded-2xl bg-white/5 border border-white/5 space-y-1">
          <span class="text-[10px] text-slate-500 font-bold uppercase">Khách Hàng</span>
          <p class="font-bold text-white truncate">{{ ticket.user_name || 'Khách Hàng' }}</p>
          <p class="text-[11px] text-slate-400 truncate">{{ ticket.user_email }}</p>
        </div>

        <div class="p-3 rounded-2xl bg-white/5 border border-white/5 space-y-1 text-right">
          <span class="text-[10px] text-slate-500 font-bold uppercase">Tổng Thanh Toán</span>
          <p class="text-sm font-black text-amber-400 font-mono">{{ formatVnd(ticket.total_amount) }}</p>
          <p class="text-[10px] text-emerald-400 font-semibold">{{ ticket.payment?.payment_method?.toUpperCase() || 'Đã thanh toán' }}</p>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="pt-2 flex gap-3">
        <BaseButton 
          variant="primary" 
          size="md" 
          block
          @click="downloadTicketPdf"
        >
          <template #prefix>
            <Download class="w-4 h-4" />
          </template>
          Tải Vé PDF
        </BaseButton>
      </div>
    </div>
  </BaseModal>
</template>

<script setup lang="ts">
import { Clock, CheckCircle2, Download } from 'lucide-vue-next';
import BaseModal from '../base/BaseModal.vue';
import BaseBadge from '../base/BaseBadge.vue';
import BaseButton from '../base/BaseButton.vue';
import { formatVnd } from '../../utils/formatters';
import { useToast } from '../../composables/useToast';

const props = defineProps<{
  modelValue: boolean;
  ticket: any | null;
}>();

defineEmits<{
  (e: 'update:modelValue', val: boolean): void;
}>();

const toast = useToast();

const downloadTicketPdf = () => {
  if (!props.ticket) return;
  window.print();
  toast.success('Đang chuẩn bị bản in vé điện tử PDF...', 'Tải vé');
};
</script>
