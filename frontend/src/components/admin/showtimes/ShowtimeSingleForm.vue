<template>
  <div class="space-y-3.5">
    <div class="grid grid-cols-2 gap-3">
      <BaseSelect 
        :model-value="selectedCinemaId"
        @update:model-value="$emit('update:selectedCinemaId', $event)"
        label="Cụm Rạp Chiếu *"
        required
      >
        <option value="" disabled>-- Chọn cụm rạp --</option>
        <option v-for="c in cinemasList" :key="c.id" :value="c.id">
          {{ c.name }}
        </option>
      </BaseSelect>

      <BaseSelect 
        v-model="form.room_id"
        label="Phòng Chiếu *"
        required
      >
        <option value="" disabled>-- Chọn phòng chiếu --</option>
        <option v-for="r in availableRooms" :key="r.id" :value="r.id">
          {{ r.name }} ({{ r.room_type || '2D' }})
        </option>
      </BaseSelect>
    </div>

    <div class="grid grid-cols-2 gap-3">
      <BaseInput 
        v-model="form.show_date"
        type="date"
        label="Ngày Chiếu *"
        required
      />
      <BaseInput 
        v-model="form.start_time"
        type="time"
        label="Giờ Bắt Đầu *"
        required
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import BaseSelect from '../../base/BaseSelect.vue';
import BaseInput from '../../base/BaseInput.vue';

defineProps<{
  form: any;
  selectedCinemaId: string | number;
  cinemasList: any[];
  availableRooms: any[];
}>();

defineEmits<{
  (e: 'update:selectedCinemaId', val: string | number): void;
}>();
</script>
