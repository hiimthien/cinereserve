<template>
  <BaseModal 
    :model-value="isOpen"
    :title="title ? `${title} - Official Trailer` : 'Official Trailer'"
    max-width="4xl"
    @update:model-value="$emit('close')"
    @close="$emit('close')"
  >
    <!-- Video Player in 16:9 Aspect Ratio -->
    <div class="relative w-full aspect-video bg-black rounded-2xl overflow-hidden shadow-inner">
      <iframe 
        v-if="isOpen && videoUrl"
        :src="embedUrl" 
        class="w-full h-full border-0"
        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
        allowfullscreen
      ></iframe>
      <div v-else class="w-full h-full flex items-center justify-center text-cinema-muted text-xs">
        Đang tải trailer...
      </div>
    </div>
  </BaseModal>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import BaseModal from '../base/BaseModal.vue';

const props = defineProps<{
  isOpen: boolean;
  title: string;
  videoUrl: string;
}>();

defineEmits<{
  (e: 'close'): void;
}>();

const embedUrl = computed(() => {
  if (!props.videoUrl) return '';
  return props.videoUrl.includes('?') 
    ? `${props.videoUrl}&autoplay=1` 
    : `${props.videoUrl}?autoplay=1`;
});
</script>
