<template>
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
          :model-value="userName" 
          @update:model-value="$emit('update:userName', $event)"
          label="Họ và tên khách hàng" 
          placeholder="Ví dụ: Cao Lương Thiện"
          required
        />
      </div>
      <div>
        <BaseInput 
          :model-value="userEmail" 
          @update:model-value="$emit('update:userEmail', $event)"
          type="email"
          label="Email nhận vé & QR Pass" 
          placeholder="name@example.com"
          required
        />
      </div>
      <div>
        <BaseInput 
          :model-value="userPhone" 
          @update:model-value="$emit('update:userPhone', $event)"
          type="tel"
          label="Số điện thoại" 
          placeholder="0388 145 796"
          required
        />
      </div>
    </div>
  </BaseCard>
</template>

<script setup lang="ts">
import { Sparkles } from 'lucide-vue-next';
import BaseCard from '../base/BaseCard.vue';
import BaseInput from '../base/BaseInput.vue';

defineProps<{
  userName: string;
  userEmail: string;
  userPhone: string;
  authStore: any;
}>();

defineEmits<{
  (e: 'update:userName', val: string): void;
  (e: 'update:userEmail', val: string): void;
  (e: 'update:userPhone', val: string): void;
}>();
</script>
