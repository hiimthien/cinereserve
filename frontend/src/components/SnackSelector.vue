<template>
  <div class="bg-cinema-surface/60 border border-cinema-border rounded-3xl p-6 backdrop-blur-md space-y-4">
    <div class="flex items-center justify-between">
      <h2 class="text-base font-bold text-white flex items-center gap-2">
        <span class="w-2 h-2 rounded-full bg-cinema-gold"></span>
        Combo Bắp & Nước (Tùy chọn)
      </h2>
      <span class="text-xs text-cinema-muted">Ưu đãi giảm 15%</span>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
      <div 
        v-for="combo in combos" 
        :key="combo.id"
        class="flex flex-col justify-between p-3.5 rounded-2xl bg-cinema-card/50 border border-white/5 space-y-3"
      >
        <div class="flex items-center gap-3">
          <span class="text-2xl">{{ combo.icon }}</span>
          <div>
            <h4 class="text-xs font-bold text-white">{{ combo.name }}</h4>
            <p class="text-[11px] text-cinema-muted line-clamp-1">{{ combo.desc }}</p>
          </div>
        </div>

        <div class="flex items-center justify-between border-t border-white/5 pt-2">
          <span class="text-xs font-bold text-amber-400">${{ combo.price }}.00</span>
          
          <!-- Quantity Controls -->
          <div class="flex items-center gap-2">
            <button 
              @click="decrementCombo(combo.id)"
              :disabled="!selectedCombos[combo.id]"
              class="w-6 h-6 rounded-lg bg-slate-800 text-white flex items-center justify-center text-xs font-bold disabled:opacity-30 cursor-pointer"
            >
              -
            </button>
            <span class="text-xs font-bold text-white w-4 text-center">
              {{ selectedCombos[combo.id] || 0 }}
            </span>
            <button 
              @click="incrementCombo(combo.id)"
              class="w-6 h-6 rounded-lg bg-cinema-accent hover:bg-rose-600 text-white flex items-center justify-center text-xs font-bold transition-colors cursor-pointer"
            >
              +
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';

const emit = defineEmits(['update:total']);

const combos = [
  { id: 'popcorn', name: 'Bắp Rang Bơ Caramel', desc: 'Size Lớp 1 Popcorn ngọt/phô mai', price: 4.5, icon: '🍿' },
  { id: 'coke', name: 'Nước Ngọt Coca-Cola', desc: 'Ly lớn 32oz đá mát lạnh', price: 2.5, icon: '🥤' },
  { id: 'couple_combo', name: 'Combo Couple Siêu Tiết Kiệm', desc: '1 Bắp lớn + 2 Nước ngọt', price: 8.0, icon: '🎉' },
];

const selectedCombos = ref<Record<string, number>>({});

const incrementCombo = (id: string) => {
  selectedCombos.value[id] = (selectedCombos.value[id] || 0) + 1;
  calculateTotal();
};

const decrementCombo = (id: string) => {
  if (selectedCombos.value[id] > 0) {
    selectedCombos.value[id]--;
    calculateTotal();
  }
};

const calculateTotal = () => {
  let sum = 0;
  for (const combo of combos) {
    sum += (selectedCombos.value[combo.id] || 0) * combo.price;
  }
  emit('update:total', sum);
};
</script>
