<template>
  <div class="inline-flex flex-col items-center justify-center gap-3">
    <div 
      class="relative flex items-center justify-center"
      :class="sizeClasses[size]"
    >
      <!-- Outer glowing ring -->
      <div 
        class="w-full h-full rounded-full border-2 border-cinema-accent/20 border-t-cinema-accent animate-spin shadow-glow-accent"
      ></div>

      <!-- Inner gold ring -->
      <div 
        class="absolute w-2/3 h-2/3 rounded-full border-2 border-cinema-gold/20 border-b-cinema-gold animate-spin-reverse"
      ></div>

      <!-- Center dot -->
      <div class="absolute w-2 h-2 rounded-full bg-white shadow-glow-white animate-pulse"></div>
    </div>

    <p v-if="text" class="text-xs font-semibold text-cinema-muted animate-pulse">
      {{ text }}
    </p>
  </div>
</template>

<script setup lang="ts">
interface Props {
  size?: 'sm' | 'md' | 'lg' | 'xl';
  text?: string;
}

withDefaults(defineProps<Props>(), {
  size: 'md',
  text: '',
});

const sizeClasses = {
  sm: 'w-6 h-6',
  md: 'w-10 h-10',
  lg: 'w-14 h-14',
  xl: 'w-20 h-20',
};
</script>

<style scoped>
@keyframes spin-reverse {
  from {
    transform: rotate(360deg);
  }
  to {
    transform: rotate(0deg);
  }
}
.animate-spin-reverse {
  animation: spin-reverse 1.2s linear infinite;
}
</style>
