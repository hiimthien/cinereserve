<template>
  <div class="space-y-6">
    
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
      <div>
        <h2 class="text-xl font-extrabold text-white">Quản Lý Rạp & Ma Trận Ghế Phòng Chiếu</h2>
        <p class="text-xs text-slate-400 mt-0.5">Cấu hình số hàng, số ghế và phân bổ khu vực Ghế Thường, VIP Prime, Couple Sweetbox</p>
      </div>

      <button 
        @click="handleSaveMatrix"
        :disabled="isSaving"
        class="px-5 py-2.5 rounded-2xl bg-emerald-600 hover:bg-emerald-500 disabled:opacity-40 text-white font-black text-xs shadow-glow-green transition-all cursor-pointer flex items-center gap-2"
      >
        <Save class="w-4 h-4" />
        <span>{{ isSaving ? 'Đang Lưu...' : 'Lưu Cấu Hình Ma Trận Ghế' }}</span>
      </button>
    </div>

    <!-- Cinema & Room Selector -->
    <div class="p-5 rounded-3xl bg-[#11192e] border border-white/5 grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <label class="text-xs font-bold text-slate-300 block mb-1.5">Chọn Cụm Rạp:</label>
        <select 
          v-model="selectedCinemaId"
          @change="onCinemaChange"
          class="w-full bg-slate-900 border border-white/10 rounded-2xl p-3 text-xs text-white font-bold outline-none cursor-pointer"
        >
          <option v-for="c in cinemas" :key="c.id" :value="c.id">{{ c.name }} ({{ c.city }})</option>
        </select>
      </div>

      <div>
        <label class="text-xs font-bold text-slate-300 block mb-1.5">Chọn Phòng Chiếu:</label>
        <select 
          v-model="selectedRoomId"
          @change="fetchRoomSeats"
          class="w-full bg-slate-900 border border-white/10 rounded-2xl p-3 text-xs text-white font-bold outline-none cursor-pointer"
        >
          <option v-for="r in currentCinemaRooms" :key="r.id" :value="r.id">
            {{ r.name }} • Sức chứa hiện tại: {{ r.total_seats || 80 }} ghế
          </option>
        </select>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
      
      <!-- Left Config Controls (5 Cols) -->
      <div class="lg:col-span-5 p-6 rounded-3xl bg-[#11192e] border border-white/5 space-y-6 shadow-2xl">
        <h3 class="text-base font-extrabold text-white">Bảng Cấu Hình Kích Thước</h3>

        <!-- Number of Rows Slider -->
        <div class="space-y-2">
          <div class="flex justify-between text-xs font-bold">
            <span class="text-slate-300">Tổng Số Hàng Ghế (A - {{ rowLetters[config.total_rows - 1] }}):</span>
            <span class="text-amber-400 font-mono">{{ config.total_rows }} hàng</span>
          </div>
          <input 
            v-model.number="config.total_rows"
            type="range"
            min="4"
            max="10"
            class="w-full accent-rose-500 cursor-pointer"
          />
        </div>

        <!-- Seats per Row Slider -->
        <div class="space-y-2">
          <div class="flex justify-between text-xs font-bold">
            <span class="text-slate-300">Số Ghế Mỗi Hàng:</span>
            <span class="text-amber-400 font-mono">{{ config.seats_per_row }} ghế</span>
          </div>
          <input 
            v-model.number="config.seats_per_row"
            type="range"
            min="8"
            max="16"
            class="w-full accent-rose-500 cursor-pointer"
          />
        </div>

        <!-- VIP Rows Selection -->
        <div class="space-y-2 pt-2 border-t border-white/5">
          <span class="text-xs font-bold text-amber-300 block">👑 Chọn Các Hàng Ghế VIP Prime (+20K):</span>
          <div class="flex flex-wrap gap-2">
            <button 
              v-for="r in availableRowLetters" 
              :key="r"
              type="button"
              @click="toggleVipRow(r)"
              class="w-8 h-8 rounded-xl font-bold text-xs transition-all cursor-pointer flex items-center justify-center"
              :class="config.vip_rows.includes(r) ? 'bg-amber-500 text-slate-950 font-black shadow-glow-gold' : 'bg-slate-900 text-slate-400 hover:text-white border border-white/5'"
            >
              {{ r }}
            </button>
          </div>
        </div>

        <!-- Couple Rows Selection -->
        <div class="space-y-2 pt-2 border-t border-white/5">
          <span class="text-xs font-bold text-pink-300 block">💖 Chọn Các Hàng Ghế Đôi Sweetbox (220K):</span>
          <div class="flex flex-wrap gap-2">
            <button 
              v-for="r in availableRowLetters" 
              :key="r"
              type="button"
              @click="toggleCoupleRow(r)"
              class="w-8 h-8 rounded-xl font-bold text-xs transition-all cursor-pointer flex items-center justify-center"
              :class="config.couple_rows.includes(r) ? 'bg-pink-600 text-white font-black shadow-glow-accent' : 'bg-slate-900 text-slate-400 hover:text-white border border-white/5'"
            >
              {{ r }}
            </button>
          </div>
        </div>

        <div class="p-3 rounded-2xl bg-black/40 border border-white/10 text-xs text-slate-400 space-y-1 font-mono">
          <div>Tổng số ghế ước tính: <strong class="text-white">{{ calculatedTotalSeats }} ghế</strong></div>
          <div class="text-[11px] text-emerald-400">Standard: {{ seatTypeCounts.standard }} • VIP: {{ seatTypeCounts.vip }} • Couple: {{ seatTypeCounts.couple }}</div>
        </div>
      </div>

      <!-- Right Visual Grid Preview (7 Cols) -->
      <div class="lg:col-span-7 p-6 rounded-3xl bg-[#11192e] border border-white/5 space-y-6 shadow-2xl flex flex-col items-center">
        <div class="w-full flex items-center justify-between">
          <h3 class="text-base font-extrabold text-white">Sơ Đồ Ma Trận Trực Quan</h3>
          <span class="text-xs text-emerald-400 font-bold bg-emerald-500/10 px-2.5 py-1 rounded-full border border-emerald-500/20">
            Live Preview
          </span>
        </div>

        <!-- Curved Cinema Screen -->
        <div class="w-4/5 text-center space-y-2">
          <div class="h-2 w-full bg-gradient-to-r from-transparent via-cyan-400 to-transparent rounded-full shadow-[0_0_20px_rgba(6,182,212,0.5)]"></div>
          <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest block">Màn Hình Chiếu (Screen)</span>
        </div>

        <!-- Grid Matrix Preview Container -->
        <div class="w-full overflow-x-auto p-4 bg-slate-950/80 rounded-2xl border border-white/5 flex justify-center">
          <div class="space-y-2.5 py-2">
            <div 
              v-for="r in availableRowLetters" 
              :key="r"
              class="flex items-center gap-2"
            >
              <span class="w-4 text-center font-bold text-xs text-slate-500">{{ r }}</span>

              <div class="flex items-center gap-1.5">
                <template v-if="config.couple_rows.includes(r)">
                  <div 
                    v-for="n in Math.floor(config.seats_per_row / 2)" 
                    :key="n"
                    class="w-12 h-6 rounded-t-md bg-gradient-to-r from-pink-900/60 to-rose-900/60 border border-pink-500 text-[9px] font-bold text-pink-300 flex items-center justify-center shadow-sm"
                  >
                    {{ r }}{{ n }} (Đôi)
                  </div>
                </template>

                <template v-else-if="config.vip_rows.includes(r)">
                  <div 
                    v-for="n in config.seats_per_row" 
                    :key="n"
                    class="w-6 h-6 rounded-t-md bg-gradient-to-b from-amber-500/30 to-amber-950/50 border border-amber-400 text-[10px] font-extrabold text-amber-300 flex items-center justify-center shadow-[0_0_6px_rgba(245,158,11,0.2)]"
                  >
                    {{ n }}
                  </div>
                </template>

                <template v-else>
                  <div 
                    v-for="n in config.seats_per_row" 
                    :key="n"
                    class="w-6 h-6 rounded-t-md bg-slate-800 border border-slate-700 text-[10px] font-bold text-slate-400 flex items-center justify-center"
                  >
                    {{ n }}
                  </div>
                </template>
              </div>

              <span class="w-4 text-center font-bold text-xs text-slate-500">{{ r }}</span>
            </div>
          </div>
        </div>

        <!-- Legend -->
        <div class="flex items-center gap-4 text-xs font-bold text-slate-400">
          <div class="flex items-center gap-1.5">
            <span class="w-3 h-3 rounded-sm bg-slate-800 border border-slate-700"></span>
            <span>Tiêu Chuẩn</span>
          </div>
          <div class="flex items-center gap-1.5">
            <span class="w-3 h-3 rounded-sm bg-amber-500 border border-amber-400"></span>
            <span class="text-amber-300">VIP Prime</span>
          </div>
          <div class="flex items-center gap-1.5">
            <span class="w-3 h-3 rounded-sm bg-pink-600 border border-pink-500"></span>
            <span class="text-pink-300">Couple Đôi</span>
          </div>
        </div>

      </div>

    </div>

  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Save } from 'lucide-vue-next';
