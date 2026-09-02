<template>
  <div class="bg-cinema-surface/60 border border-cinema-border rounded-3xl p-6 backdrop-blur-md space-y-4">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-2">
        <Popcorn class="w-5 h-5 text-cinema-gold" />
        <h2 class="text-base font-bold text-white">Combo Bắp & Nước (Tùy chọn)</h2>
      </div>
      <span class="text-xs text-emerald-400 font-bold bg-emerald-500/10 px-2.5 py-1 rounded-full border border-emerald-500/20">
        Tiết kiệm tới 20%
      </span>
    </div>

    <!-- State 1: No snacks selected (Compact inviting trigger banner) -->
    <div 
      v-if="selectedList.length === 0"
      class="p-4 rounded-2xl bg-cinema-card/40 border border-white/5 hover:border-amber-500/30 transition-all flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4"
    >
      <div class="flex items-center gap-3.5">
        <div class="w-12 h-12 rounded-2xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center shrink-0 text-amber-400 shadow-inner">
          <Popcorn class="w-6 h-6" />
        </div>
        <div>
          <h4 class="text-xs font-bold text-white">Chưa chọn bắp nước kèm vé</h4>
          <p class="text-[11px] text-cinema-muted mt-0.5">
            Thưởng thức bắp rang bơ nóng hổi & nước ngọt mát lạnh trong suốt bộ phim.
          </p>
        </div>
      </div>

      <BaseButton 
        variant="primary" 
        size="sm" 
        @click="isModalOpen = true"
        class="shrink-0 w-full sm:w-auto"
      >
        <template #prefix><Plus class="w-3.5 h-3.5" /></template>
        <span>Chọn Bắp & Nước</span>
      </BaseButton>
    </div>

    <!-- State 2: Snacks selected (Detailed summary list) -->
    <div v-else class="space-y-3">
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
        <div 
          v-for="item in selectedList" 
          :key="item.id"
          class="flex items-center justify-between p-3 rounded-2xl bg-slate-900/80 border border-amber-500/30 shadow-glow-accent"
        >
          <div class="flex items-center gap-2.5 min-w-0">
            <img 
              :src="item.image_url || 'https://images.unsplash.com/photo-1578849278619-e73505e9610f?w=200'" 
              :alt="item.name"
              class="w-10 h-10 object-cover rounded-xl border border-white/10 shrink-0" 
            />
            <div class="min-w-0">
              <h5 class="text-xs font-bold text-white truncate">{{ item.name }}</h5>
              <span class="text-[10px] text-amber-400 font-mono font-bold">{{ formatVnd(item.price) }} × {{ item.quantity }}</span>
            </div>
          </div>

          <span class="text-xs font-black text-white font-mono shrink-0 ml-2">
            {{ formatVnd(item.price * item.quantity) }}
          </span>
        </div>
      </div>

      <!-- Action buttons & subtotal strip -->
      <div class="flex items-center justify-between pt-2 border-t border-white/5 text-xs">
        <div class="flex items-center gap-2">
          <span class="text-cinema-muted">Tổng bắp nước:</span>
          <span class="font-mono font-black text-amber-400">{{ formatVnd(selectedTotal) }}</span>
        </div>

        <div class="flex items-center gap-2">
          <button 
            @click="clearAllCombos"
            type="button"
            class="text-[11px] text-slate-400 hover:text-rose-400 transition-colors cursor-pointer px-2 py-1"
          >
            Xóa hết
          </button>
          <BaseButton 
            variant="secondary" 
            size="sm" 
            @click="isModalOpen = true"
          >
            <template #prefix><PenLine class="w-3.5 h-3.5 text-cinema-accent" /></template>
            <span>Thay Đổi / Thêm</span>
          </BaseButton>
        </div>
      </div>
    </div>

    <!-- Snack Selection Modal -->
    <BaseModal 
      v-model="isModalOpen" 
      title="Thực Đơn Bắp & Nước Rạp Chiếu" 
      maxWidth="3xl"
    >
      <div class="space-y-4 p-1">
        <!-- Subtitle note -->
        <div class="flex items-center justify-between text-xs text-cinema-muted bg-white/5 p-3 rounded-2xl border border-white/5">
          <span class="flex items-center gap-1.5 text-white">
            <Popcorn class="w-4 h-4 text-amber-400" />
            <span>Nhận bắp nước trực tiếp tại quầy Fast-Track bằng mã vé xem phim</span>
          </span>
          <span class="text-emerald-400 font-bold font-mono">Ưu Đãi Online</span>
        </div>

        <!-- Loading Skeleton -->
        <div v-if="isLoading" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div v-for="i in 4" :key="i" class="h-36 bg-cinema-card/40 rounded-2xl animate-pulse"></div>
        </div>

        <!-- Combos Grid -->
        <div v-else class="grid grid-cols-1 sm:grid-cols-2 gap-4 max-h-[50vh] overflow-y-auto pr-1">
          <div 
            v-for="combo in comboList" 
            :key="combo.id"
            class="flex flex-col justify-between p-4 rounded-2xl bg-cinema-card/60 border transition-all duration-300 space-y-3"
            :class="[
              (selectedCombos[combo.id] || 0) > 0
                ? 'border-cinema-accent/70 bg-cinema-accent/10 shadow-glow-accent ring-1 ring-cinema-accent/40'
                : 'border-white/5 hover:border-white/20 hover:bg-cinema-card/80'
            ]"
          >
            <!-- Top Row: Thumbnail + Title & Description -->
            <div class="flex items-start gap-3.5">
              <img 
                :src="combo.image_url || 'https://images.unsplash.com/photo-1578849278619-e73505e9610f?w=300'" 
                :alt="combo.name"
                class="w-16 h-16 object-cover rounded-xl border border-white/10 shrink-0 shadow-md"
              />

              <div class="space-y-1 min-w-0 flex-1">
                <div class="flex items-start justify-between gap-1.5">
                  <h4 class="text-xs font-extrabold text-white leading-snug">
                    {{ combo.name }}
                  </h4>
                </div>
                <span 
                  v-if="combo.badge" 
                  class="inline-block text-[9px] font-bold px-1.5 py-0.5 rounded bg-amber-500/15 text-amber-300 border border-amber-500/30 whitespace-nowrap"
                >
                  {{ combo.badge }}
                </span>
                <p class="text-[11px] text-cinema-muted line-clamp-2 leading-relaxed">
                  {{ combo.description }}
                </p>
              </div>
            </div>

            <!-- Bottom Row: Price & Stepper Controls -->
            <div class="flex items-center justify-between pt-2 border-t border-white/5">
              <span class="text-sm font-black text-amber-400 whitespace-nowrap">
                {{ formatVnd(combo.price) }}
              </span>
              
              <div class="flex items-center gap-1.5 bg-slate-900/90 p-1 rounded-xl border border-white/10 shadow-inner">
                <button 
                  @click="decrementCombo(combo.id)"
                  :disabled="!selectedCombos[combo.id]"
                  class="w-6 h-6 rounded-lg bg-white/5 hover:bg-white/15 text-slate-300 disabled:opacity-20 flex items-center justify-center transition-colors cursor-pointer"
                  aria-label="Giảm số lượng"
                >
                  <Minus class="w-3 h-3" />
                </button>
                
                <span class="text-xs font-bold text-white w-6 text-center select-none font-mono">
                  {{ selectedCombos[combo.id] || 0 }}
                </span>

                <button 
                  @click="incrementCombo(combo.id)"
                  class="w-6 h-6 rounded-lg bg-cinema-accent hover:bg-rose-600 text-white flex items-center justify-center transition-colors cursor-pointer shadow-glow-accent"
                  aria-label="Tăng số lượng"
                >
                  <Plus class="w-3 h-3" />
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal Footer -->
      <template #footer>
        <div class="flex items-center justify-between gap-4">
          <div class="text-xs">
            <span class="text-slate-400 block">Đã chọn {{ selectedItemsCount }} phần:</span>
            <span class="text-base font-black text-amber-400 font-mono">{{ formatVnd(selectedTotal) }}</span>
          </div>

          <BaseButton 
            variant="primary" 
            size="md" 
            @click="isModalOpen = false"
          >
            <template #prefix><Check class="w-4 h-4" /></template>
            <span>Xác Nhận & Áp Dụng</span>
          </BaseButton>
        </div>
      </template>
    </BaseModal>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Popcorn, Plus, Minus, PenLine, Check } from 'lucide-vue-next';
