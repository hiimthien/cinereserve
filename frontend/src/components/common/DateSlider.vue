<template>
  <div class="space-y-2">
    <div v-if="label" class="flex items-center gap-1.5 text-xs font-bold text-slate-400 uppercase tracking-wider">
      <Calendar class="w-3.5 h-3.5 text-cinema-accent" />
      <span>{{ label }}</span>
    </div>

    <div class="flex items-center gap-3 overflow-x-auto pb-2 scrollbar-none">
      <button 
        v-for="d in days" 
        :key="d.date"
        @click="$emit('update:modelValue', d.date)"
        class="flex flex-col items-center justify-center p-3 rounded-2xl border min-w-[85px] transition-all cursor-pointer select-none"
        :class="[
          modelValue === d.date
            ? 'bg-cinema-accent border-cinema-accent text-white shadow-glow-accent scale-105'
            : 'bg-cinema-surface/60 border-cinema-border text-slate-400 hover:border-slate-500 hover:text-white'
        ]"
      >
        <span class="text-[10px] uppercase font-bold tracking-wider">{{ d.dayLabel }}</span>
        <span class="text-lg font-black my-0.5">{{ d.dayNumber }}</span>
        <span class="text-[10px] font-medium opacity-80">{{ d.monthLabel }}</span>
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Calendar } from 'lucide-vue-next';

const props = withDefaults(defineProps<{
  modelValue: string;
  label?: string;
  count?: number;
}>(), {
  label: '',
  count: 7,
});

defineEmits<{
  (e: 'update:modelValue', date: string): void;
}>();

const days = computed(() => {
  const list = [];
  const dayNames = ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'];
  for (let i = 0; i < props.count; i++) {
    const d = new Date();
    d.setDate(d.getDate() + i);
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    const dateStr = `${year}-${month}-${day}`;
    const dayOfWeek = d.getDay();
    const dayLabel = i === 0 ? 'Hôm nay' : dayNames[dayOfWeek];
    const dayNumber = day;
    const monthLabel = `Th${d.getMonth() + 1}`;
    list.push({ date: dateStr, dayLabel, dayNumber, monthLabel });
  }
  return list;
});
</script>
