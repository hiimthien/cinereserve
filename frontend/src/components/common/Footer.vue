<template>
  <footer class="border-t border-cinema-border bg-cinema-bg/90 backdrop-blur-md mt-20 text-cinema-muted text-xs">
    <div class="max-w-7xl mx-auto px-6 py-10 flex flex-col sm:flex-row items-center justify-between gap-6">
      
      <!-- Brand & Info -->
      <div class="flex items-center gap-3">
        <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-cinema-accent to-cinema-gold p-0.5">
          <div class="w-full h-full bg-cinema-bg rounded-[10px] flex items-center justify-center">
            <Film class="w-4 h-4 text-cinema-accent" />
          </div>
        </div>
        <div>
          <p class="font-bold text-white text-sm">CineReserve Vietnam</p>
          <p class="text-[11px]">Hệ thống đặt vé xem phim trực tuyến thời gian thực</p>
        </div>
      </div>

      <!-- Links -->
      <div class="flex items-center gap-6 font-medium">
        <router-link to="/" class="hover:text-white transition-colors">Trang chủ</router-link>
        <router-link to="/cinemas" class="hover:text-white transition-colors">Cụm rạp</router-link>
        <router-link to="/my-tickets" class="hover:text-white transition-colors">Vé của tôi</router-link>
      </div>

      <!-- Copyright -->
      <div class="text-[11px] text-slate-500 text-center sm:text-right">
        <p>© 2026 CineReserve. Dữ liệu phim từ TMDb API.</p>
        <p class="mt-0.5">Hỗ trợ thanh toán VietQR, MoMo & Thẻ quốc tế.</p>
      </div>

    </div>
  </footer>
</template>

<script setup lang="ts">
import { Film } from 'lucide-vue-next';
</script>
