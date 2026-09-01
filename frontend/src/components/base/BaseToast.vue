<template>
  <div 
    class="fixed top-5 right-5 z-[9999] flex flex-col gap-2.5 max-w-sm w-full pointer-events-none"
    aria-live="assertive"
  >
    <transition-group
      enter-active-class="transform ease-out duration-300 transition"
      enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-4"
      enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
      leave-active-class="transition ease-in duration-200"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0 scale-95"
    >
      <div
        v-for="toast in toasts"
        :key="toast.id"
        class="pointer-events-auto flex items-start gap-3 p-4 rounded-2xl border shadow-2xl backdrop-blur-xl transition-all select-none"
        :class="getToastClasses(toast.type)"
      >
        <!-- Icon -->
        <component 
          :is="getToastIcon(toast.type)" 
          class="w-5 h-5 shrink-0 mt-0.5" 
          :class="getIconColorClass(toast.type)"
        />

        <!-- Content -->
        <div class="flex-1 space-y-0.5 min-w-0">
          <h4 v-if="toast.title" class="text-xs font-bold text-white tracking-wide">
            {{ toast.title }}
          </h4>
          <p class="text-xs text-slate-300 leading-relaxed break-words font-medium">
            {{ toast.message }}
          </p>
        </div>

        <!-- Close Button -->
        <button
          @click="removeToast(toast.id)"
          class="shrink-0 p-1 rounded-lg text-slate-400 hover:text-white hover:bg-white/10 transition-colors"
          title="Đóng"
        >
          <X class="w-4 h-4" />
        </button>
      </div>
    </transition-group>
  </div>
</template>

<script setup lang="ts">
import { CheckCircle2, AlertCircle, AlertTriangle, Info, X } from 'lucide-vue-next';
import { useToast, type ToastType } from '../../composables/useToast';

const { toasts, removeToast } = useToast();

const getToastClasses = (type: ToastType) => {
  switch (type) {
    case 'success':
      return 'bg-[#0f172a]/95 border-emerald-500/40 shadow-emerald-500/10 text-emerald-300';
    case 'error':
      return 'bg-[#180d12]/95 border-rose-500/40 shadow-rose-500/10 text-rose-300';
    case 'warning':
      return 'bg-[#1a1409]/95 border-amber-500/40 shadow-amber-500/10 text-amber-300';
    case 'info':
    default:
      return 'bg-[#0f172a]/95 border-cyan-500/40 shadow-cyan-500/10 text-cyan-300';
  }
};

const getToastIcon = (type: ToastType) => {
  switch (type) {
    case 'success':
      return CheckCircle2;
    case 'error':
      return AlertCircle;
    case 'warning':
      return AlertTriangle;
    case 'info':
    default:
      return Info;
  }
};

const getIconColorClass = (type: ToastType) => {
  switch (type) {
    case 'success':
      return 'text-emerald-400';
    case 'error':
      return 'text-rose-400';
    case 'warning':
      return 'text-amber-400';
    case 'info':
    default:
      return 'text-cyan-400';
  }
};
</script>
