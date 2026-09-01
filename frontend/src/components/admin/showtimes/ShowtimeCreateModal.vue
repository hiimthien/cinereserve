<template>
  <BaseModal 
    :model-value="modelValue" 
    @update:model-value="$emit('update:modelValue', $event)"
    :title="creationMode === 'single' ? 'Tạo 1 Suất Chiếu Đơn' : '⚡ Tạo Suất Chiếu Hàng Loạt (Nhiều Rạp & Nhiều Ngày)'"
    maxWidth="4xl"
    :zIndex="60"
  >
    <div class="space-y-4">
      <!-- Mode Tabs -->
      <div class="flex rounded-2xl bg-slate-950 p-1.5 border border-white/10">
        <button 
          @click="$emit('update:creationMode', 'single')"
          class="flex-1 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer text-center"
          :class="creationMode === 'single' ? 'bg-cinema-accent text-white shadow-glow-accent' : 'text-slate-400 hover:text-white'"
        >
          Tạo 1 Suất Đơn
        </button>
        <button 
          @click="$emit('update:creationMode', 'batch')"
          class="flex-1 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer text-center"
          :class="creationMode === 'batch' ? 'bg-cinema-accent text-white shadow-glow-accent' : 'text-slate-400 hover:text-white'"
        >
          ⚡ Tạo Hàng Loạt (Nhiều Rạp & Nhiều Khung Giờ)
        </button>
      </div>

      <!-- 2-Column Responsive Form Layout -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        
        <!-- LEFT COLUMN: Movie, Cinemas, Dates -->
        <div class="space-y-4">
          <BaseSelect 
            v-model="form.movie_id"
            label="Chọn Phim Chiếu *"
            required
            @update:model-value="$emit('movie-select-change', $event)"
          >
            <option value="" disabled>-- Chọn bộ phim --</option>
            <option v-for="m in moviesList" :key="m.id" :value="m.id">
              {{ m.title }} ({{ m.duration || 120 }}p) - {{ formatStatus(m.status) }}
            </option>
          </BaseSelect>

          <!-- Single Mode vs Batch Mode Sub-components -->
          <ShowtimeSingleForm 
            v-if="creationMode === 'single'"
            :form="form"
            :selectedCinemaId="selectedCinemaId"
            :cinemasList="cinemasList"
            :availableRooms="availableRooms"
            @update:selectedCinemaId="$emit('cinema-change', $event)"
          />

          <ShowtimeBatchForm 
            v-else
            :batchForm="batchForm"
            :cinemasList="cinemasList"
            @toggle-select-all="$emit('toggle-select-all-cinemas')"
          />
        </div>

        <!-- RIGHT COLUMN: Timeslots, Format, Status, Pricing -->
        <div class="space-y-4">
          <!-- If Batch Mode: Multiple Time Slots -->
          <div v-if="creationMode === 'batch'" class="space-y-1.5">
            <label class="block text-xs font-semibold text-cinema-muted">Khung Giờ Chiếu Trong Ngày *</label>
            <div class="grid grid-cols-3 sm:grid-cols-4 gap-1.5">
              <label 
                v-for="slot in availableTimeSlots" 
                :key="slot"
                class="p-2 rounded-xl border flex items-center justify-between text-xs cursor-pointer transition-all font-mono font-bold"
                :class="batchForm.time_slots.includes(slot) ? 'bg-cinema-accent/20 border-cinema-accent text-white shadow-glow-accent' : 'bg-slate-900/90 border-cinema-border text-slate-400'"
              >
                <span>{{ slot }}</span>
                <input 
                  type="checkbox" 
                  :value="slot" 
                  v-model="batchForm.time_slots"
                  class="hidden"
                />
              </label>
            </div>
          </div>

          <!-- Format & Status (Shared) -->
          <div class="grid grid-cols-2 gap-3">
            <BaseSelect 
              v-model="form.format"
              label="Định Dạng Chiếu *"
              required
            >
              <option value="2D Standard">2D Standard</option>
              <option value="3D">3D RealD</option>
              <option value="IMAX Laser">IMAX Laser</option>
              <option value="4DX">4DX Motion</option>
            </BaseSelect>

            <BaseSelect 
              v-model="form.status"
              label="Loại Suất Chiếu *"
              required
            >
              <option value="scheduled">🟢 Suất Thường</option>
              <option value="early_premiere">✨ Suất Chiếu Sớm</option>
            </BaseSelect>
          </div>

          <!-- 💺 Tùy Chỉnh Giá Chi Tiết Từng Loại Ghế -->
          <div class="p-3.5 rounded-2xl bg-slate-900/90 border border-cinema-border space-y-3">
            <span class="text-xs font-bold text-white block">💺 Cấu Hình Bảng Giá Riêng Từng Loại Ghế (VNĐ):</span>
            
            <div class="grid grid-cols-3 gap-2.5">
              <BaseInput 
                v-model="form.base_price"
                type="number"
                label="Ghế Thường *"
                placeholder="95000"
                required
                @input="$emit('base-price-change')"
              />

              <BaseInput 
                v-model="form.price_vip"
                type="number"
                label="Ghế VIP *"
                placeholder="115000"
                required
              />

              <BaseInput 
                v-model="form.price_couple"
                type="number"
                label="Ghế Đôi *"
                placeholder="200000"
                required
              />
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- Sticky Bottom Footer with Action Buttons -->
    <template #footer>
      <div class="flex items-center justify-between w-full">
        <span class="text-xs text-cinema-muted">
          {{ creationMode === 'single' ? 'Tạo 1 suất chiếu nhanh' : `Tạo lịch đồng loạt cho ${batchForm.cinema_ids.length} rạp` }}
        </span>

        <div class="flex items-center gap-3">
          <BaseButton 
            type="button" 
            variant="secondary" 
            size="md"
            @click="$emit('update:modelValue', false)"
          >
            Hủy Bỏ
          </BaseButton>
          <BaseButton 
            type="button" 
            variant="primary" 
            size="md"
            :loading="isSubmitting"
            @click="creationMode === 'single' ? $emit('submit-single') : $emit('submit-batch')"
          >
            {{ creationMode === 'single' ? 'Tạo Suất Chiếu' : `Tạo Hàng Loạt (${batchForm.cinema_ids.length} Rạp)` }}
          </BaseButton>
        </div>
      </div>
    </template>
  </BaseModal>
</template>

<script setup lang="ts">
import BaseModal from '../../base/BaseModal.vue';
import BaseSelect from '../../base/BaseSelect.vue';
import BaseInput from '../../base/BaseInput.vue';
import BaseButton from '../../base/BaseButton.vue';
import ShowtimeSingleForm from './ShowtimeSingleForm.vue';
import ShowtimeBatchForm from './ShowtimeBatchForm.vue';
import { formatStatus } from '../../../utils/formatters';

defineProps<{
  modelValue: boolean;
  creationMode: 'single' | 'batch';
  form: any;
  batchForm: any;
  moviesList: any[];
  cinemasList: any[];
  availableRooms: any[];
  availableTimeSlots: string[];
  selectedCinemaId: string | number;
  isSubmitting: boolean;
}>();

defineEmits<{
  (e: 'update:modelValue', val: boolean): void;
  (e: 'update:creationMode', val: 'single' | 'batch'): void;
  (e: 'movie-select-change', movieId: string | number): void;
  (e: 'cinema-change', cinemaId: string | number): void;
  (e: 'toggle-select-all-cinemas'): void;
  (e: 'base-price-change'): void;
  (e: 'submit-single'): void;
  (e: 'submit-batch'): void;
}>();
</script>
