<template>
  <div class="relative w-full aspect-square max-w-[360px] mx-auto rounded-3xl overflow-hidden bg-black border-2 border-cinema-border flex items-center justify-center shadow-2xl">
    <!-- Active Camera Viewfinder -->
    <video 
      ref="videoEl" 
      class="w-full h-full object-cover" 
      autoplay 
      playsinline 
      muted
      v-show="isActive"
    ></video>

    <!-- Inactive Camera Overlay Placeholder -->
    <div v-if="!isActive" class="text-center p-6 space-y-4">
      <div class="w-16 h-16 rounded-full bg-slate-900 border border-white/10 flex items-center justify-center mx-auto text-slate-400">
        <Camera class="w-8 h-8" />
      </div>
      <div>
        <p class="text-sm font-bold text-white">Camera Đang Tắt</p>
        <p class="text-xs text-slate-400 mt-1">Bật camera để quét mã vé QR tự động</p>
      </div>
      <BaseButton 
        variant="primary" 
        size="md"
        @click="startCamera"
      >
        <template #prefix>
          <Camera class="w-4 h-4" />
        </template>
        Bật Camera
      </BaseButton>
    </div>

    <!-- Active Scanner Overlay Crosshairs & Laser Animation -->
    <div v-else class="absolute inset-0 pointer-events-none flex items-center justify-center p-8">
      <div class="relative w-full h-full border-2 border-cyan-400/80 rounded-2xl shadow-[0_0_20px_rgba(6,182,212,0.4)]">
        <!-- Laser Scan Line -->
        <div class="absolute inset-x-0 h-0.5 bg-gradient-to-r from-transparent via-cyan-400 to-transparent shadow-[0_0_12px_#06b6d4] animate-scan"></div>
        <!-- Corner Reticles -->
        <span class="absolute -top-1 -left-1 w-4 h-4 border-t-2 border-l-2 border-cyan-300"></span>
        <span class="absolute -top-1 -right-1 w-4 h-4 border-t-2 border-r-2 border-cyan-300"></span>
        <span class="absolute -bottom-1 -left-1 w-4 h-4 border-b-2 border-l-2 border-cyan-300"></span>
        <span class="absolute -bottom-1 -right-1 w-4 h-4 border-b-2 border-r-2 border-cyan-300"></span>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onUnmounted } from 'vue';
import { Camera } from 'lucide-vue-next';
import BaseButton from '../base/BaseButton.vue';

const emit = defineEmits<{
  (e: 'scan', code: string): void;
}>();

const videoEl = ref<HTMLVideoElement | null>(null);
const isActive = ref(false);
let stream: MediaStream | null = null;
let scanInterval: any = null;

const sampleCodes = ['CR-A1B2C3', 'CR-9F8E7D', 'CR-5K4J3H'];

const startCamera = async () => {
  try {
    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
      stream = await navigator.mediaDevices.getUserMedia({
        video: { facingMode: 'environment' },
      });
      if (videoEl.value) {
        videoEl.value.srcObject = stream;
        videoEl.value.play();
      }
      isActive.value = true;

      // Simulated auto-detect loop for demo purposes
      scanInterval = setInterval(() => {
        if (Math.random() < 0.25) {
          const randomCode = sampleCodes[Math.floor(Math.random() * sampleCodes.length)];
          emit('scan', randomCode);
        }
      }, 4000);
    } else {
      isActive.value = true;
    }
  } catch (e) {
    console.warn('Camera access denied, fallback active:', e);
    isActive.value = true;
  }
};

const stopCamera = () => {
  if (stream) {
    stream.getTracks().forEach(t => t.stop());
    stream = null;
  }
  if (scanInterval) {
    clearInterval(scanInterval);
    scanInterval = null;
  }
  isActive.value = false;
};

onUnmounted(() => {
  stopCamera();
});
</script>

<style scoped>
@keyframes scan {
  0% { top: 10%; opacity: 0; }
  15% { opacity: 1; }
  85% { opacity: 1; }
  100% { top: 90%; opacity: 0; }
}
.animate-scan {
  animation: scan 2.2s ease-in-out infinite;
}
</style>
