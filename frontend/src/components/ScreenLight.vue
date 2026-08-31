<template>
  <div class="relative w-full max-w-2xl mx-auto mb-10 text-center">
    <!-- Cinema Screen Glow effect -->
    <div class="h-8 w-full cinema-screen-curve bg-gradient-to-b from-amber-500/20 via-amber-500/5 to-transparent border-t border-amber-500/40"></div>
    
    <!-- Light flare projection -->
    <div class="absolute inset-x-0 -top-4 flex justify-center">
      <div class="w-3/4 h-12 bg-amber-400/15 blur-2xl rounded-full pointer-events-none"></div>
    </div>

    <!-- Screen label -->
    <span class="inline-block mt-3 text-xs tracking-[0.3em] font-semibold text-cinema-muted uppercase">
      Màn hình / Screen
    </span>
  </div>
</template>

<script setup lang="ts">
</script>
