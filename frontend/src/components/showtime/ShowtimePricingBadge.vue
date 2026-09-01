<template>
  <span 
    v-if="pricing.badge"
    class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-wider shadow-sm select-none"
    :class="[
      pricing.badgeVariant === 'emerald' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 shadow-emerald-500/10' :
      pricing.badgeVariant === 'rose' ? 'bg-rose-500/20 text-rose-300 border border-rose-500/40 shadow-rose-500/10' :
      'bg-amber-500/20 text-amber-300 border border-amber-500/40 shadow-amber-500/10'
    ]"
  >
    <Sparkles v-if="pricing.iconName === 'Sparkles'" class="w-2.5 h-2.5 text-emerald-400" />
    <Flame v-else-if="pricing.iconName === 'Flame'" class="w-2.5 h-2.5 text-rose-400" />
    <Moon v-else-if="pricing.iconName === 'Moon'" class="w-2.5 h-2.5 text-amber-400" />
    <span>{{ pricing.badge }}</span>
  </span>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Sparkles, Flame, Moon } from 'lucide-vue-next';
import { useDynamicPricing } from '../../composables/useDynamicPricing';
import type { Showtime } from '../../types';

const props = defineProps<{
  showtime: Showtime;
  targetDate?: string;
}>();

const { getDynamicPricing } = useDynamicPricing();

const pricing = computed(() => {
  return getDynamicPricing(props.showtime, props.targetDate);
});
</script>
