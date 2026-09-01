<template>
  <Teleport to="body">
    <!-- Backdrop overlay -->
    <Transition name="sidebar-fade">
      <div 
        v-if="isOpen"
        class="fixed inset-0 z-50 bg-black/80 backdrop-blur-md"
        @click="close"
      ></div>
    </Transition>

    <!-- Slide-over Drawer Panel -->
    <Transition name="sidebar-slide">
      <aside 
        v-if="isOpen"
        class="fixed top-0 right-0 bottom-0 z-50 w-80 max-w-[85vw] bg-cinema-surface/95 border-l border-cinema-border backdrop-blur-2xl shadow-2xl flex flex-col justify-between overflow-y-auto select-none"
        role="dialog"
        aria-modal="true"
      >
        <!-- Header -->
        <div class="p-6 border-b border-white/10 flex items-center justify-between">
          <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-cinema-accent to-cinema-gold p-0.5 shadow-glow-accent">
              <div class="w-full h-full bg-cinema-bg rounded-[10px] flex items-center justify-center">
                <Clapperboard class="w-4 h-4 text-cinema-accent" />
              </div>
            </div>
            <span class="font-black text-lg text-white">
              Cine<span class="text-cinema-accent">Reserve</span>
            </span>
          </div>

          <!-- Close Button -->
          <button 
            @click="close"
            class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-slate-300 hover:text-white flex items-center justify-center transition-colors cursor-pointer"
            aria-label="Đóng menu"
          >
            <X class="w-4 h-4" />
          </button>
        </div>

        <!-- Body: Nav Links & Quick Settings -->
        <div class="p-6 space-y-6 flex-1">
          
          <!-- User Profile Card -->
          <div 
            v-if="authStore.isAuthenticated"
            @click="authStore.openRewardModal(); close();"
            class="p-4 rounded-2xl bg-cinema-card/80 border border-amber-500/20 hover:border-amber-500/40 transition-all cursor-pointer space-y-3"
          >
            <div class="flex items-center gap-3">
              <img 
                :src="authStore.user?.avatar || 'https://api.dicebear.com/7.x/bottts/svg?seed=User'" 
                class="w-11 h-11 rounded-xl bg-slate-800 object-cover border border-amber-400/40 shadow-md"
              />
              <div class="space-y-0.5 min-w-0 flex-1">
                <h4 class="text-sm font-extrabold text-white truncate">{{ authStore.user?.name }}</h4>
                <div class="flex items-center gap-1.5">
                  <span class="text-[11px] font-bold text-amber-400">
                    {{ authStore.user?.membership_tier === 'diamond' ? '💎 CineDiamond' : authStore.user?.membership_tier === 'vip' ? '👑 CineVIP' : '🥈 CineMember' }}
                  </span>
                  <span>•</span>
                  <span class="text-[11px] font-mono text-emerald-400 font-bold">{{ authStore.user?.points || 0 }} Pts</span>
                </div>
              </div>
            </div>

            <div class="flex items-center justify-between pt-2 border-t border-white/5 text-xs text-amber-300 font-bold">
              <span>Đổi voucher quà tặng</span>
              <span>→</span>
            </div>
          </div>

          <!-- Login CTA if guest -->
          <div v-else class="p-4 rounded-2xl bg-cinema-card/60 border border-white/10 text-center space-y-3">
            <p class="text-xs text-slate-300 font-semibold">Đăng nhập để tích điểm & nhận voucher 30K</p>
            <button 
              @click="authStore.openAuth('login'); close();"
              class="w-full py-2.5 rounded-xl bg-cinema-accent hover:bg-rose-600 text-white font-bold text-xs shadow-glow-accent cursor-pointer transition-colors"
            >
              Đăng Nhập / Đăng Ký
            </button>
          </div>

          <!-- Navigation Links -->
          <div class="space-y-1.5">
            <span class="text-[10px] font-bold text-cinema-muted uppercase tracking-widest px-3 block mb-2">
              Khám phá rạp & phim
            </span>

            <router-link 
              to="/" 
              @click="close"
              class="flex items-center gap-3 px-3.5 py-3 rounded-2xl text-sm font-bold text-slate-300 hover:text-white hover:bg-white/5 transition-all"
              active-class="bg-cinema-accent/15 text-cinema-accent border border-cinema-accent/30 shadow-glow-accent"
            >
              <Home class="w-4 h-4" />
              <span>Trang chủ</span>
            </router-link>

            <router-link 
              to="/cinemas" 
              @click="close"
              class="flex items-center justify-between px-3.5 py-3 rounded-2xl text-sm font-bold text-slate-300 hover:text-white hover:bg-white/5 transition-all"
              active-class="bg-cinema-accent/15 text-cinema-accent border border-cinema-accent/30 shadow-glow-accent"
            >
              <div class="flex items-center gap-3">
                <Building2 class="w-4 h-4 text-slate-400" />
                <span>Cụm Rạp & Lịch Chiếu</span>
              </div>
              <span class="text-[10px] bg-cinema-accent/20 text-cinema-accent px-2 py-0.5 rounded-full border border-cinema-accent/30">Mới</span>
            </router-link>

            <router-link 
              to="/now-showing" 
              @click="close"
              class="flex items-center gap-3 px-3.5 py-3 rounded-2xl text-sm font-bold text-slate-300 hover:text-white hover:bg-white/5 transition-all"
              active-class="bg-emerald-500/15 text-emerald-300 border border-emerald-500/30"
            >
              <Flame class="w-4 h-4 text-orange-400" />
              <span>Phim đang chiếu</span>
            </router-link>

            <router-link 
              to="/coming-soon" 
              @click="close"
              class="flex items-center gap-3 px-3.5 py-3 rounded-2xl text-sm font-bold text-slate-300 hover:text-white hover:bg-white/5 transition-all"
              active-class="bg-amber-500/15 text-amber-300 border border-amber-500/30"
            >
              <Sparkles class="w-4 h-4 text-cinema-gold" />
              <span>Phim sắp chiếu</span>
            </router-link>

            <router-link 
              to="/my-tickets" 
              @click="close"
              class="flex items-center justify-between px-3.5 py-3 rounded-2xl text-sm font-bold text-slate-300 hover:text-white hover:bg-white/5 transition-all"
              active-class="bg-cinema-accent/15 text-cinema-accent border border-cinema-accent/30 shadow-glow-accent"
            >
              <div class="flex items-center gap-3">
                <Ticket class="w-4 h-4" />
                <span>Vé của tôi</span>
              </div>
              <span v-if="store.activeTicket" class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
            </router-link>

            <button 
              v-if="authStore.isAuthenticated"
              @click="authStore.openRewardModal(); close();"
              class="w-full flex items-center justify-between px-3.5 py-3 rounded-2xl text-sm font-bold text-amber-300 hover:bg-amber-500/10 transition-all cursor-pointer"
            >
              <div class="flex items-center gap-3">
                <Gift class="w-4 h-4 text-amber-400" />
                <span>Đổi Điểm Thưởng VIP</span>
              </div>
              <span class="text-xs font-mono font-black text-amber-400">{{ authStore.user?.points || 0 }} Pts</span>
            </button>
          </div>

          <!-- Quick Location Switcher -->
          <div class="pt-4 border-t border-white/5 space-y-2">
            <span class="text-[10px] font-bold text-cinema-muted uppercase tracking-widest px-3 block">
              Khu vực rạp
            </span>
            <div class="p-3 rounded-2xl bg-cinema-card/50 border border-white/5 flex items-center justify-between">
              <div class="flex items-center gap-2 text-xs font-semibold text-white">
                <MapPin class="w-4 h-4 text-emerald-400" />
                <span>{{ selectedCity }}</span>
              </div>
              <select 
                v-model="selectedCity"
                class="bg-transparent text-xs text-cinema-accent font-bold outline-none cursor-pointer"
              >
                <option class="bg-slate-900 text-white" value="TP. Hồ Chí Minh">TP.HCM</option>
                <option class="bg-slate-900 text-white" value="Hà Nội">Hà Nội</option>
                <option class="bg-slate-900 text-white" value="Đà Nẵng">Đà Nẵng</option>
                <option class="bg-slate-900 text-white" value="Hải Phòng">Hải Phòng</option>
                <option class="bg-slate-900 text-white" value="Cần Thơ">Cần Thơ</option>
                <option class="bg-slate-900 text-white" value="Lâm Đồng">Đà Lạt</option>
              </select>
            </div>
          </div>

          <!-- Logout Button if authenticated -->
          <div v-if="authStore.isAuthenticated" class="pt-2">
            <button 
              @click="authStore.logout(); close();"
              class="w-full py-2.5 rounded-xl bg-white/5 hover:bg-rose-500/10 text-slate-400 hover:text-rose-400 text-xs font-bold transition-colors cursor-pointer flex items-center justify-center gap-2"
            >
              <LogOut class="w-3.5 h-3.5" />
              <span>Đăng xuất</span>
            </button>
          </div>

        </div>

        <!-- Footer / Support -->
        <div class="p-6 border-t border-white/10 space-y-3 bg-black/20">
          <div class="flex items-center justify-between text-xs text-cinema-muted">
            <span class="flex items-center gap-1.5">
              <PhoneCall class="w-3.5 h-3.5 text-cinema-accent" />
              <span>Hotline hỗ trợ:</span>
            </span>
            <strong class="text-white font-mono">1900 6868</strong>
          </div>
          <p class="text-[10px] text-center text-slate-500">
            CineReserve Realtime Booking System v2.0
          </p>
        </div>

      </aside>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
