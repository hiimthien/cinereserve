<template>
  <div class="min-h-screen bg-[#090d16] text-slate-200 flex">
    
    <!-- Admin Sidebar -->
    <aside class="w-64 bg-[#0e1424] border-r border-white/5 flex flex-col justify-between p-5 select-none shrink-0 hidden md:flex">
      <div class="space-y-6">
        
        <!-- Logo -->
        <router-link to="/admin" class="flex items-center gap-2.5">
          <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-rose-500 to-amber-500 p-0.5 shadow-glow-accent">
            <div class="w-full h-full bg-slate-950 rounded-[10px] flex items-center justify-center">
              <Film class="w-5 h-5 text-rose-500" />
            </div>
          </div>
          <div>
            <span class="text-base font-black text-white block leading-tight">Cine<span class="text-rose-500">Admin</span></span>
            <span class="text-[9px] font-bold text-amber-400 uppercase tracking-wider">Management Portal</span>
          </div>
        </router-link>

        <!-- Navigation Menu -->
        <nav class="space-y-1 pt-2">
          <router-link 
            to="/admin" 
            class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition-all"
            :class="$route.path === '/admin' ? 'bg-rose-500/15 text-rose-400 border border-rose-500/30 shadow-glow-accent' : 'text-slate-400 hover:text-white hover:bg-white/5'"
          >
            <BarChart3 class="w-4 h-4" />
            <span>Tổng Quan & Doanh Thu</span>
          </router-link>

          <router-link 
            to="/admin/movies" 
            class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition-all"
            :class="$route.path === '/admin/movies' ? 'bg-rose-500/15 text-rose-400 border border-rose-500/30 shadow-glow-accent' : 'text-slate-400 hover:text-white hover:bg-white/5'"
          >
            <Clapperboard class="w-4 h-4" />
            <span>Quản Lý Phim</span>
          </router-link>

          <router-link 
            to="/admin/showtimes" 
            class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition-all"
            :class="$route.path === '/admin/showtimes' ? 'bg-rose-500/15 text-rose-400 border border-rose-500/30 shadow-glow-accent' : 'text-slate-400 hover:text-white hover:bg-white/5'"
          >
            <Calendar class="w-4 h-4" />
            <span>Lịch & Suất Chiếu</span>
          </router-link>

          <router-link 
            to="/admin/snacks" 
            class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition-all"
            :class="$route.path === '/admin/snacks' ? 'bg-rose-500/15 text-rose-400 border border-rose-500/30 shadow-glow-accent' : 'text-slate-400 hover:text-white hover:bg-white/5'"
          >
            <Popcorn class="w-4 h-4 text-amber-400" />
            <span>Bắp Nước & Combo</span>
          </router-link>

          <router-link 
            to="/admin/vouchers" 
            class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition-all"
            :class="$route.path === '/admin/vouchers' ? 'bg-rose-500/15 text-rose-400 border border-rose-500/30 shadow-glow-accent' : 'text-slate-400 hover:text-white hover:bg-white/5'"
          >
            <TicketPercent class="w-4 h-4 text-emerald-400" />
            <span>Mã Giảm Giá & Voucher</span>
          </router-link>

          <router-link 
            to="/admin/bookings" 
            class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition-all"
            :class="$route.path === '/admin/bookings' ? 'bg-rose-500/15 text-rose-400 border border-rose-500/30 shadow-glow-accent' : 'text-slate-400 hover:text-white hover:bg-white/5'"
          >
            <Ticket class="w-4 h-4 text-cinema-accent" />
            <span>Quản Lý Đơn Đặt Vé</span>
          </router-link>

          <router-link 
            to="/admin/rooms" 
            class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition-all"
            :class="$route.path === '/admin/rooms' ? 'bg-rose-500/15 text-rose-400 border border-rose-500/30 shadow-glow-accent' : 'text-slate-400 hover:text-white hover:bg-white/5'"
          >
            <Armchair class="w-4 h-4" />
            <span>Rạp & Ma Trận Ghế</span>
          </router-link>

          <router-link 
            to="/staff/scanner" 
            class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold text-slate-400 hover:text-white hover:bg-white/5 transition-all"
          >
            <QrCode class="w-4 h-4 text-emerald-400" />
            <span>Máy Quét Soát Vé</span>
          </router-link>
        </nav>

      </div>

      <!-- Footer / User Info -->
      <div class="pt-4 border-t border-white/5 space-y-3">
        <router-link 
          to="/" 
          class="w-full flex items-center justify-center gap-2 py-2 px-3 rounded-xl bg-white/5 hover:bg-white/10 text-xs font-semibold text-slate-300 transition-colors"
        >
          <ExternalLink class="w-3.5 h-3.5" />
          <span>Về Website Khách Hàng</span>
        </router-link>

        <div class="flex items-center gap-2.5 p-2 rounded-xl bg-slate-900/60 border border-white/5">
          <div class="w-8 h-8 rounded-lg bg-rose-500/20 text-rose-400 flex items-center justify-center font-bold text-xs">
            AD
          </div>
          <div class="min-w-0 flex-1">
            <span class="text-xs font-bold text-white block truncate">Admin Master</span>
            <span class="text-[10px] text-emerald-400 font-mono">System Online</span>
          </div>
        </div>
      </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0 overflow-y-auto">
      
      <!-- Top Bar -->
      <header class="h-16 bg-[#0e1424]/80 backdrop-blur-md border-b border-white/5 px-6 flex items-center justify-between sticky top-0 z-30">
        <div class="flex items-center gap-3">
          <h2 class="text-sm md:text-base font-extrabold text-white">
            Bảng Điều Khiển Quản Trị Hệ Thống
          </h2>
        </div>

        <div class="flex items-center gap-3">
          <router-link 
            to="/" 
            class="px-3 py-1.5 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-300 border border-rose-500/30 text-xs font-bold transition-colors flex items-center gap-1.5"
          >
            <ExternalLink class="w-3.5 h-3.5" />
            <span class="hidden sm:inline">Xem Trang Khách</span>
          </router-link>
        </div>
      </header>

      <!-- Sub Page Content -->
      <main class="flex-1 p-4 md:p-8 space-y-8">
        <router-view />
      </main>

    </div>

  </div>
</template>

<script setup lang="ts">
import { 
  Film, 
  BarChart3, 
  Clapperboard, 
  Calendar, 
  Armchair, 
  QrCode, 
  ExternalLink,
  Popcorn,
  TicketPercent,
  Ticket
} from 'lucide-vue-next';
</script>