import api from '../../services/api';
import { useToast } from '../../composables/useToast';

const toast = useToast();

const cinemas = ref<any[]>([]);
const selectedCinemaId = ref<number>(1);
const selectedRoomId = ref<number>(1);
const isSaving = ref(false);

const rowLetters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K'];

const config = ref({
  total_rows: 8,
  seats_per_row: 12,
  vip_rows: ['E', 'F', 'G'],
  couple_rows: ['J'],
});

const currentCinemaRooms = computed(() => {
  const c = cinemas.value.find(item => item.id === Number(selectedCinemaId.value));
  return c?.rooms || [];
});

const availableRowLetters = computed(() => {
  return rowLetters.slice(0, config.value.total_rows);
});

const calculatedTotalSeats = computed(() => {
  let count = 0;
  for (const r of availableRowLetters.value) {
    if (config.value.couple_rows.includes(r)) {
      count += Math.floor(config.value.seats_per_row / 2);
    } else {
      count += config.value.seats_per_row;
    }
  }
  return count;
});

const seatTypeCounts = computed(() => {
  let std = 0;
  let vip = 0;
  let couple = 0;
  for (const r of availableRowLetters.value) {
    if (config.value.couple_rows.includes(r)) {
      couple += Math.floor(config.value.seats_per_row / 2);
    } else if (config.value.vip_rows.includes(r)) {
      vip += config.value.seats_per_row;
    } else {
      std += config.value.seats_per_row;
    }
  }
  return { standard: std, vip, couple };
});

