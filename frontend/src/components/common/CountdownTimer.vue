<template>
  <div 
    v-if="store.isTimerActive"
    class="flex items-center gap-2 px-3 py-1.5 rounded-xl border transition-all duration-300 font-mono text-sm"
    :class="[
      store.isTimeCritical 
        ? 'bg-red-500/20 border-red-500/50 text-red-400 animate-pulse' 
        : 'bg-amber-500/10 border-amber-500/30 text-amber-300'
    ]"
  >
    <Clock class="w-4 h-4" />
    <span>{{ store.formattedRemainingTime }}</span>
  </div>
</template>

<script setup lang="ts">
import { Clock } from 'lucide-vue-next';
import { useBookingStore } from '../../stores/bookingStore';

const store = useBookingStore();
</script>
