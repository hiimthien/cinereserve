<template>
  <Teleport to="body">
    <Transition name="modal-fade">
      <div 
        v-if="modelValue" 
        class="fixed inset-0 flex items-center justify-center p-4 sm:p-6 bg-black/85 backdrop-blur-md overflow-y-auto"
        :style="{ zIndex: zIndex }"
        @click.self="handleBackdropClick"
      >

        <!-- Modal Dialog Box -->
        <Transition name="modal-scale">
          <div 
            v-if="modelValue"
            ref="modalContainerRef"
            class="relative w-full bg-cinema-surface border border-cinema-border rounded-3xl shadow-2xl flex flex-col max-h-[90vh] overflow-hidden my-auto"
            :class="maxWidthClasses[maxWidth]"
            role="dialog"
            aria-modal="true"
          >
            <!-- Header (Sticky Top) -->
            <div v-if="title || $slots.header" class="flex items-center justify-between px-6 py-5 border-b border-white/5 bg-cinema-surface/90 backdrop-blur-md shrink-0">
              <slot name="header">
                <h3 class="text-lg font-extrabold text-white leading-tight">{{ title }}</h3>
              </slot>
              
              <button 
                v-if="closable"
                @click="close"
                class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-slate-300 hover:text-white flex items-center justify-center transition-colors cursor-pointer shrink-0"
                aria-label="Close"
              >
                ✕
              </button>
            </div>

            <!-- Body (Scrollable with nice spacing) -->
            <div class="p-6 overflow-y-auto max-h-[calc(90vh-130px)] scrollbar-thin scrollbar-thumb-white/10 scrollbar-track-transparent">
              <slot />
            </div>

            <!-- Footer (Optional Sticky Bottom) -->
            <div v-if="$slots.footer" class="px-6 py-4 border-t border-white/5 bg-cinema-surface/90 backdrop-blur-md shrink-0">
              <slot name="footer" />
            </div>

          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
import { watch, onMounted, onUnmounted } from 'vue';

interface Props {
  modelValue: boolean;
  title?: string;
  closable?: boolean;
  closeOnBackdrop?: boolean;
  closeOnEsc?: boolean;
  maxWidth?: 'sm' | 'md' | 'lg' | 'xl' | '2xl' | '3xl' | '4xl' | '5xl';
  zIndex?: number;
}

const props = withDefaults(defineProps<Props>(), {
  title: '',
  closable: true,
  closeOnBackdrop: true,
  closeOnEsc: true,
  maxWidth: '2xl',
  zIndex: 50,
});


const emit = defineEmits<{
  (e: 'update:modelValue', value: boolean): void;
  (e: 'close'): void;
  (e: 'confirm'): void;
}>();

const maxWidthClasses = {
  sm: 'max-w-md',
  md: 'max-w-lg',
  lg: 'max-w-xl',
  xl: 'max-w-2xl',
  '2xl': 'max-w-3xl',
  '3xl': 'max-w-4xl',
  '4xl': 'max-w-5xl',
  '5xl': 'max-w-6xl',
};

const close = () => {
  emit('update:modelValue', false);
  emit('close');
};

const handleBackdropClick = () => {
  if (props.closeOnBackdrop) {
    close();
  }
};

const handleKeyDown = (event: KeyboardEvent) => {
  if (props.closeOnEsc && event.key === 'Escape' && props.modelValue) {
    close();
  }
};

const updateBodyScrollLock = (isLocked: boolean) => {
  if (typeof document !== 'undefined') {
    if (isLocked) {
      document.body.style.overflow = 'hidden';
    } else {
      document.body.style.overflow = '';
    }
  }
};

watch(() => props.modelValue, (isOpen) => {
  updateBodyScrollLock(isOpen);
}, { immediate: true });

onMounted(() => {
  if (typeof window !== 'undefined') {
    window.addEventListener('keydown', handleKeyDown);
  }
});

onUnmounted(() => {
  updateBodyScrollLock(false);
  if (typeof window !== 'undefined') {
    window.removeEventListener('keydown', handleKeyDown);
  }
});
</script>

<style scoped>
.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.3s ease;
}
.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}

.modal-scale-enter-active,
.modal-scale-leave-active {
  transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.modal-scale-enter-from,
.modal-scale-leave-to {
  opacity: 0;
  transform: scale(0.95) translateY(10px);
}
</style>