const toggleVipRow = (row: string) => {
  if (config.value.couple_rows.includes(row)) {
    config.value.couple_rows = config.value.couple_rows.filter(r => r !== row);
  }
  if (config.value.vip_rows.includes(row)) {
    config.value.vip_rows = config.value.vip_rows.filter(r => r !== row);
  } else {
    config.value.vip_rows.push(row);
  }
};

const toggleCoupleRow = (row: string) => {
  if (config.value.vip_rows.includes(row)) {
    config.value.vip_rows = config.value.vip_rows.filter(r => r !== row);
  }
  if (config.value.couple_rows.includes(row)) {
    config.value.couple_rows = config.value.couple_rows.filter(r => r !== row);
  } else {
    config.value.couple_rows.push(row);
  }
};

const onCinemaChange = () => {
  if (currentCinemaRooms.value.length > 0) {
    selectedRoomId.value = currentCinemaRooms.value[0].id;
    fetchRoomSeats();
  }
};

const fetchCinemas = async () => {
  try {
    const res = await api.get('/admin/rooms');
    if (res.data?.success && res.data.data.length > 0) {
      cinemas.value = res.data.data;
      selectedCinemaId.value = cinemas.value[0].id;
      if (cinemas.value[0].rooms?.length > 0) {
        selectedRoomId.value = cinemas.value[0].rooms[0].id;
      }
    }
  } catch (err) {
    console.warn('Failed to fetch cinemas', err);
  }
};

const fetchRoomSeats = async () => {
  // Can populate initial sliders if needed
};

const handleSaveMatrix = async () => {
  isSaving.value = true;
  try {
    const res = await api.post(`/admin/rooms/${selectedRoomId.value}/seat-matrix`, {
      total_rows: config.value.total_rows,
      seats_per_row: config.value.seats_per_row,
      vip_rows: config.value.vip_rows,
      couple_rows: config.value.couple_rows,
    });
    toast.success(res.data?.message || 'Cấu hình ma trận ghế thành công!', 'Thành Công');
    fetchCinemas();
  } catch (err: any) {
    toast.error(err.response?.data?.message || 'Không thể lưu ma trận ghế.', 'Lỗi Cấu Hình');
  } finally {
    isSaving.value = false;
  }
};

onMounted(() => {
  fetchCinemas();
});
</script>
