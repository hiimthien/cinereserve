<template>
  <div class="min-h-screen bg-cinema-bg py-8 px-4 sm:px-6 lg:px-8 pb-24 select-none">
    <div class="max-w-4xl mx-auto space-y-6">
      
      <!-- Top Title & Staff Switcher -->
      <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border-b border-white/10 pb-4">
        <div>
          <div class="flex items-center gap-2">
            <span class="px-2.5 py-0.5 rounded-full bg-rose-500/20 text-rose-300 border border-rose-500/30 text-xs font-black uppercase tracking-wider">
              Staff Portal
            </span>
            <h1 class="text-xl md:text-2xl font-black text-white">Quét QR Soát Vé Tại Quầy</h1>
          </div>
          <p class="text-xs text-cinema-muted mt-1">
            Hệ thống nhận diện vé thời gian thực • Tự động đối soát & chống quét vé 2 lần
          </p>
        </div>

        <div class="flex items-center gap-3">
          <div class="flex items-center gap-2 bg-cinema-card px-3 py-1.5 rounded-2xl border border-white/10 text-xs">
            <UserCheck class="w-4 h-4 text-emerald-400" />
            <span class="text-slate-300">Nhân viên: <strong class="text-white">{{ staffName }}</strong></span>
          </div>
          <button 
            @click="soundEnabled = !soundEnabled"
            class="p-2 rounded-xl border transition-colors cursor-pointer"
            :class="soundEnabled ? 'bg-emerald-500/10 border-emerald-500/30 text-emerald-400' : 'bg-cinema-card border-white/10 text-slate-500'"
            :title="soundEnabled ? 'Âm thanh bật' : 'Âm thanh tắt'"
          >
            <Volume2 v-if="soundEnabled" class="w-4 h-4" />
            <VolumeX v-else class="w-4 h-4" />
          </button>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- Left: Camera Viewport & Manual Input (7 Cols) -->
        <div class="lg:col-span-7 space-y-5">
          
          <!-- Scanner Camera Card -->
          <div class="relative overflow-hidden rounded-3xl bg-slate-950 border-2 border-cinema-border aspect-video sm:aspect-[4/3] flex items-center justify-center shadow-2xl">
            
            <!-- Video Stream -->
            <video 
              ref="videoEl" 
              class="w-full h-full object-cover"
              autoplay 
              playsinline 
              muted
            ></video>

            <!-- Laser Scanning Grid Overlay -->
            <div class="absolute inset-0 pointer-events-none flex items-center justify-center p-8">
              <div class="relative w-64 h-64 sm:w-72 sm:h-72 border-2 border-emerald-400/40 rounded-3xl overflow-hidden shadow-[0_0_30px_rgba(16,185,129,0.2)]">
                <!-- 4 Corner Target Accents -->
                <div class="absolute top-0 left-0 w-6 h-6 border-t-4 border-l-4 border-emerald-400 rounded-tl-xl"></div>
                <div class="absolute top-0 right-0 w-6 h-6 border-t-4 border-r-4 border-emerald-400 rounded-tr-xl"></div>
                <div class="absolute bottom-0 left-0 w-6 h-6 border-b-4 border-l-4 border-emerald-400 rounded-bl-xl"></div>
                <div class="absolute bottom-0 right-0 w-6 h-6 border-b-4 border-r-4 border-emerald-400 rounded-br-xl"></div>
                
                <!-- Laser Line -->
                <div class="w-full h-1 bg-gradient-to-r from-transparent via-emerald-400 to-transparent shadow-[0_0_15px_#10b981] animate-laser"></div>
              </div>
            </div>

            <!-- Camera Status Pill -->
            <div class="absolute top-4 left-4 flex items-center gap-2 bg-black/70 backdrop-blur-md px-3 py-1.5 rounded-full border border-white/10 text-xs">
              <span class="w-2 h-2 rounded-full" :class="isCameraActive ? 'bg-emerald-400 animate-pulse' : 'bg-amber-400'"></span>
              <span class="text-white font-medium">{{ isCameraActive ? 'Camera Đang Hoạt Động' : 'Đang Kết Nối Camera...' }}</span>
            </div>

            <!-- Switch Camera Button -->
            <button 
              @click="toggleCamera"
              class="absolute top-4 right-4 p-2 rounded-full bg-black/70 backdrop-blur-md text-white hover:bg-black/90 border border-white/10 cursor-pointer transition-colors"
              title="Đổi Camera"
            >
              <RefreshCw class="w-4 h-4" />
            </button>
          </div>

          <!-- Manual Code Input & Quick Sample Demos -->
          <div class="p-5 rounded-3xl bg-cinema-card/70 border border-cinema-border backdrop-blur-md space-y-3">
            <label class="text-xs font-bold text-slate-300 block">Hoặc Nhập Mã Vé Thủ Công:</label>
            <div class="flex gap-2">
              <input 
                v-model="manualCode"
                @keyup.enter="handleCheckIn(manualCode)"
                type="text"
                placeholder="Nhập mã vé (ví dụ: CR-1A2B3C)"
                class="flex-1 bg-slate-900/90 border border-white/10 rounded-2xl px-4 py-3 text-sm text-white placeholder:text-slate-500 uppercase font-mono tracking-wider focus:outline-none focus:border-cinema-accent transition-colors"
              />
              <button 
                @click="handleCheckIn(manualCode)"
                :disabled="!manualCode || isChecking"
                class="px-6 py-3 rounded-2xl bg-cinema-accent hover:bg-rose-600 disabled:opacity-30 text-white font-black text-xs transition-colors cursor-pointer shadow-glow-accent shrink-0 flex items-center gap-2"
              >
                <Loader2 v-if="isChecking" class="w-4 h-4 animate-spin" />
                <span v-else>Soát Vé</span>
              </button>
            </div>

            <!-- Quick Sample Test Buttons -->
            <div class="pt-2 flex items-center gap-2 flex-wrap">
              <span class="text-[11px] font-bold text-cinema-muted">Vé mẫu thử nghiệm:</span>
              <button 
                v-for="code in sampleCodes" 
                :key="code"
                @click="manualCode = code; handleCheckIn(code)"
                class="px-2.5 py-1 rounded-xl bg-white/5 hover:bg-white/10 border border-white/5 hover:border-amber-500/30 text-xs font-mono font-bold text-amber-300 cursor-pointer transition-all"
              >
                {{ code }}
              </button>
            </div>
          </div>

        </div>

        <!-- Right: Scan Result & Inspection Card (5 Cols) -->
        <div class="lg:col-span-5 space-y-5">
          
          <!-- Scan Result Banner -->
          <div 
            v-if="scanResult"
            class="p-6 rounded-3xl border transition-all duration-300 shadow-2xl space-y-4"
            :class="[
              scanResult.status === 'VALID'
                ? 'bg-emerald-950/40 border-emerald-500 shadow-glow-green text-emerald-300'
                : 'bg-rose-950/40 border-rose-500 shadow-glow-accent text-rose-300'
            ]"
          >
            <!-- Status Header Icon -->
            <div class="flex items-center gap-3">
              <div 
                class="w-12 h-12 rounded-2xl flex items-center justify-center text-white shrink-0 shadow-md"
                :class="scanResult.status === 'VALID' ? 'bg-emerald-500' : 'bg-rose-500'"
              >
                <CheckCircle2 v-if="scanResult.status === 'VALID'" class="w-7 h-7" />
                <AlertOctagon v-else class="w-7 h-7" />
              </div>

              <div>
                <h3 class="text-base font-black text-white">
                  {{ scanResult.status === 'VALID' ? 'VÉ HỢP LỆ • ĐÃ SOÁT VÉ' : 'VÉ KHÔNG HỢP LỆ' }}
                </h3>
                <p class="text-xs font-medium mt-0.5 leading-snug">
                  {{ scanResult.message }}
                </p>
              </div>
            </div>

            <!-- Ticket Details Card if available -->
            <div v-if="scanResult.ticket" class="p-4 rounded-2xl bg-black/40 border border-white/10 space-y-3 text-xs">
              <div class="flex items-center gap-3 pb-3 border-b border-white/10">
                <img 
                  :src="scanResult.ticket.movie_poster || 'https://images.unsplash.com/photo-1534447677768-be436bb09401?w=300'" 
                  class="w-12 h-16 object-cover rounded-xl border border-white/10 shrink-0 shadow-sm"
                />
                <div class="min-w-0 flex-1">
                  <h4 class="font-extrabold text-white text-sm truncate">{{ scanResult.ticket.movie_title }}</h4>
                  <p class="text-slate-400 text-[11px]">{{ scanResult.ticket.cinema_name }} • {{ scanResult.ticket.room_name }}</p>
                  <p class="text-amber-400 font-bold text-[11px]">
                    Suất: {{ scanResult.ticket.start_time }} • {{ scanResult.ticket.show_date }}
                  </p>
                </div>
              </div>

              <div class="grid grid-cols-2 gap-2 text-[11px]">
                <div>
                  <span class="text-slate-500 block">Khách hàng:</span>
                  <strong class="text-white">{{ scanResult.ticket.user_name }}</strong>
                </div>
                <div>
                  <span class="text-slate-500 block">Số điện thoại:</span>
                  <strong class="text-white font-mono">{{ scanResult.ticket.user_phone || '---' }}</strong>
                </div>
                <div class="col-span-2">
                  <span class="text-slate-500 block">Ghế ngồi:</span>
                  <span class="font-black text-emerald-400 text-sm">
                    {{ scanResult.ticket.seats?.join(', ') || 'Chưa rõ' }}
                  </span>
                </div>
                <div v-if="scanResult.ticket.combos?.length" class="col-span-2 text-amber-300">
                  <span class="text-slate-500 block">Bắp nước nhận tại quầy:</span>
                  <div v-for="cb in scanResult.ticket.combos" :key="cb.id" class="font-bold">
                    🍿 {{ cb.name }} (x{{ cb.quantity }})
                  </div>
                </div>
              </div>
            </div>

            <!-- Close Result / Scan Next Button -->
            <button 
              @click="scanResult = null"
              class="w-full py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white font-bold text-xs transition-colors cursor-pointer text-center block"
            >
              Tiếp tục quét vé tiếp theo
            </button>
          </div>

          <!-- Empty State when waiting -->
          <div v-else class="p-8 rounded-3xl bg-cinema-card/40 border border-white/5 text-center space-y-3">
            <div class="w-14 h-14 rounded-2xl bg-white/5 flex items-center justify-center mx-auto text-slate-500">
              <QrCode class="w-7 h-7" />
            </div>
            <h4 class="text-sm font-bold text-white">Sẵn Sàng Quét Mã</h4>
            <p class="text-xs text-cinema-muted max-w-xs mx-auto">
              Hướng camera về phía mã QR trên vé điện tử của khách hàng hoặc nhập mã vé để đối soát.
            </p>
          </div>

          <!-- Recent Check-ins History Log -->
          <div class="p-5 rounded-3xl bg-cinema-card/50 border border-cinema-border space-y-3">
            <div class="flex items-center justify-between text-xs">
              <h4 class="font-bold text-white flex items-center gap-1.5">
                <History class="w-4 h-4 text-cinema-gold" />
                <span>Lịch Sử Soát Vé Vừa Xong:</span>
              </h4>
              <span class="text-cinema-muted font-mono">{{ recentHistory.length }} vé</span>
            </div>

            <div v-if="recentHistory.length === 0" class="text-xs text-slate-500 italic py-2 text-center">
              Chưa có lượt quét nào trong phiên này
            </div>

            <div v-else class="space-y-2 max-h-48 overflow-y-auto pr-1">
              <div 
                v-for="(item, idx) in recentHistory" 
                :key="idx"
                class="p-2.5 rounded-xl bg-black/30 border border-white/5 flex items-center justify-between text-xs"
              >
                <div>
                  <span class="font-mono font-bold text-white">{{ item.code }}</span>
                  <span class="text-[10px] text-slate-400 block">{{ item.name }} • Ghế {{ item.seats }}</span>
                </div>
                <span class="text-[10px] font-mono text-emerald-400">{{ item.time }}</span>
              </div>
            </div>
          </div>

        </div>

      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { 
  QrCode, 
  CheckCircle2, 
  AlertOctagon, 
  RefreshCw, 
  UserCheck, 
  Volume2, 
  VolumeX, 
  History,
  Loader2 
} from 'lucide-vue-next';
import api from '../services/api';

