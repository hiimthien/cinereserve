<template>
  <div class="space-y-1.5 w-full">
    <label v-if="label" class="block text-xs font-semibold text-cinema-muted">
      {{ label }}
      <span v-if="required" class="text-cinema-accent">*</span>
    </label>

    <div class="relative flex items-center">
      <!-- Prefix Icon -->
      <div v-if="$slots.prefix" class="absolute left-3.5 flex items-center pointer-events-none text-slate-400">
        <slot name="prefix" />
      </div>

      <input
        ref="inputRef"
        :type="type"
        :value="modelValue"
        :placeholder="placeholder"
        :disabled="disabled"
        :required="required"
        :maxlength="maxlength"
        class="w-full bg-cinema-card/80 border rounded-xl py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none transition-colors"
        :class="[
          $slots.prefix ? 'pl-10' : 'pl-4',
          $slots.suffix ? 'pr-10' : 'pr-4',
          error 
            ? 'border-red-500/80 focus:border-red-500' 
            : 'border-cinema-border focus:border-cinema-accent',
          disabled ? 'opacity-50 cursor-not-allowed' : ''
        ]"
        @input="handleInput"
        @focus="$emit('focus', $event)"
        @blur="$emit('blur', $event)"
      />

      <!-- Suffix Icon -->
      <div v-if="$slots.suffix" class="absolute right-3.5 flex items-center pointer-events-none text-slate-400">
        <slot name="suffix" />
      </div>
    </div>

    <!-- Error message or helper text -->
    <p v-if="error" class="text-[11px] text-red-400">{{ error }}</p>
    <p v-else-if="helper" class="text-[11px] text-cinema-muted">{{ helper }}</p>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';

interface Props {
  modelValue: string | number;
  label?: string;
  placeholder?: string;
  type?: string;
  disabled?: boolean;
  required?: boolean;
  error?: string;
  helper?: string;
  maxlength?: number;
}

withDefaults(defineProps<Props>(), {
  label: '',
  placeholder: '',
  type: 'text',
  disabled: false,
  required: false,
  error: '',
  helper: '',
});

const emit = defineEmits<{
  (e: 'update:modelValue', value: string): void;
  (e: 'focus', event: FocusEvent): void;
  (e: 'blur', event: FocusEvent): void;
}>();

const inputRef = ref<HTMLInputElement | null>(null);

const handleInput = (event: Event) => {
  const target = event.target as HTMLInputElement;
  emit('update:modelValue', target.value);
};

defineExpose({
  focus: () => inputRef.value?.focus(),
  blur: () => inputRef.value?.blur(),
});
</script>