import BaseButton from '../base/BaseButton.vue';
import BaseModal from '../base/BaseModal.vue';
import { DEFAULT_SNACK_COMBOS, type SnackComboItem } from '../../constants';
import api from '../../services/api';

const emit = defineEmits<{
  (e: 'update:total', total: number): void;
  (e: 'update:combos', combos: any[]): void;
}>();

const isModalOpen = ref(false);
const comboList = ref<SnackComboItem[]>([...DEFAULT_SNACK_COMBOS]);

const selectedCombos = ref<Record<string | number, number>>({});
const isLoading = ref(false);

const selectedList = computed(() => {
  const list = [];
  for (const combo of comboList.value) {
    const qty = selectedCombos.value[combo.id] || 0;
    if (qty > 0) {
      list.push({
        id: combo.id,
        name: combo.name,
        price: combo.price,
        image_url: combo.image_url,
        quantity: qty,
      });
    }
  }
  return list;
});

const selectedTotal = computed(() => {
  return selectedList.value.reduce((sum, item) => sum + item.price * item.quantity, 0);
});

const selectedItemsCount = computed(() => {
  return selectedList.value.reduce((sum, item) => sum + item.quantity, 0);
});

const fetchCombos = async () => {
  isLoading.value = true;
  try {
    const res = await api.get('/snacks');
    if (res.data?.data && res.data.data.length > 0) {
      comboList.value = res.data.data;
    }
  } catch (err) {
    console.error('Failed to fetch snacks', err);
  } finally {
    isLoading.value = false;
  }
};

const formatVnd = (val: number) => {
  return new Intl.NumberFormat('vi-VN').format(val) + ' đ';
};

const incrementCombo = (id: number | string) => {
  selectedCombos.value[id] = (selectedCombos.value[id] || 0) + 1;
  notifyParent();
};

const decrementCombo = (id: number | string) => {
  if (selectedCombos.value[id] > 0) {
    selectedCombos.value[id]--;
    notifyParent();
  }
};

const clearAllCombos = () => {
  selectedCombos.value = {};
  notifyParent();
};

const notifyParent = () => {
  emit('update:total', selectedTotal.value);
  emit('update:combos', selectedList.value);
};

onMounted(() => {
  fetchCombos();
});
</script>
