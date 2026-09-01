<template>
  <div class="bg-cinema-surface/60 border border-cinema-border rounded-3xl p-6 backdrop-blur-md space-y-5">
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-2">
        <Popcorn class="w-5 h-5 text-cinema-gold" />
        <h2 class="text-base font-bold text-white">Combo Bắp & Nước (Tùy chọn)</h2>
      </div>
      <span class="text-xs text-emerald-400 font-bold bg-emerald-500/10 px-2.5 py-1 rounded-full border border-emerald-500/20">
        Tiết kiệm tới 20%
      </span>
    </div>

    <!-- Loading Skeleton -->
    <div v-if="isLoading" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div v-for="i in 4" :key="i" class="h-36 bg-cinema-card/40 rounded-2xl animate-pulse"></div>
    </div>

    <!-- Spacious 2-Column Grid -->
    <div v-else class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div 
        v-for="combo in comboList" 
        :key="combo.id"
        class="flex flex-col justify-between p-4 rounded-2xl bg-cinema-card/50 border transition-all duration-300 space-y-3"
        :class="[
          (selectedCombos[combo.id] || 0) > 0
            ? 'border-cinema-accent/60 bg-cinema-accent/5 shadow-glow-accent ring-1 ring-cinema-accent/30'
            : 'border-white/5 hover:border-white/20 hover:bg-cinema-card/70'
        ]"
      >
        <!-- Top Row: Thumbnail + Title & Description -->
        <div class="flex items-start gap-3.5">
          <img 
            :src="combo.image_url || 'https://images.unsplash.com/photo-1578849278619-e73505e9610f?w=300'" 
            :alt="combo.name"
            class="w-14 h-14 object-cover rounded-xl border border-white/10 shrink-0 shadow-md"
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

        <!-- Bottom Row: Price & Pill Stepper Controls -->
        <div class="flex items-center justify-between pt-2 border-t border-white/5">
          <span class="text-sm font-black text-amber-400 whitespace-nowrap">
            {{ formatVnd(combo.price) }}
          </span>
          
          <!-- Stepper Capsule -->
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
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Popcorn, Plus, Minus } from 'lucide-vue-next';
import api from '../services/api';

const emit = defineEmits<{
  (e: 'update:total', total: number): void;
  (e: 'update:combos', combos: any[]): void;
}>();

interface ComboItem {
  id: number | string;
  name: string;
  description: string;
  price: number;
  image_url?: string;
  badge?: string;
}

const comboList = ref<ComboItem[]>([
  { id: 1, name: 'Solo Combo', description: '1 Bắp ngọt nóng hổi (60oz) + 1 Ly nước ngọt có ga (22oz)', price: 69000, badge: 'Tiết Kiệm', image_url: 'https://images.unsplash.com/photo-1578849278619-e73505e9610f?w=300' },
  { id: 2, name: 'Couple Combo', description: '1 Bắp phô mai size L (85oz) + 2 Ly nước ngọt (32oz)', price: 109000, badge: 'Phổ Biến Nhất', image_url: 'https://images.unsplash.com/photo-1585647347483-22b66260dfff?w=300' },
  { id: 3, name: 'Party VIP Combo', description: '2 Bắp Caramel lớn + 4 Nước + 1 Khoai tây chiên', price: 169000, badge: 'Ưu Đãi Nhóm', image_url: 'https://images.unsplash.com/photo-1505686994434-e3cc5abf1330?w=300' },
  { id: 4, name: 'Nachos Cheese Combo', description: '1 Khay bánh bắp sốt phô mai & salsa + 1 Nước ngọt lớn', price: 89000, badge: 'Món Mới', image_url: 'https://images.unsplash.com/photo-1513456852971-30c0b8199d4d?w=300' },
]);

const selectedCombos = ref<Record<string | number, number>>({});
const isLoading = ref(false);

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

const notifyParent = () => {
  let sum = 0;
  const list = [];
  for (const combo of comboList.value) {
    const qty = selectedCombos.value[combo.id] || 0;
    if (qty > 0) {
      sum += qty * combo.price;
      list.push({
        id: combo.id,
        name: combo.name,
        price: combo.price,
        quantity: qty,
      });
    }
  }
  emit('update:total', sum);
  emit('update:combos', list);
};

onMounted(() => {
  fetchCombos();
});
</script>
