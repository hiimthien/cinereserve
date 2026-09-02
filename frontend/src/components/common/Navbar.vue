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
        
        <!-- Points Pill (Chỉ hiển thị cho Khách Hàng, ẩn đối với Admin & Staff) -->
        <button 
          v-if="authStore.isAuthenticated && !authStore.isAdmin && !authStore.isStaff"
          @click="authStore.openRewardModal"
          class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-amber-500/10 hover:bg-amber-500/20 border border-amber-500/30 text-xs text-amber-300 font-bold transition-all cursor-pointer shadow-sm group shrink-0"
          title="Xem điểm thưởng & Đổi voucher"
        >
          <Sparkles class="w-3.5 h-3.5 text-amber-400 group-hover:rotate-12 transition-transform" />
          <span>{{ authStore.user?.points || 0 }} Pts</span>
          <span class="text-[10px] bg-amber-500/20 px-1 py-0.2 rounded text-amber-200">Đổi Quà</span>
        </button>

        <!-- Admin Quick Link Pill (Hiển thị cho Admin) -->
        <router-link 
          v-else-if="authStore.isAdmin"
          to="/admin"
          class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/30 text-xs text-rose-300 font-bold transition-all shadow-sm group shrink-0"
          title="Mở Trang Quản Trị Hệ Thống"
        >
          <LayoutDashboard class="w-3.5 h-3.5 text-rose-400 group-hover:scale-110 transition-transform" />
          <span>Portal Admin</span>
        </router-link>

        <!-- Staff Quick Link Pill (Hiển thị cho Staff) -->
        <router-link 
          v-else-if="authStore.isStaff"
          to="/staff/scanner"
          class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-cyan-500/10 hover:bg-cyan-500/20 border border-cyan-500/30 text-xs text-cyan-300 font-bold transition-all shadow-sm group shrink-0"
          title="Mở Máy Quét Vé"
        >
          <QrCode class="w-3.5 h-3.5 text-cyan-400 group-hover:scale-110 transition-transform" />
          <span>Máy Soát Vé</span>
        </router-link>

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
                  <span class="text-xs font-bold text-white truncate max-w-[140px]">{{ authStore.user?.name }}</span>
                  <span 
                    v-if="authStore.isAdmin"
                    class="text-[9px] font-black tracking-wider text-rose-400 bg-rose-500/20 px-1.5 py-0.5 rounded uppercase"
                  >
                    👑 Admin
                  </span>
                  <span 
                    v-else-if="authStore.isStaff"
                    class="text-[9px] font-black tracking-wider text-cyan-400 bg-cyan-500/20 px-1.5 py-0.5 rounded uppercase"
                  >
                    🎫 Staff
                  </span>
                  <span 
                    v-else
                    class="text-[10px] font-mono font-bold text-amber-400 bg-amber-500/20 px-1.5 py-0.5 rounded"
                  >
                    {{ authStore.user?.points || 0 }} Pts
                  </span>
                </div>
                <p class="text-[10px] text-slate-400 truncate">{{ authStore.user?.email }}</p>
                <div v-if="!authStore.isAdmin && !authStore.isStaff" class="text-[10px] text-slate-400 pt-1">
                  Hạng: <strong class="text-white uppercase">{{ authStore.user?.membership_tier || 'Member' }}</strong>
                </div>
              </div>

              <!-- Admin Link (Only for Admin) -->
              <router-link
                v-if="authStore.isAdmin"
                to="/admin"
                @click="isUserMenuOpen = false"
                class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-bold text-rose-300 hover:bg-rose-500/20 transition-colors"
              >
                <LayoutDashboard class="w-4 h-4 text-rose-400" />
                <span>Quản Trị Hệ Thống (Admin)</span>
              </router-link>

              <!-- Staff Scanner Link (For Staff & Admin) -->
              <router-link
                v-if="authStore.isStaff"
                to="/staff/scanner"
                @click="isUserMenuOpen = false"
                class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-bold text-cyan-300 hover:bg-cyan-500/20 transition-colors"
              >
                <QrCode class="w-4 h-4 text-cyan-400" />
                <span>Máy Quét Vé Soát Vé (Staff)</span>
              </router-link>

              <!-- Rewards Center Link (Chỉ dành cho Khách Hàng) -->
              <button
                v-if="!authStore.isAdmin && !authStore.isStaff"
                @click="() => { isUserMenuOpen = false; authStore.openRewardModal(); }"
                class="w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-bold text-amber-300 hover:bg-amber-500/20 transition-colors cursor-pointer text-left"
              >
                <Gift class="w-4 h-4 text-amber-400" />
                <span>Đổi Điểm Thưởng & Quà Tặng</span>
              </button>

              <!-- My Tickets Link (Cho Khách Hàng và Admin kiểm tra) -->
              <router-link
                v-if="!authStore.isStaff"
                to="/my-tickets"
                @click="isUserMenuOpen = false"
                class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold text-slate-300 hover:text-white hover:bg-white/5 transition-colors"
              >
                <Ticket class="w-4 h-4 text-slate-400" />
                <span>Vé Đã Đặt Của Tôi</span>
              </router-link>

              <!-- Change Password Button -->
              <button
                @click="() => { isUserMenuOpen = false; authStore.openChangePasswordModal(); }"
                class="w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold text-slate-300 hover:text-white hover:bg-white/5 transition-colors cursor-pointer text-left"
              >
                <KeyRound class="w-4 h-4 text-slate-400" />
                <span>Đổi Mật Khẩu</span>
              </button>

              <!-- Logout Button -->
              <div class="pt-1 border-t border-white/5">
                <button
                  @click="handleLogout"
                  class="w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold text-rose-400 hover:bg-rose-500/10 transition-colors cursor-pointer text-left"
                >
                  <LogOut class="w-4 h-4" />
                  <span>Đăng Xuất</span>
                </button>
              </div>
            </div>
          </div>
        </template>

        <!-- Unauthenticated: Login / Register Button -->
        <template v-else>
          <button 
            @click="authStore.openAuth('login')"
            class="flex items-center gap-2 px-4 py-2 rounded-full bg-gradient-to-r from-cinema-accent to-rose-600 hover:from-rose-600 hover:to-cinema-accent text-white text-xs font-bold transition-all cursor-pointer shadow-glow-accent hover:scale-105 active:scale-95"
          >
            <User class="w-3.5 h-3.5" />
            <span>Đăng Nhập</span>
          </button>
        </template>

        <!-- Mobile Menu Toggle Button -->
        <button 
          @click="isMobileMenuOpen = !isMobileMenuOpen"
          class="lg:hidden p-2 rounded-xl bg-cinema-surface border border-cinema-border text-slate-300 hover:text-white transition-colors"
          aria-label="Mở menu"
        >
          <Menu class="w-5 h-5" />
        </button>

      </div>

    </div>

    <!-- Mobile Drawer Menu -->
    <div 
      v-if="isMobileMenuOpen"
      class="lg:hidden border-t border-cinema-border bg-cinema-bg/98 px-4 py-4 space-y-2 backdrop-blur-xl animate-in fade-in"
    >
      <router-link 
        to="/cinemas" 
        @click="isMobileMenuOpen = false"
        class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold text-slate-300 hover:text-white hover:bg-white/5"
      >
        <Building2 class="w-4 h-4 text-slate-400" />
        <span>Lịch Chiếu & Cụm Rạp</span>
      </router-link>

      <router-link 
        to="/now-showing" 
        @click="isMobileMenuOpen = false"
        class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold text-slate-300 hover:text-white hover:bg-white/5"
      >
        <Flame class="w-4 h-4 text-orange-400" />
        <span>Phim Đang Chiếu</span>
      </router-link>

      <router-link 
        to="/coming-soon" 
        @click="isMobileMenuOpen = false"
        class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold text-slate-300 hover:text-white hover:bg-white/5"
      >
        <Sparkles class="w-4 h-4 text-cinema-gold" />
        <span>Phim Sắp Chiếu</span>
      </router-link>

      <router-link 
        to="/my-tickets" 
        @click="isMobileMenuOpen = false"
        class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold text-slate-300 hover:text-white hover:bg-white/5"
      >
        <Ticket class="w-4 h-4 text-cinema-accent" />
        <span>Vé Của Tôi</span>
      </router-link>

      <div v-if="authStore.isAdmin" class="pt-2 border-t border-white/5">
        <router-link 
          to="/admin" 
          @click="isMobileMenuOpen = false"
          class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold text-rose-400 bg-rose-500/10"
        >
          <LayoutDashboard class="w-4 h-4" />
          <span>Trang Quản Trị (Admin)</span>
        </router-link>
      </div>
    </div>
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
  LogOut,
  KeyRound
} from 'lucide-vue-next';

import { useBookingStore } from '../../stores/bookingStore';
import { useAuthStore } from '../../stores/authStore';

const store = useBookingStore();
const authStore = useAuthStore();
const isMobileMenuOpen = ref(false);
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
