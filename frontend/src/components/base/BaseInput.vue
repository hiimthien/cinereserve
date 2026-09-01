<template>
  <div class="space-y-1.5 w-full">
    <!-- Label with Clean Single Asterisk -->
    <label v-if="label" class="block text-xs font-bold text-cinema-muted uppercase tracking-wider pl-1">
      {{ cleanLabel }}
      <span v-if="required" class="text-cinema-accent">*</span>
    </label>

    <div class="relative flex items-center group">
      <!-- Prefix Icon -->
      <div v-if="$slots.prefix" class="absolute left-3.5 flex items-center pointer-events-none text-slate-400 group-focus-within:text-cinema-accent transition-colors">
        <slot name="prefix" />
      </div>

      <input
        ref="inputRef"
        :type="actualType"
        :value="modelValue"
        :placeholder="placeholder"
        :disabled="disabled"
        :required="required"
        :maxlength="maxlength"
        class="w-full bg-slate-900/90 hover:bg-slate-900 border rounded-2xl py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none transition-all duration-200 shadow-inner"
        :class="[
          $slots.prefix ? 'pl-10' : 'pl-4',
          ($slots.suffix || isPasswordType || clearable) ? 'pr-11' : 'pr-4',
          error 
            ? 'border-rose-500/80 ring-2 ring-rose-500/20' 
            : 'border-cinema-border hover:border-white/20 focus:border-cinema-accent focus:ring-2 focus:ring-cinema-accent/20 focus:shadow-glow-accent',
          disabled ? 'opacity-50 cursor-not-allowed' : ''
        ]"
        @input="handleInput"
        @focus="$emit('focus', $event)"
        @blur="$emit('blur', $event)"
      />

      <!-- Password Eye Toggle or Suffix Icon or Clear Button -->
      <div class="absolute right-3 flex items-center gap-1.5">
        <!-- Clear Text Button -->
        <button
          v-if="clearable && modelValue && !disabled"
          type="button"
          @click="clearText"
          class="p-1 rounded-lg text-slate-400 hover:text-white hover:bg-white/10 transition-colors cursor-pointer"
          tabindex="-1"
          aria-label="Xóa nội dung"
        >
          <X class="w-3.5 h-3.5" />
        </button>

        <!-- Eye toggle for password -->
        <button
          v-if="isPasswordType && !disabled"
          type="button"
          @click="showPassword = !showPassword"
          class="p-1 rounded-lg text-slate-400 hover:text-white hover:bg-white/10 transition-colors cursor-pointer"
          tabindex="-1"
          aria-label="Hiện/Ẩn mật khẩu"
        >
          <component :is="showPassword ? EyeOff : Eye" class="w-3.5 h-3.5" />
        </button>

        <!-- Custom Suffix Slot -->
        <div v-if="$slots.suffix" class="text-slate-400 pointer-events-none">
          <slot name="suffix" />
        </div>
      </div>
    </div>

    <!-- Error message or helper text -->
    <p v-if="error" class="text-[11px] text-rose-400 font-semibold pl-1 flex items-center gap-1">
      <span>{{ error }}</span>
    </p>
    <p v-else-if="helper" class="text-[11px] text-cinema-muted pl-1">{{ helper }}</p>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { Eye, EyeOff, X } from 'lucide-vue-next';

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
  clearable?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  label: '',
  placeholder: '',
  type: 'text',
  disabled: false,
  required: false,
  error: '',
  helper: '',
  clearable: false,
});

const emit = defineEmits<{
  (e: 'update:modelValue', value: string): void;
  (e: 'focus', event: FocusEvent): void;
  (e: 'blur', event: FocusEvent): void;
}>();

const inputRef = ref<HTMLInputElement | null>(null);
const showPassword = ref(false);

const cleanLabel = computed(() => {
  return props.label ? props.label.replace(/\s*\*+\s*$/, '') : '';
});

const isPasswordType = computed(() => props.type === 'password');
const actualType = computed(() => {
  if (isPasswordType.value) {
    return showPassword.value ? 'text' : 'password';
  }
  return props.type;
});

const handleInput = (event: Event) => {
  const target = event.target as HTMLInputElement;
  emit('update:modelValue', target.value);
};

const clearText = () => {
  emit('update:modelValue', '');
  inputRef.value?.focus();
};

defineExpose({
  focus: () => inputRef.value?.focus(),
  blur: () => inputRef.value?.blur(),
});
</script>
