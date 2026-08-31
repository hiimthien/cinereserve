<template>
  <div class="flex flex-wrap items-center justify-center gap-6 py-3 px-6 bg-cinema-surface/60 backdrop-blur-md rounded-2xl border border-cinema-border max-w-xl mx-auto my-6 text-xs text-cinema-muted">
    <!-- Available -->
    <div class="flex items-center gap-2">
      <div class="w-4 h-4 rounded bg-seat-available border border-slate-600/50"></div>
      <span>Ghế trống</span>
    </div>

    <!-- Selected -->
    <div class="flex items-center gap-2">
      <div class="w-4 h-4 rounded bg-seat-selected shadow-glow-green"></div>
      <span class="text-emerald-400 font-medium">Đang chọn</span>
    </div>

    <!-- Holding -->
    <div class="flex items-center gap-2">
      <div class="w-4 h-4 rounded bg-seat-holding shadow-glow-gold animate-holding"></div>
      <span class="text-amber-400 font-medium">Đang giữ chỗ</span>
    </div>

    <!-- Booked -->
    <div class="flex items-center gap-2">
      <div class="w-4 h-4 rounded bg-seat-booked border border-seat-booked-border"></div>
      <span>Đã bán</span>
    </div>

    <!-- VIP -->
    <div class="flex items-center gap-2 pl-2 border-l border-white/10">
      <div class="w-4 h-4 rounded bg-seat-available border-2 border-amber-500"></div>
      <span class="text-amber-400">VIP</span>
    </div>
  </div>
</template>

<script setup lang="ts">
</script>
