<template>
  <header class="sticky top-0 z-40 bg-cinema-bg/85 backdrop-blur-xl border-b border-cinema-border">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">
      
      <!-- Brand Logo -->
      <router-link to="/" class="flex items-center gap-2.5 group">
        <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-cinema-accent to-cinema-gold p-0.5 shadow-glow-accent group-hover:scale-105 transition-transform">
          <div class="w-full h-full bg-cinema-bg rounded-[10px] flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-cinema-accent fill-current" viewBox="0 0 24 24"><path d="M18 4l2 4h-3l-2-4h-2l2 4h-3l-2-4H8l2 4H7L5 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V4h-4z"/></svg>
          </div>
        </div>
        <span class="text-xl font-black tracking-tight text-white">
          Cine<span class="text-cinema-accent">Reserve</span>
        </span>
      </router-link>

      <!-- Nav Links -->
      <nav class="hidden md:flex items-center gap-6 text-sm font-semibold text-slate-300">
        <router-link 
          to="/" 
          class="transition-colors hover:text-cinema-accent"
          active-class="text-cinema-accent"
        >
          Trang chủ
        </router-link>
        <a href="#movies-section" class="transition-colors hover:text-cinema-accent">Phim đang chiếu</a>
        <a href="#coming-soon" class="transition-colors hover:text-cinema-accent">Phim sắp chiếu</a>
        <router-link 
          to="/my-tickets" 
          class="transition-colors hover:text-cinema-accent flex items-center gap-1.5"
          active-class="text-cinema-accent"
        >
          <span>Vé của tôi</span>
          <span v-if="store.activeTicket" class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
        </router-link>
      </nav>

      <!-- Right User Actions -->
      <div class="flex items-center gap-3">
        <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-full bg-cinema-surface border border-cinema-border text-xs text-cinema-muted">
          <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
          <span>TP. Hồ Chí Minh</span>
        </div>

        <div class="w-8 h-8 rounded-full bg-slate-800 border border-white/10 flex items-center justify-center font-bold text-xs text-cinema-gold">
          T
        </div>
      </div>

    </div>
  </header>
</template>

<script setup lang="ts">
import { useBookingStore } from '../stores/bookingStore';

const store = useBookingStore();
</script>
