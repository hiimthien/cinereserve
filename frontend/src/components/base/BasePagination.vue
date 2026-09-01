<template>
  <div v-if="totalPages > 1" class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-4 border-t border-white/10 select-none">
    <!-- Meta Info -->
    <div class="text-xs text-cinema-muted">
      Trang <strong class="text-white">{{ currentPage }}</strong> / <strong class="text-white">{{ totalPages }}</strong>
      <span v-if="totalItems !== undefined"> (Tổng cộng <strong class="text-white">{{ totalItems }}</strong> mục)</span>
    </div>

    <!-- Page Buttons -->
    <div class="flex items-center gap-1.5">
      <!-- Prev Button -->
      <button 
        :disabled="currentPage === 1"
        @click="goToPage(currentPage - 1)"
        class="p-2 rounded-xl bg-cinema-surface border border-cinema-border text-slate-300 hover:text-white disabled:opacity-30 disabled:cursor-not-allowed transition-all cursor-pointer"
        title="Trang trước"
      >
        <ChevronLeft class="w-4 h-4" />
      </button>

      <!-- Page Pills -->
      <button 
        v-for="p in visiblePages" 
        :key="p"
        @click="goToPage(p)"
        class="w-8 h-8 rounded-xl text-xs font-bold transition-all cursor-pointer flex items-center justify-center"
        :class="currentPage === p ? 'bg-cinema-accent text-white shadow-glow-accent' : 'bg-cinema-surface border border-cinema-border text-slate-400 hover:text-white'"
      >
        {{ p }}
      </button>

      <!-- Next Button -->
      <button 
        :disabled="currentPage === totalPages"
        @click="goToPage(currentPage + 1)"
        class="p-2 rounded-xl bg-cinema-surface border border-cinema-border text-slate-300 hover:text-white disabled:opacity-30 disabled:cursor-not-allowed transition-all cursor-pointer"
        title="Trang sau"
      >
        <ChevronRight class="w-4 h-4" />
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';

interface Props {
  currentPage: number;
  totalPages: number;
  totalItems?: number;
  maxVisiblePages?: number;
}

const props = withDefaults(defineProps<Props>(), {
  totalItems: undefined,
  maxVisiblePages: 7,
});

const emit = defineEmits<{
  (e: 'update:currentPage', page: number): void;
  (e: 'change', page: number): void;
}>();

const visiblePages = computed(() => {
  const pages: number[] = [];
  const max = Math.min(props.totalPages, props.maxVisiblePages);
  let start = Math.max(1, props.currentPage - Math.floor(max / 2));
  let end = start + max - 1;

  if (end > props.totalPages) {
    end = props.totalPages;
    start = Math.max(1, end - max + 1);
  }

  for (let i = start; i <= end; i++) {
    pages.push(i);
  }
  return pages;
});

const goToPage = (page: number) => {
  if (page < 1 || page > props.totalPages || page === props.currentPage) return;
  emit('update:currentPage', page);
  emit('change', page);
};
</script>
