<template>
  <span
    class="inline-flex items-center justify-center gap-1.5 font-bold select-none whitespace-nowrap leading-none shrink-0"
    :class="[
      sizeClasses[size],
      variantClasses[variant],
      roundedClasses[rounded],
      glow ? 'shadow-glow-accent' : ''
    ]"
  >
    <slot name="prefix" />
    <slot />
    <slot name="suffix" />
  </span>
</template>

<script setup lang="ts">
interface Props {
  variant?: 'accent' | 'gold' | 'emerald' | 'cyan' | 'purple' | 'blue' | 'rose' | 'amber' | 'neutral' | 'outline';
  size?: 'xs' | 'sm' | 'md';
  rounded?: 'sm' | 'md' | 'xl' | 'full';
  glow?: boolean;
}

withDefaults(defineProps<Props>(), {
  variant: 'neutral',
  size: 'sm',
  rounded: 'full',
  glow: false,
});

const sizeClasses = {
  xs: 'px-2 py-0.5 text-[10px]',
  sm: 'px-3 py-1.5 text-xs',
  md: 'px-3.5 py-1.5 text-xs md:text-sm',
};

const variantClasses = {
  accent: 'bg-cinema-accent/20 text-cinema-accent border border-cinema-accent/30',
  gold: 'bg-amber-500/20 text-amber-300 border border-amber-500/30',
  emerald: 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30',
  cyan: 'bg-cyan-500/20 text-cyan-300 border border-cyan-500/30',
  purple: 'bg-purple-500/20 text-purple-300 border border-purple-500/40 shadow-glow-accent',
  blue: 'bg-blue-500/20 text-blue-300 border border-blue-500/30',
  rose: 'bg-rose-500/15 text-rose-300 border border-rose-500/30',
  amber: 'bg-amber-500/15 text-amber-300 border border-amber-500/30',
  neutral: 'bg-white/10 text-slate-300 border border-white/10',
  outline: 'bg-transparent text-slate-300 border border-white/20',
};

const roundedClasses = {
  sm: 'rounded-md',
  md: 'rounded-lg',
  xl: 'rounded-xl',
  full: 'rounded-full',
};
</script>
