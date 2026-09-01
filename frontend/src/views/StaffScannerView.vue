<template>
  <div class="min-h-screen bg-cinema-bg flex flex-col justify-between">
    <Navbar />

    <main class="max-w-4xl mx-auto px-4 sm:px-6 py-8 w-full flex-1 space-y-6">
      
      <!-- Header with Staff Badge & Audio Toggle -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-cinema-border pb-6">
        <div class="space-y-1">
          <div class="flex items-center gap-2.5">
            <span class="p-2 rounded-2xl bg-cyan-500/10 border border-cyan-500/30 text-cyan-400">
              <QrCode class="w-6 h-6" />
            </span>
            <div>
              <h1 class="text-2xl font-black text-white tracking-tight">Máy Soát Vé QR Tại Cổng</h1>
              <p class="text-xs text-cinema-muted">CineReserve Staff Live Ticket Validation System</p>
            </div>
          </div>
        </div>

        <div class="flex items-center gap-3">
          <!-- Audio Toggle -->
          <button 
            @click="soundEnabled = !soundEnabled"
            class="p-2.5 rounded-xl border transition-all cursor-pointer text-xs flex items-center gap-1.5"
            :class="soundEnabled ? 'bg-cyan-500/15 border-cyan-500/40 text-cyan-300' : 'bg-slate-800 border-white/5 text-slate-500'"
          >
            <component :is="soundEnabled ? Volume2 : VolumeX" class="w-4 h-4" />
            <span class="font-bold">{{ soundEnabled ? 'Bật Âm' : 'Tắt Âm' }}</span>
          </button>
        </div>
      </div>

      <!-- Main Layout: 2 Columns (Left: Scanner / Manual, Right: Results / History) -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <!-- Left: Camera Scanner & Manual Input -->
        <div class="space-y-5">
          <!-- Camera Component -->
          <ScannerCamera @scan="executeCheckIn" />

          <!-- Manual Code Form Input -->
          <div class="p-5 rounded-3xl bg-cinema-card/60 border border-cinema-border space-y-3">
            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider">
              Nhập mã vé thủ công:
            </label>
            <div class="flex gap-2">
              <input 
                v-model="manualCode"
                @keyup.enter="handleManualSubmit"
                type="text"
                placeholder="VD: CR-982103"
                class="flex-1 bg-slate-900 border border-cinema-border rounded-xl px-4 py-2.5 text-xs text-white uppercase font-mono tracking-wider focus:outline-none focus:border-cyan-400 transition-colors"
              />
              <BaseButton 
                variant="primary" 
                size="md"
                :loading="isChecking"
                @click="handleManualSubmit"
              >
                Kiểm Tra
              </BaseButton>
            </div>
          </div>
        </div>

        <!-- Right: Real-time Scan Result Card & History Log -->
        <div class="space-y-5">
          <ScanResultCard 
            :result="scanResult" 
            @clear="scanResult = null" 
          />

          <ScanHistoryList 
            :history="recentHistory" 
          />
        </div>

      </div>

    </main>

    <Footer />
  </div>
</template>

<script setup lang="ts">
import { QrCode, Volume2, VolumeX } from 'lucide-vue-next';
import { useStaffScanner } from '../composables/useStaffScanner';
import Navbar from '../components/common/Navbar.vue';
import Footer from '../components/common/Footer.vue';
import ScannerCamera from '../components/scanner/ScannerCamera.vue';
import ScanResultCard from '../components/scanner/ScanResultCard.vue';
import ScanHistoryList from '../components/scanner/ScanHistoryList.vue';
import BaseButton from '../components/base/BaseButton.vue';

const {
  soundEnabled,
  manualCode,
  isChecking,
  scanResult,
  recentHistory,
  executeCheckIn,
  handleManualSubmit,
} = useStaffScanner();
</script>
