<template>
  <BaseModal 
    :model-value="modelValue" 
    @update:model-value="$emit('update:modelValue', $event)"
    :title="`Lịch Chiếu Phim: ${movie?.title || ''}`"
    maxWidth="4xl"
  >
    <div v-if="movie" class="space-y-5 p-1">
      <!-- Movie Summary Banner -->
      <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between p-4 rounded-2xl bg-slate-900/90 border border-white/10 gap-4">
        <div class="flex items-center gap-3 min-w-0">
          <img :src="movie.poster_url" class="w-12 h-16 object-cover rounded-xl border border-white/10 shrink-0" />
          <div class="min-w-0">
            <h4 class="font-extrabold text-white text-base leading-tight truncate">{{ movie.title }}</h4>
            <p class="text-xs text-cinema-muted">{{ movie.duration }} phút • Khởi chiếu: {{ formatDate(movie.release_date) }}</p>
          </div>
        </div>

        <div class="flex items-center gap-2 shrink-0">
          <BaseButton 
            variant="primary" 
            size="sm"
            @click="$emit('add-showtime', movie)"
          >
            <template #prefix><Plus class="w-3.5 h-3.5" /></template>
            <span>Thêm Suất Mới</span>
          </BaseButton>
        </div>
      </div>

      <!-- Showtimes List Table -->
      <div class="max-h-[550px] overflow-y-auto rounded-2xl border border-white/5 bg-slate-900/50">
        <table class="w-full text-left text-xs text-slate-300">
          <thead class="sticky top-0 bg-slate-950 text-[11px] uppercase tracking-wider text-slate-400 border-b border-white/10 shadow-md">
            <tr>
              <th class="p-3.5">Cụm Rạp & Phòng</th>
              <th class="p-3.5">Ngày & Giờ Chiếu</th>
              <th class="p-3.5">Định Dạng</th>
              <th class="p-3.5">Loại Suất</th>
              <th class="p-3.5">Giá Vé (Thường / VIP / Đôi)</th>
              <th class="p-3.5 text-right">Thao Tác</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-white/5 font-medium">
            <tr v-if="showtimes.length === 0">
              <td colspan="6" class="p-8 text-center text-slate-500 italic space-y-3">
                <p>Chưa có suất chiếu nào được tạo cho phim này.</p>
                <BaseButton 
                  variant="primary" 
                  size="sm"
                  @click="$emit('add-showtime', movie)"
                >
                  <template #prefix><Plus class="w-3.5 h-3.5" /></template>
                  <span>Tạo Lịch Chiếu Ngay</span>
                </BaseButton>
              </td>
            </tr>
            <tr 
              v-else
              v-for="st in showtimes" 
              :key="st.id"
              class="hover:bg-white/5 transition-colors"
            >
              <td class="p-3.5">
                <strong class="text-white block text-sm">{{ st.cinema?.name || 'Cụm rạp' }}</strong>
                <span class="text-xs text-slate-400">{{ st.room?.name || 'Phòng 1' }} ({{ st.room?.room_type || '2D' }})</span>
              </td>
              <td class="p-3.5">
                <span class="text-amber-400 font-mono font-bold text-sm block">{{ st.start_time }}</span>
                <span class="text-xs text-slate-400 font-mono">{{ formatDate(st.show_date) }}</span>
              </td>
              <td class="p-3.5">
                <BaseBadge variant="rose" size="xs">{{ st.format || '2D' }}</BaseBadge>
              </td>
              <td class="p-3.5">
                <BaseBadge :variant="st.status === 'early_premiere' ? 'purple' : 'emerald'" size="xs">
                  {{ st.status === 'early_premiere' ? '✨ Chiếu Sớm' : '🟢 Tiêu Chuẩn' }}
                </BaseBadge>
              </td>
              <td class="p-3.5 font-mono text-xs space-y-0.5">
                <div class="text-emerald-400 font-bold">💺 {{ formatVnd(st.base_price) }}</div>
                <div class="text-amber-300">👑 {{ formatVnd(st.price_vip || (st.base_price + 15000)) }}</div>
                <div class="text-rose-300">💖 {{ formatVnd(st.price_couple || (st.base_price * 2)) }}</div>
              </td>
              <td class="p-3.5 text-right space-x-2">
                <button 
                  @click="$emit('edit-showtime', st)"
                  class="p-2 rounded-xl bg-white/5 hover:bg-white/15 text-slate-300 hover:text-white transition-colors cursor-pointer"
                  title="Chỉnh sửa suất chiếu này"
                >
                  <Edit2 class="w-4 h-4" />
                </button>
                <button 
                  @click="$emit('delete-showtime', st.id)"
                  class="p-2 rounded-xl bg-rose-500/10 hover:bg-rose-500/25 text-rose-400 transition-colors cursor-pointer"
                  title="Xóa suất chiếu này"
                >
                  <Trash2 class="w-4 h-4" />
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="flex justify-end pt-2">
        <BaseButton variant="secondary" size="md" @click="$emit('update:modelValue', false)">
          Đóng
        </BaseButton>
      </div>
    </div>
  </BaseModal>
</template>

<script setup lang="ts">
import { Plus, Edit2, Trash2 } from 'lucide-vue-next';
import BaseModal from '../../base/BaseModal.vue';
import BaseBadge from '../../base/BaseBadge.vue';
import BaseButton from '../../base/BaseButton.vue';
import { formatDate, formatVnd } from '../../../utils/formatters';

defineProps<{
  modelValue: boolean;
  movie: any;
  showtimes: any[];
}>();

defineEmits<{
  (e: 'update:modelValue', val: boolean): void;
  (e: 'add-showtime', movie: any): void;
  (e: 'edit-showtime', showtime: any): void;
  (e: 'delete-showtime', id: number): void;
}>();
</script>
