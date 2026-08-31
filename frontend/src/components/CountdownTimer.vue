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
    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>
    <span>{{ store.formattedRemainingTime }}</span>
  </div>
</template>

<script setup lang="ts">
import { useBookingStore } from '../stores/bookingStore';

const store = useBookingStore();
</script>
