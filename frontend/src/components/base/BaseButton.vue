<template>
  <button
    :type="type"
    :disabled="disabled || loading"
    class="inline-flex items-center justify-center font-bold transition-all duration-300 select-none cursor-pointer focus:outline-none"
    :class="[
      // Size variants
      sizeClasses[size],
      // Color/Style variants
      variantClasses[variant],
      // States
      (disabled || loading) ? 'opacity-50 cursor-not-allowed transform-none' : 'hover:scale-[1.02] active:scale-[0.98]',
      // Full width
      block ? 'w-full' : '',
      // Rounded
      roundedClasses[rounded]
    ]"
    @click="$emit('click', $event)"
  >
    <!-- Loading Spinner -->
    <Loader2 
      v-if="loading" 
      class="animate-spin -ml-1 mr-2 h-4 w-4 text-current shrink-0" 
    />

    <!-- Optional Prefix Icon Slot -->
    <slot name="prefix" />

    <!-- Default Content Slot -->
    <slot />

    <!-- Optional Suffix Icon Slot -->
    <slot name="suffix" />
  </button>
</template>

<script setup lang="ts">
import { Loader2 } from 'lucide-vue-next';
interface Props {
  variant?: 'primary' | 'secondary' | 'gold' | 'outline' | 'ghost' | 'danger';
  size?: 'sm' | 'md' | 'lg' | 'xl';
  type?: 'button' | 'submit' | 'reset';
  disabled?: boolean;
  loading?: boolean;
  block?: boolean;
  rounded?: 'md' | 'xl' | '2xl' | 'full';
}

withDefaults(defineProps<Props>(), {
  variant: 'primary',
  size: 'md',
  type: 'button',
  disabled: false,
  loading: false,
  block: false,
  rounded: '2xl',
});

defineEmits<{
  (e: 'click', event: MouseEvent): void;
  (e: 'update:modelValue', value: any): void;
}>();

const sizeClasses = {
  sm: 'px-3 py-1.5 text-xs gap-1.5',
  md: 'px-5 py-2.5 text-xs md:text-sm gap-2',
  lg: 'px-6 py-3 text-sm font-bold gap-2',
  xl: 'px-8 py-3.5 text-base font-bold gap-2.5',
};

const variantClasses = {
  primary: 'bg-cinema-accent hover:bg-rose-600 text-white shadow-glow-accent',
  gold: 'bg-cinema-gold hover:bg-amber-400 text-slate-950 font-black shadow-glow-gold',
  secondary: 'bg-cinema-surface hover:bg-cinema-card border border-cinema-border text-slate-200 hover:text-white',
  outline: 'bg-transparent border border-white/20 hover:border-white/50 text-slate-200 hover:text-white',
  ghost: 'bg-transparent hover:bg-white/10 text-slate-300 hover:text-white',
  danger: 'bg-red-600 hover:bg-red-700 text-white',
};

const roundedClasses = {
  md: 'rounded-lg',
  xl: 'rounded-xl',
  '2xl': 'rounded-2xl',
  full: 'rounded-full',
};
</script>