const staffName = ref('Cao Lương Thiện');
const soundEnabled = ref(true);
const manualCode = ref('');
const isChecking = ref(false);
const isCameraActive = ref(false);
const videoEl = ref<HTMLVideoElement | null>(null);

let stream: MediaStream | null = null;
let scanInterval: any = null;

const sampleCodes = ref(['CR-A1B2C3', 'CR-9F8E7D', 'CR-5K4J3H']);

interface ScanResultData {
  status: 'VALID' | 'ALREADY_USED' | 'INVALID' | 'UNPAID';
  message: string;
  ticket?: any;
}

const scanResult = ref<ScanResultData | null>(null);
const recentHistory = ref<Array<{ code: string; name: string; seats: string; time: string }>>([]);

// Sound synthesizer using Web Audio API
const playSound = (isSuccess: boolean) => {
  if (!soundEnabled.value || typeof window === 'undefined') return;
  try {
    const ctx = new (window.AudioContext || (window as any).webkitAudioContext)();
    const osc = ctx.createOscillator();
    const gain = ctx.createGain();
    osc.connect(gain);
    gain.connect(ctx.destination);

    if (isSuccess) {
      // Pleasant high-pitch success chime
      osc.frequency.setValueAtTime(800, ctx.currentTime);
      osc.frequency.exponentialRampToValueAtTime(1200, ctx.currentTime + 0.15);
      gain.gain.setValueAtTime(0.3, ctx.currentTime);
      gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.2);
      osc.start(ctx.currentTime);
      osc.stop(ctx.currentTime + 0.2);
    } else {
      // Low buzz error tone
      osc.type = 'sawtooth';
      osc.frequency.setValueAtTime(220, ctx.currentTime);
      osc.frequency.exponentialRampToValueAtTime(150, ctx.currentTime + 0.3);
      gain.gain.setValueAtTime(0.3, ctx.currentTime);
      gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.3);
      osc.start(ctx.currentTime);
      osc.stop(ctx.currentTime + 0.3);
    }
  } catch (e) {
    // Ignore audio context restrictions
  }
};

