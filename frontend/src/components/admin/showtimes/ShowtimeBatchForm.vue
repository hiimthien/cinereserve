<template>
  <div class="space-y-3.5">
    <!-- Multi Cinema Selection Box -->
    <div class="space-y-1.5">
      <div class="flex items-center justify-between">
        <label class="block text-xs font-semibold text-cinema-muted">
          Chọn Cụm Rạp Chiếu ({{ batchForm.cinema_ids.length }}/{{ cinemasList.length }}) *
        </label>
        <button 
          type="button" 
          @click="$emit('toggle-select-all')"
          class="text-[11px] text-cinema-accent hover:underline font-bold cursor-pointer"
        >
          {{ batchForm.cinema_ids.length === cinemasList.length ? 'Bỏ chọn' : 'Chọn tất cả' }}
        </button>
      </div>

      <div class="max-h-44 overflow-y-auto p-3 rounded-2xl bg-slate-900/90 border border-cinema-border grid grid-cols-1 sm:grid-cols-2 gap-2 scrollbar-thin">
        <label 
          v-for="c in cinemasList" 
          :key="c.id"
          class="flex items-center gap-2 text-xs text-slate-300 hover:text-white cursor-pointer"
        >
          <input 
            type="checkbox" 
            :value="c.id" 
            v-model="batchForm.cinema_ids"
            class="rounded text-cinema-accent bg-slate-950 border-cinema-border cursor-pointer"
          />
          <span class="truncate">{{ c.name }}</span>
        </label>
      </div>
    </div>

    <!-- Date Range & Days Count -->
    <div class="grid grid-cols-2 gap-3">
      <BaseInput 
        v-model="batchForm.start_date"
        type="date"
        label="Từ Ngày Khởi Chiếu *"
        required
      />

      <BaseSelect 
        v-model="batchForm.days_count"
        label="Số Ngày Chiếu *"
        required
      >
        <option :value="3">3 Ngày</option>
        <option :value="7">7 Ngày (1 Tuần)</option>
        <option :value="14">14 Ngày (2 Tuần)</option>
      </BaseSelect>
    </div>
  </div>
</template>

<script setup lang="ts">
import BaseSelect from '../../base/BaseSelect.vue';
import BaseInput from '../../base/BaseInput.vue';

defineProps<{
  batchForm: any;
  cinemasList: any[];
}>();

defineEmits<{
  (e: 'toggle-select-all'): void;
}>();
</script>
