<template>
  <div class="min-h-screen bg-[#090d16] text-slate-200 flex">
    
    <!-- Desktop Admin Sidebar -->
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
            v-for="item in navItems"
            :key="item.to"
            :to="item.to" 
            class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition-all"
            :class="$route.path === item.to ? 'bg-rose-500/15 text-rose-400 border border-rose-500/30 shadow-glow-accent' : 'text-slate-400 hover:text-white hover:bg-white/5'"
          >
            <component :is="item.icon" class="w-4 h-4" :class="item.iconColor" />
            <span>{{ item.label }}</span>
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
            <ShieldCheck class="w-4 h-4 text-rose-400" />
          </div>
          <div class="min-w-0 flex-1">
            <span class="text-xs font-bold text-white block truncate">Quản Trị Viên</span>
            <span class="text-[10px] text-emerald-400 font-mono">System Online</span>
          </div>
        </div>
      </div>
    </aside>

    <!-- Mobile Drawer & Sidebar (Teleported to Body) -->
    <Teleport to="body">
      <!-- Backdrop -->
      <transition 
        enter-active-class="transition-opacity duration-300"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition-opacity duration-200"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
      >
        <div 
          v-if="isMobileSidebarOpen"
          @click="isMobileSidebarOpen = false"
          class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 md:hidden"
        ></div>
      </transition>

      <!-- Slide-over Drawer Panel -->
      <transition 
        enter-active-class="transition-transform duration-300 ease-out"
        enter-from-class="-translate-x-full"
        enter-to-class="translate-x-0"
        leave-active-class="transition-transform duration-200 ease-in"
        leave-from-class="translate-x-0"
        leave-to-class="-translate-x-full"
      >
        <aside 
          v-if="isMobileSidebarOpen"
          class="fixed inset-y-0 left-0 w-72 bg-[#0e1424] border-r border-white/10 flex flex-col justify-between p-5 select-none z-50 md:hidden shadow-2xl overflow-y-auto"
        >
          <div class="space-y-6">
            <!-- Mobile Header with Close Button -->
            <div class="flex items-center justify-between pb-4 border-b border-white/5">
              <router-link to="/admin" @click="isMobileSidebarOpen = false" class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-rose-500 to-amber-500 p-0.5">
                  <div class="w-full h-full bg-slate-950 rounded-[10px] flex items-center justify-center">
                    <Film class="w-4 h-4 text-rose-500" />
                  </div>
                </div>
                <div>
                  <span class="text-sm font-black text-white block leading-tight">Cine<span class="text-rose-500">Admin</span></span>
                  <span class="text-[8px] font-bold text-amber-400 uppercase tracking-wider">Management Portal</span>
                </div>
              </router-link>

              <button 
                @click="isMobileSidebarOpen = false"
                class="p-2 rounded-xl bg-white/5 hover:bg-white/10 text-slate-400 hover:text-white transition-colors cursor-pointer"
                aria-label="Đóng menu"
              >
                <X class="w-4 h-4" />
              </button>
            </div>

            <!-- Mobile Navigation Menu -->
            <nav class="space-y-1">
              <router-link 
                v-for="item in navItems"
                :key="item.to"
                :to="item.to" 
                @click="isMobileSidebarOpen = false"
                class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition-all"
                :class="$route.path === item.to ? 'bg-rose-500/15 text-rose-400 border border-rose-500/30 shadow-glow-accent' : 'text-slate-400 hover:text-white hover:bg-white/5'"
              >
                <component :is="item.icon" class="w-4 h-4" :class="item.iconColor" />
                <span>{{ item.label }}</span>
              </router-link>
            </nav>
          </div>

          <!-- Mobile Footer -->
          <div class="pt-4 border-t border-white/5 space-y-3 mt-6">
            <router-link 
              to="/" 
              @click="isMobileSidebarOpen = false"
              class="w-full flex items-center justify-center gap-2 py-2 px-3 rounded-xl bg-white/5 hover:bg-white/10 text-xs font-semibold text-slate-300 transition-colors"
            >
              <ExternalLink class="w-3.5 h-3.5" />
              <span>Về Website Khách Hàng</span>
            </router-link>
          </div>
        </aside>
      </transition>
    </Teleport>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0 overflow-y-auto">
      
      <!-- Top Bar with Hamburger for Mobile -->
      <header class="h-16 bg-[#0e1424]/80 backdrop-blur-md border-b border-white/5 px-4 md:px-6 flex items-center justify-between sticky top-0 z-30">
        <div class="flex items-center gap-3">
          <!-- Mobile Hamburger Button -->
          <button 
            @click="isMobileSidebarOpen = true"
            class="md:hidden p-2 rounded-xl bg-white/5 border border-white/10 text-slate-300 hover:text-white transition-colors cursor-pointer"
            aria-label="Mở menu quản trị"
          >
            <Menu class="w-5 h-5 text-rose-400" />
          </button>

          <h2 class="text-sm md:text-base font-extrabold text-white truncate">
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
import { ref } from 'vue';
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
  Ticket,
  Building2,
  Users,
  Menu,
  X,
  ShieldCheck
} from 'lucide-vue-next';

const isMobileSidebarOpen = ref(false);

const navItems = [
  { to: '/admin', label: 'Tổng Quan & Doanh Thu', icon: BarChart3, iconColor: '' },
  { to: '/admin/movies', label: 'Quản Lý Phim', icon: Clapperboard, iconColor: '' },
  { to: '/admin/showtimes', label: 'Lịch & Suất Chiếu', icon: Calendar, iconColor: '' },
  { to: '/admin/snacks', label: 'Bắp Nước & Combo', icon: Popcorn, iconColor: 'text-amber-400' },
  { to: '/admin/vouchers', label: 'Mã Giảm Giá & Voucher', icon: TicketPercent, iconColor: 'text-emerald-400' },
  { to: '/admin/bookings', label: 'Quản Lý Đơn Đặt Vé', icon: Ticket, iconColor: 'text-cinema-accent' },
  { to: '/admin/users', label: 'Người Dùng & Quyền', icon: Users, iconColor: 'text-indigo-400' },
  { to: '/admin/cinemas', label: 'Cụm Rạp Chiếu', icon: Building2, iconColor: 'text-cyan-400' },
  { to: '/admin/rooms', label: 'Phòng & Ma Trận Ghế', icon: Armchair, iconColor: '' },
  { to: '/staff/scanner', label: 'Máy Quét Soát Vé', icon: QrCode, iconColor: 'text-emerald-400' },
];
</script>
