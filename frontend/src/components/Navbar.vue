<template>
  <header class="sticky top-0 z-40 bg-cinema-bg/95 backdrop-blur-xl border-b border-cinema-border select-none">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">
      
      <!-- Brand Logo -->
      <router-link to="/" class="flex items-center gap-2.5 group shrink-0">
        <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-cinema-accent to-cinema-gold p-0.5 shadow-glow-accent group-hover:scale-105 transition-transform">
          <div class="w-full h-full bg-cinema-bg rounded-[10px] flex items-center justify-center">
            <Clapperboard class="w-5 h-5 text-cinema-accent" />
          </div>
        </div>
        <span class="text-xl font-black tracking-tight text-white">
          Cine<span class="text-cinema-accent">Reserve</span>
        </span>
      </router-link>

      <!-- Clean Central Nav Links (Desktop) -->
      <nav class="hidden lg:flex items-center gap-6 text-sm font-semibold text-slate-300">
        <router-link 
          to="/cinemas" 
          class="transition-colors hover:text-cinema-accent flex items-center gap-1.5"
          active-class="text-cinema-accent font-bold"
        >
          <Building2 class="w-4 h-4 text-slate-400" />
          <span>Lịch Chiếu & Rạp</span>
        </router-link>

        <router-link 
          to="/now-showing" 
          class="transition-colors hover:text-cinema-accent flex items-center gap-1.5"
          active-class="text-cinema-accent font-bold"
        >
          <Flame class="w-4 h-4 text-orange-400" />
          <span>Phim Đang Chiếu</span>
        </router-link>

        <router-link 
          to="/coming-soon" 
          class="transition-colors hover:text-cinema-gold flex items-center gap-1.5"
          active-class="text-cinema-gold font-bold"
        >
          <Sparkles class="w-4 h-4 text-cinema-gold" />
          <span>Phim Sắp Chiếu</span>
        </router-link>

        <router-link 
          to="/my-tickets" 
          class="transition-colors hover:text-cinema-accent flex items-center gap-1.5"
          active-class="text-cinema-accent font-bold"
        >
          <Ticket class="w-4 h-4" />
          <span>Vé Của Tôi</span>
          <span v-if="store.activeTicket" class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
        </router-link>
      </nav>

      <!-- Right User Hub -->
      <div class="flex items-center gap-2 sm:gap-3">
        
        <!-- Points Pill (Click opens Rewards Modal) -->
        <button 
          v-if="authStore.isAuthenticated"
          @click="authStore.openRewardModal"
          class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-amber-500/10 hover:bg-amber-500/20 border border-amber-500/30 text-xs text-amber-300 font-bold transition-all cursor-pointer shadow-sm group shrink-0"
          title="Xem điểm thưởng & Đổi voucher"
        >
          <Sparkles class="w-3.5 h-3.5 text-amber-400 group-hover:rotate-12 transition-transform" />
          <span>{{ authStore.user?.points || 0 }} Pts</span>
          <span class="text-[10px] bg-amber-500/20 px-1 py-0.2 rounded text-amber-200">Đổi Quà</span>
        </button>

        <!-- User Profile Dropdown Pill -->
        <template v-if="authStore.isAuthenticated">
          <div class="relative" ref="dropdownRef">
            <button 
              @click.stop="isUserMenuOpen = !isUserMenuOpen"
              class="flex items-center gap-2 p-1.5 pr-3 rounded-full bg-cinema-surface border border-cinema-border hover:border-white/20 transition-all cursor-pointer shadow-sm"
            >
              <img 
                :src="authStore.user?.avatar || 'https://api.dicebear.com/7.x/bottts/svg?seed=User'" 
                class="w-7 h-7 rounded-full bg-slate-800 object-cover border border-white/10 shrink-0" 
              />
              <div class="text-left hidden sm:block">
                <span class="text-xs font-bold text-white block leading-none truncate max-w-[90px]">
                  {{ authStore.user?.name?.split(' ')[0] || 'User' }}
                </span>
                <span class="text-[9px] font-bold block leading-none mt-0.5" :class="authStore.isAdmin ? 'text-rose-400' : 'text-amber-400'">
                  {{ authStore.isAdmin ? '👑 Admin' : authStore.isStaff ? '🎟️ Staff' : '🥈 Member' }}
                </span>
              </div>
              <ChevronDown class="w-3.5 h-3.5 text-slate-400 transition-transform" :class="{ 'rotate-180': isUserMenuOpen }" />
            </button>

            <!-- Dropdown Menu Panel -->
            <div 
              v-if="isUserMenuOpen"
              class="absolute right-0 mt-2 w-64 rounded-2xl bg-[#0f172a] border border-white/10 backdrop-blur-2xl shadow-2xl p-2 space-y-1.5 z-50 animate-in fade-in duration-150"
            >
              <!-- User Info Header -->
              <div class="p-3 rounded-xl bg-white/5 border border-white/5 space-y-1">
                <div class="flex items-center justify-between">
                  <span class="text-xs font-extrabold text-white truncate">{{ authStore.user?.name }}</span>
                  <span class="text-[9px] font-bold px-1.5 py-0.2 rounded" :class="authStore.isAdmin ? 'bg-rose-500/20 text-rose-300' : 'bg-amber-500/20 text-amber-300'">
                    {{ authStore.user?.role?.toUpperCase() || 'USER' }}
                  </span>
                </div>
                <p class="text-[11px] text-cinema-muted truncate">{{ authStore.user?.email }}</p>
              </div>

              <!-- Admin Portal Link (If Admin) -->
              <router-link 
                v-if="authStore.isAdmin"
                to="/admin" 
                @click="isUserMenuOpen = false"
                class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-bold text-rose-400 hover:bg-rose-500/10 transition-colors"
              >
                <LayoutDashboard class="w-4 h-4 text-rose-400" />
                <span>Bảng Quản Trị CineAdmin</span>
              </router-link>

              <!-- Staff Scanner Link (If Staff/Admin) -->
              <router-link 
                v-if="authStore.isStaff"
                to="/staff/scanner" 
                @click="isUserMenuOpen = false"
                class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-bold text-emerald-400 hover:bg-emerald-500/10 transition-colors"
              >
                <QrCode class="w-4 h-4 text-emerald-400" />
                <span>Máy Quét Soát Vé QR</span>
              </router-link>

              <!-- Loyalty Rewards -->
              <button 
                @click="authStore.openRewardModal(); isUserMenuOpen = false;"
                class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-bold text-amber-300 hover:bg-amber-500/10 transition-colors cursor-pointer"
              >
                <span class="flex items-center gap-2.5">
                  <Gift class="w-4 h-4 text-amber-400" />
                  <span>Đổi Điểm Thưởng VIP</span>
                </span>
                <span class="font-mono text-amber-400">{{ authStore.user?.points || 0 }} Pts</span>
              </button>

              <!-- My Tickets -->
              <router-link 
                to="/my-tickets" 
                @click="isUserMenuOpen = false"
                class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-bold text-slate-300 hover:bg-white/5 hover:text-white transition-colors"
              >
                <Ticket class="w-4 h-4" />
                <span>Vé Của Tôi</span>
              </router-link>

              <!-- Logout Item -->
              <div class="pt-1 border-t border-white/5">
                <button 
                  @click="handleLogout"
                  class="w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-bold text-rose-400 hover:bg-rose-500/10 transition-colors cursor-pointer"
                >
                  <LogOut class="w-4 h-4" />
                  <span>Đăng Xuất</span>
                </button>
              </div>


            </div>
          </div>
        </template>


        <!-- Login Button if guest -->
        <template v-else>
          <button 
            @click="authStore.openAuth('login')"
            class="flex items-center gap-2 px-4 py-2 rounded-2xl bg-cinema-accent hover:bg-rose-600 text-white font-bold text-xs shadow-glow-accent transition-all cursor-pointer"
          >
            <User class="w-4 h-4" />
            <span>Đăng Nhập</span>
          </button>
        </template>

        <!-- Mobile Hamburger Button -->
        <button 
          @click="isSidebarOpen = true"
          class="lg:hidden w-9 h-9 rounded-xl bg-cinema-surface border border-cinema-border flex items-center justify-center text-slate-300 hover:text-white hover:border-white/20 transition-colors cursor-pointer shrink-0"
          aria-label="Mở menu"
        >
          <Menu class="w-5 h-5" />
        </button>
      </div>

    </div>

    <!-- Responsive Sidebar Drawer -->
    <Sidebar 
      :is-open="isSidebarOpen" 
      @close="isSidebarOpen = false" 
    />

    <!-- Global Auth Modal -->
    <AuthModal />

    <!-- Global Loyalty & Reward Center Modal -->
    <LoyaltyRewardModal />
  </header>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { 
  Clapperboard, 
  Building2, 
  Flame, 
  Sparkles, 
  Ticket, 
  Menu,
  User,
  LayoutDashboard,
  QrCode,
  Gift,
  ChevronDown,
  LogOut
} from 'lucide-vue-next';

import { useBookingStore } from '../stores/bookingStore';
import { useAuthStore } from '../stores/authStore';
import Sidebar from './Sidebar.vue';
import AuthModal from './AuthModal.vue';
import LoyaltyRewardModal from './LoyaltyRewardModal.vue';

const store = useBookingStore();
const authStore = useAuthStore();
const isSidebarOpen = ref(false);
const isUserMenuOpen = ref(false);
const dropdownRef = ref<HTMLElement | null>(null);

const handleLogout = async () => {
  isUserMenuOpen.value = false;
  await authStore.logout();
};

const handleDocumentClick = (e: MouseEvent) => {
  if (dropdownRef.value && !dropdownRef.value.contains(e.target as Node)) {
    isUserMenuOpen.value = false;
  }
};

onMounted(() => {
  document.addEventListener('click', handleDocumentClick);
});

onUnmounted(() => {
  document.removeEventListener('click', handleDocumentClick);
});
</script>