const handleCheckIn = async (code: string) => {
  if (!code || isChecking.value) return;
  isChecking.value = true;

  try {
    const res = await api.post('/tickets/check-in', {
      qr_code: code.trim(),
      staff_name: staffName.value,
    });

    if (res.data?.success) {
      playSound(true);
      scanResult.value = {
        status: 'VALID',
        message: res.data.message || 'Soát vé thành công!',
        ticket: res.data.data,
      };

      // Add to recent history
      recentHistory.value.unshift({
        code: res.data.data.booking_code,
        name: res.data.data.user_name || 'Khách',
        seats: res.data.data.seats?.join(', ') || '',
        time: new Date().toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit', second: '2-digit' }),
      });
      if (recentHistory.value.length > 8) recentHistory.value.pop();

      manualCode.value = '';
    }
  } catch (err: any) {
    playSound(false);
    const data = err.response?.data;
    scanResult.value = {
      status: data?.status || 'INVALID',
      message: data?.message || 'Vé không hợp lệ hoặc không tồn tại.',
      ticket: data?.data,
    };
  } finally {
    isChecking.value = false;
  }
};

const startCamera = async () => {
  try {
    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
      stream = await navigator.mediaDevices.getUserMedia({
        video: { facingMode: 'environment' },
        audio: false,
      });
      if (videoEl.value) {
        videoEl.value.srcObject = stream;
        isCameraActive.value = true;
      }
    }
  } catch (e) {
    console.warn('Cannot access camera:', e);
    isCameraActive.value = false;
  }
};

const stopCamera = () => {
  if (stream) {
    stream.getTracks().forEach(track => track.stop());
    stream = null;
  }
  isCameraActive.value = false;
};

const toggleCamera = () => {
  stopCamera();
  startCamera();
};

onMounted(() => {
  startCamera();
});

onUnmounted(() => {
  stopCamera();
  if (scanInterval) clearInterval(scanInterval);
});
</script>

<style scoped>
@keyframes laser-sweep {
  0% { transform: translateY(0); }
  50% { transform: translateY(270px); }
  100% { transform: translateY(0); }
}

.animate-laser {
  animation: laser-sweep 2s ease-in-out infinite;
}
</style>
