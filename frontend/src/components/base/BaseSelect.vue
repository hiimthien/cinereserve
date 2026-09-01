<template>
  <div class="space-y-1.5 select-none relative w-full" :class="containerClass">
    <!-- Optional Label with Single Clean Asterisk -->
    <label v-if="label" class="block text-xs font-bold text-cinema-muted uppercase tracking-wider pl-1">
      {{ cleanLabel }}
      <span v-if="required" class="text-cinema-accent">*</span>
    </label>

    <!-- Hidden native select for slot harvesting and form binding -->
    <select
      ref="hiddenSelectRef"
      class="hidden"
      :value="modelValue"
      :disabled="disabled"
      :required="required"
    >
      <slot />
    </select>

    <!-- Trigger Button -->
    <button
      ref="triggerBtnRef"
      type="button"
      :disabled="disabled"
      @click="toggleDropdown"
      class="w-full bg-slate-900/90 hover:bg-slate-900 border rounded-2xl px-4 py-2.5 text-xs text-left font-medium transition-all duration-200 flex items-center justify-between gap-2 cursor-pointer shadow-inner group"
      :class="[
        isOpen 
          ? 'border-cinema-accent ring-2 ring-cinema-accent/20 bg-slate-900 shadow-glow-accent' 
          : 'border-cinema-border hover:border-white/20',
        error ? 'border-rose-500/60 ring-2 ring-rose-500/20' : '',
        disabled ? 'opacity-50 cursor-not-allowed' : ''
      ]"
    >
      <!-- Left Side: Prefix Icon + Selected Label -->
      <div class="flex items-center gap-2.5 min-w-0 flex-1">
        <div v-if="$slots.prefix" class="text-slate-400 group-hover:text-cinema-accent transition-colors shrink-0">
          <slot name="prefix" />
        </div>
        
        <span 
          class="truncate block"
          :class="selectedOption ? 'text-white font-semibold' : 'text-slate-500'"
        >
          {{ selectedOption?.label || placeholder || 'Chọn tùy chọn...' }}
        </span>
      </div>

      <!-- Right Side: Chevron Indicator -->
      <ChevronDown 
        class="w-4 h-4 text-slate-400 group-hover:text-white transition-transform duration-200 shrink-0"
        :class="{ 'rotate-180 text-cinema-accent': isOpen }"
      />
    </button>

    <!-- Error Message -->
    <p v-if="error" class="text-[11px] text-rose-400 font-semibold pl-1 flex items-center gap-1">
      <span>{{ error }}</span>
    </p>
    <p v-else-if="helper" class="text-[11px] text-cinema-muted pl-1">{{ helper }}</p>

    <!-- Teleported Floating Dropdown Menu (Guarantees zero modal scroll overflow) -->
    <Teleport to="body">
      <div 
        v-if="isOpen"
        ref="popoverRef"
        :style="popoverStyle"
        class="fixed z-[9999] bg-[#0b1120] border border-white/10 rounded-2xl backdrop-blur-2xl shadow-2xl flex flex-col max-h-64 overflow-hidden animate-in fade-in zoom-in-95 duration-150 select-none"
        @click.stop
      >
        <!-- Dedicated Non-scrolling Header: Search filter if more than 5 options -->
        <div v-if="allOptions.length > 5" class="p-2 border-b border-white/10 shrink-0 bg-[#0b1120]">
          <div class="relative">
            <input 
              ref="searchInputRef"
              v-model="searchFilter"
              type="text"
              placeholder="Tìm kiếm nhanh..."
              class="w-full bg-slate-950/80 border border-white/10 rounded-xl pl-8 pr-3 py-1.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-cinema-accent transition-colors"
            />
            <Search class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2" />
          </div>
        </div>

        <!-- Dedicated Scrollable Body: Options List -->
        <div class="flex-1 overflow-y-auto p-1.5 space-y-1 scrollbar-thin scrollbar-thumb-white/10">
          <div v-if="filteredOptions.length === 0" class="p-3 text-center text-xs text-slate-500 italic">
            Không tìm thấy kết quả
          </div>

          <button
            v-for="opt in filteredOptions"
            :key="String(opt.value)"
            type="button"
            @click="selectOption(opt)"
            class="w-full px-3 py-2 rounded-xl text-xs flex items-center justify-between text-left transition-all cursor-pointer group/item"
            :class="[
              String(modelValue) === String(opt.value)
                ? 'bg-gradient-to-r from-cinema-accent to-rose-600 text-white font-bold shadow-md'
                : 'text-slate-300 hover:text-white hover:bg-white/5 font-medium'
            ]"
          >
            <span class="truncate">{{ opt.label }}</span>
            <Check 
              v-if="String(modelValue) === String(opt.value)" 
              class="w-3.5 h-3.5 text-white shrink-0" 
            />
          </button>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, nextTick, watch } from 'vue';
