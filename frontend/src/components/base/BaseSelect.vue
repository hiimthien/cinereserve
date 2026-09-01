<template>
  <div class="space-y-1.5" :class="containerClass">
    <label v-if="label" class="block text-xs font-semibold text-cinema-muted">
      {{ label }}
    </label>

    <div class="relative">
      <div v-if="$slots.prefix" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
        <slot name="prefix" />
      </div>

      <select
        :value="modelValue"
        :disabled="disabled"
        :required="required"
        class="w-full bg-slate-900/90 border border-cinema-border rounded-xl px-3.5 py-2.5 text-xs text-white outline-none cursor-pointer appearance-none transition-colors focus:border-cinema-accent pr-9 disabled:opacity-50 disabled:cursor-not-allowed"
        :class="[
          $slots.prefix ? 'pl-9' : '',
          error ? 'border-red-500/50 focus:border-red-500' : ''
        ]"
        @change="$emit('update:modelValue', ($event.target as HTMLSelectElement).value)"
      >
        <slot />
      </select>

      <div class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
        <ChevronDown class="w-4 h-4" />
      </div>
    </div>

    <p v-if="error" class="text-[11px] text-rose-400 font-medium">{{ error }}</p>
  </div>
</template>

<script setup lang="ts">
import { ChevronDown } from 'lucide-vue-next';

interface Props {
  modelValue?: string | number;
  label?: string;
  error?: string;
  disabled?: boolean;
  required?: boolean;
  containerClass?: string;
}

defineProps<Props>();

defineEmits<{
  (e: 'update:modelValue', val: string | number): void;
}>();
</script>
