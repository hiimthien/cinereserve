<template>
  <div class="p-5 rounded-3xl bg-cinema-card/50 border border-cinema-border space-y-3">
    <div class="flex items-center justify-between text-xs">
      <h4 class="font-bold text-white flex items-center gap-1.5">
        <History class="w-4 h-4 text-cinema-gold" />
        <span>Lịch Sử Soát Vé Vừa Xong:</span>
      </h4>
      <span class="text-cinema-muted font-mono">{{ history.length }} vé</span>
    </div>

    <div v-if="history.length === 0" class="text-xs text-slate-500 italic py-2 text-center">
      Chưa có lượt quét nào trong phiên làm việc này
    </div>

    <div v-else class="space-y-2 max-h-48 overflow-y-auto pr-1">
      <div 
        v-for="(item, idx) in history" 
        :key="idx"
        class="flex items-center justify-between p-2.5 rounded-xl bg-white/5 border border-white/5 text-xs"
      >
        <div class="space-y-0.5">
          <div class="flex items-center gap-2">
            <span class="font-mono font-bold text-white">#{{ item.code }}</span>
            <span class="text-slate-400 font-medium">{{ item.name }}</span>
          </div>
          <span class="text-[10px] text-cinema-accent block font-semibold">Ghế: {{ item.seats }}</span>
        </div>
        <span class="text-[10px] font-mono text-slate-500">{{ item.time }}</span>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { History } from 'lucide-vue-next';

defineProps<{
  history: Array<{ code: string; name: string; seats: string; time: string; status: string }>;
}>();
</script>
