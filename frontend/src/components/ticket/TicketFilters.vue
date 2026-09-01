<template>
  <div class="space-y-4">
    <!-- Status Tabs -->
    <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-none">
      <button 
        v-for="tab in statusTabs" 
        :key="tab.value"
        @click="$emit('update:activeStatus', tab.value); $emit('filter-change')"
        class="px-4 py-2 rounded-2xl text-xs font-bold transition-all cursor-pointer select-none shrink-0"
        :class="[
          activeStatus === tab.value
            ? 'bg-cinema-accent text-white shadow-glow-accent'
            : 'bg-cinema-surface/80 border border-cinema-border text-slate-400 hover:text-white hover:border-slate-500'
        ]"
      >
        {{ tab.label }}
      </button>
    </div>

    <!-- Dropdown Filters & Search Box -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 p-4 rounded-3xl bg-cinema-card/50 border border-cinema-border">
      <!-- Search Input -->
      <div class="relative">
        <input 
          :value="searchQuery"
          @input="$emit('update:searchQuery', ($event.target as HTMLInputElement).value); $emit('filter-change')"
          type="text"
          placeholder="Tìm theo mã vé, tên phim..."
          class="w-full bg-slate-900/90 border border-cinema-border rounded-xl pl-9 pr-4 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-cinema-accent transition-colors"
        />
        <Search class="w-4 h-4 text-slate-400 absolute left-3 top-3" />
      </div>

      <!-- Movie Selector -->
      <BaseSelect 
        :modelValue="selectedMovieId"
        :options="movieOptions"
        @update:modelValue="$emit('update:selectedMovieId', $event); $emit('filter-change')"
      >
        <template #prefix>
          <Film class="w-3.5 h-3.5 text-cinema-accent" />
        </template>
      </BaseSelect>

      <!-- Cinema Selector -->
      <BaseSelect 
        :modelValue="selectedCinemaId"
        :options="cinemaOptions"
        @update:modelValue="$emit('update:selectedCinemaId', $event); $emit('filter-change')"
      >
        <template #prefix>
          <Building2 class="w-3.5 h-3.5 text-cyan-400" />
        </template>
      </BaseSelect>

      <!-- Reset Filters Button -->
      <BaseButton 
        variant="secondary"
        size="md"
        @click="$emit('reset')"
      >
        <template #prefix>
          <RotateCcw class="w-3.5 h-3.5" />
        </template>
        Đặt Lại Bộ Lọc
      </BaseButton>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Search, RotateCcw, Film, Building2 } from 'lucide-vue-next';
import BaseSelect from '../base/BaseSelect.vue';
import BaseButton from '../base/BaseButton.vue';

const props = defineProps<{
  activeStatus: string;
  selectedMovieId: string | number;
  selectedCinemaId: string | number;
  searchQuery: string;
  availableMovies: any[];
  availableCinemas: any[];
}>();

defineEmits<{
  (e: 'update:activeStatus', val: string): void;
  (e: 'update:selectedMovieId', val: string | number): void;
  (e: 'update:selectedCinemaId', val: string | number): void;
  (e: 'update:searchQuery', val: string): void;
  (e: 'filter-change'): void;
  (e: 'reset'): void;
}>();

const statusTabs = [
  { label: 'Tất Cả Vé', value: 'all' },
  { label: '✅ Đã Thanh Toán', value: 'confirmed' },
  { label: '🎟️ Đã Soát Vé', value: 'checked_in' },
  { label: '❌ Đã Hủy', value: 'cancelled' },
];

const movieOptions = computed(() => [
  { label: 'Tất Cả Phim', value: 'all' },
  ...props.availableMovies.map(m => ({ label: m.title, value: m.id })),
]);

const cinemaOptions = computed(() => [
  { label: 'Tất Cả Rạp', value: 'all' },
  ...props.availableCinemas.map(c => ({ label: c.name, value: c.id })),
]);
</script>
