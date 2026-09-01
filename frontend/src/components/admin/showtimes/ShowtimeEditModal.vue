<template>
  <BaseModal 
    :model-value="modelValue" 
    @update:model-value="$emit('update:modelValue', $event)"
    title="Chỉnh Sửa Suất Chiếu & Bảng Giá Ghế"
    maxWidth="2xl"
    :zIndex="60"
  >
    <form @submit.prevent="$emit('submit')" class="space-y-4 p-1">
      <div class="grid grid-cols-2 gap-3">
        <BaseInput 
          v-model="editForm.show_date"
          type="date"
          label="Ngày Chiếu *"
          required
        />
        <BaseInput 
          v-model="editForm.start_time"
          type="time"
          label="Giờ Bắt Đầu *"
          required
        />
      </div>

      <div class="grid grid-cols-2 gap-3">
        <BaseSelect 
          v-model="editForm.format"
          label="Định Dạng *"
          required
        >
          <option value="2D Standard">2D Standard</option>
          <option value="3D">3D RealD</option>
          <option value="IMAX Laser">IMAX Laser</option>
          <option value="4DX">4DX Motion</option>
        </BaseSelect>

        <BaseSelect 
          v-model="editForm.status"
          label="Loại Suất Chiếu *"
          required
        >
          <option value="scheduled">🟢 Suất Chiếu Thường</option>
          <option value="early_premiere">✨ Suất Chiếu Sớm (Sneak Show)</option>
          <option value="cancelled">❌ Đã Hủy</option>
        </BaseSelect>
      </div>

      <!-- Custom Price Inputs for Edit -->
      <div class="p-3.5 rounded-2xl bg-slate-900/90 border border-cinema-border space-y-3">
        <span class="text-xs font-bold text-white block">💺 Cập Nhật Giá Từng Loại Ghế:</span>
        
        <div class="grid grid-cols-3 gap-2">
          <BaseInput 
            v-model="editForm.base_price"
            type="number"
            label="Ghế Thường *"
            required
            @input="$emit('base-price-change')"
          />
          <BaseInput 
            v-model="editForm.price_vip"
            type="number"
            label="Ghế VIP *"
            required
          />
          <BaseInput 
            v-model="editForm.price_couple"
            type="number"
            label="Ghế Đôi *"
            required
          />
        </div>
      </div>
    </form>

    <template #footer>
      <div class="flex items-center justify-end gap-3 w-full">
        <BaseButton 
          type="button" 
          variant="secondary" 
          @click="$emit('update:modelValue', false)"
        >
          Hủy Bỏ
        </BaseButton>
        <BaseButton 
          type="button" 
          variant="primary" 
          :loading="isSubmitting"
          @click="$emit('submit')"
        >
          Lưu Thay Đổi
        </BaseButton>
      </div>
    </template>
  </BaseModal>
</template>

<script setup lang="ts">
import BaseModal from '../../base/BaseModal.vue';
import BaseInput from '../../base/BaseInput.vue';
import BaseSelect from '../../base/BaseSelect.vue';
import BaseButton from '../../base/BaseButton.vue';

defineProps<{
  modelValue: boolean;
  editForm: any;
  isSubmitting: boolean;
}>();

defineEmits<{
  (e: 'update:modelValue', val: boolean): void;
  (e: 'base-price-change'): void;
  (e: 'submit'): void;
}>();
</script>
