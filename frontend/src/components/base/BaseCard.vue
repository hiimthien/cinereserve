<template>
  <div
    class="border backdrop-blur-md transition-all duration-300"
    :class="[
      variantClasses[variant],
      paddingClasses[padding],
      roundedClasses[rounded],
      hoverable ? 'hover:border-white/20 hover:-translate-y-1 hover:shadow-2xl cursor-pointer' : '',
    ]"
  >
    <div v-if="$slots.header || title" class="border-b border-white/5 pb-3 mb-3 flex items-center justify-between">
      <slot name="header">
        <h3 class="font-extrabold text-base text-white">{{ title }}</h3>
      </slot>
    </div>

    <slot />

    <div v-if="$slots.footer" class="border-t border-white/5 pt-3 mt-3">
      <slot name="footer" />
    </div>
  </div>
</template>

<script setup lang="ts">
interface Props {
  variant?: 'surface' | 'card' | 'glass' | 'accent';
  padding?: 'none' | 'sm' | 'md' | 'lg';
  rounded?: 'xl' | '2xl' | '3xl';
  title?: string;
  hoverable?: boolean;
}

withDefaults(defineProps<Props>(), {
  variant: 'surface',
  padding: 'md',
  rounded: '3xl',
  title: '',
  hoverable: false,
});

const variantClasses = {
  surface: 'bg-cinema-surface/70 border-cinema-border',
  card: 'bg-cinema-card/70 border-white/10',
  glass: 'bg-white/5 border-white/10 backdrop-blur-xl',
  accent: 'bg-cinema-accent/10 border-cinema-accent/30',
};

const paddingClasses = {
  none: 'p-0',
  sm: 'p-3.5',
  md: 'p-5 md:p-6',
  lg: 'p-8',
};

const roundedClasses = {
  xl: 'rounded-xl',
  '2xl': 'rounded-2xl',
  '3xl': 'rounded-3xl',
};
</script>