import { ChevronDown, Check, Search } from 'lucide-vue-next';

export interface SelectOption {
  label: string;
  value: string | number;
}

interface Props {
  modelValue?: string | number;
  options?: ReadonlyArray<SelectOption | string | { readonly label: string; readonly value: string | number }> | Array<SelectOption | string>;
  label?: string;
  placeholder?: string;
  error?: string;
  helper?: string;
  disabled?: boolean;
  required?: boolean;
  containerClass?: string;
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: '',
  label: '',
  placeholder: '',
  error: '',
  helper: '',
  disabled: false,
  required: false,
  containerClass: '',
});

const emit = defineEmits<{
  (e: 'update:modelValue', val: string | number): void;
  (e: 'change', val: string | number): void;
}>();

const isOpen = ref(false);
const searchFilter = ref('');
const triggerBtnRef = ref<HTMLButtonElement | null>(null);
const popoverRef = ref<HTMLElement | null>(null);
const hiddenSelectRef = ref<HTMLSelectElement | null>(null);
const searchInputRef = ref<HTMLInputElement | null>(null);
const slotOptions = ref<SelectOption[]>([]);
const popoverStyle = ref<Record<string, string>>({});

const cleanLabel = computed(() => {
  return props.label ? props.label.replace(/\s*\*+\s*$/, '') : '';
});

// Calculate fixed position on screen so modal scroll is NEVER affected
const updatePosition = () => {
  if (!triggerBtnRef.value) return;
  const rect = triggerBtnRef.value.getBoundingClientRect();
  popoverStyle.value = {
    top: `${rect.bottom + 6}px`,
    left: `${rect.left}px`,
    width: `${rect.width}px`,
  };
};

// Extract options from slotted <option> elements
const extractSlotOptions = () => {
  if (!hiddenSelectRef.value) return;
  const opts = hiddenSelectRef.value.querySelectorAll('option');
  const result: SelectOption[] = [];
  opts.forEach(el => {
    if (el.disabled && !el.value) return;
    result.push({
      label: el.textContent?.trim() || el.value,
      value: el.value,
    });
  });
  slotOptions.value = result;
};

// Combine prop options with slot options
const allOptions = computed<SelectOption[]>(() => {
  if (props.options && props.options.length > 0) {
    return props.options.map(opt => {
      if (typeof opt === 'object' && opt !== null) {
        return { label: (opt as any).label, value: (opt as any).value };
      }
      return { label: String(opt), value: String(opt) };
    });
  }
  return slotOptions.value;
});

const filteredOptions = computed(() => {
  if (!searchFilter.value.trim()) return allOptions.value;
  const q = searchFilter.value.toLowerCase();
  return allOptions.value.filter(o => o.label.toLowerCase().includes(q));
});

const selectedOption = computed(() => {
  return allOptions.value.find(o => String(o.value) === String(props.modelValue));
});

const toggleDropdown = async () => {
  if (props.disabled) return;
  extractSlotOptions();
  updatePosition();
  isOpen.value = !isOpen.value;
  if (isOpen.value) {
    searchFilter.value = '';
    nextTick(() => {
      updatePosition();
      searchInputRef.value?.focus();
    });
  }
};

const selectOption = (opt: SelectOption) => {
  emit('update:modelValue', opt.value);
  emit('change', opt.value);
  isOpen.value = false;
};

const handleClickOutside = (e: MouseEvent) => {
  const target = e.target as Node;
  const isTrigger = triggerBtnRef.value && triggerBtnRef.value.contains(target);
  const isPopover = popoverRef.value && popoverRef.value.contains(target);
  if (!isTrigger && !isPopover) {
    isOpen.value = false;
  }
};

const handleScrollOrResize = () => {
  if (isOpen.value) {
    updatePosition();
  }
};

onMounted(() => {
  nextTick(() => extractSlotOptions());
  document.addEventListener('click', handleClickOutside);
  window.addEventListener('resize', handleScrollOrResize);
  window.addEventListener('scroll', handleScrollOrResize, true);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
  window.removeEventListener('resize', handleScrollOrResize);
  window.removeEventListener('scroll', handleScrollOrResize, true);
});

watch(() => props.options, () => {
  nextTick(() => extractSlotOptions());
}, { deep: true });
</script>