import { ref, watch, onMounted, onUnmounted } from 'vue';
import { 
  Clapperboard, 
  X, 
  Home, 
  Building2, 
  Flame, 
  Sparkles, 
  Ticket, 
  MapPin, 
  PhoneCall,
  Gift,
  LogOut
} from 'lucide-vue-next';
import { useBookingStore } from '../stores/bookingStore';
import { useAuthStore } from '../stores/authStore';

const props = defineProps<{
  isOpen: boolean;
}>();

const emit = defineEmits<{
  (e: 'close'): void;
}>();

const store = useBookingStore();
const authStore = useAuthStore();
const selectedCity = ref('TP. Hồ Chí Minh');

const close = () => {
  emit('close');
};

const handleKeyDown = (e: KeyboardEvent) => {
  if (e.key === 'Escape' && props.isOpen) {
    close();
  }
};

watch(() => props.isOpen, (open) => {
  if (typeof document !== 'undefined') {
    if (open) {
      document.body.style.overflow = 'hidden';
    } else {
      document.body.style.overflow = '';
    }
  }
}, { immediate: true });

onMounted(() => {
  if (typeof window !== 'undefined') {
    window.addEventListener('keydown', handleKeyDown);
  }
});

onUnmounted(() => {
  if (typeof document !== 'undefined') {
    document.body.style.overflow = '';
  }
  if (typeof window !== 'undefined') {
    window.removeEventListener('keydown', handleKeyDown);
  }
});
</script>

<style scoped>
.sidebar-fade-enter-active,
.sidebar-fade-leave-active {
  transition: opacity 0.3s ease;
}
.sidebar-fade-enter-from,
.sidebar-fade-leave-to {
  opacity: 0;
}

.sidebar-slide-enter-active,
.sidebar-slide-leave-active {
  transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1);
}
.sidebar-slide-enter-from,
.sidebar-slide-leave-to {
  transform: translateX(100%);
}
</style>
