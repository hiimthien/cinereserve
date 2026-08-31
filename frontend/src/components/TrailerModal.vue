<template>
  <div 
    v-if="isOpen" 
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-md transition-opacity"
    @click.self="close"
  >
    <div class="relative w-full max-w-4xl bg-cinema-surface rounded-3xl overflow-hidden border border-cinema-border shadow-2xl">
      <!-- Header -->
      <div class="flex items-center justify-between p-4 border-b border-white/10">
        <h3 class="font-bold text-white text-base">{{ title }} - Official Trailer</h3>
        <button 
          @click="close"
          class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition-colors"
        >
          ✕
        </button>
      </div>

      <!-- Video Player -->
      <div class="relative w-full aspect-video bg-black">
        <iframe 
          v-if="videoUrl"
          :src="videoUrl + '?autoplay=1'" 
          class="w-full h-full border-0"
          allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
          allowfullscreen
        ></iframe>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  isOpen: boolean;
  title: string;
  videoUrl: string;
}>();

const emit = defineEmits(['close']);

const close = () => {
  emit('close');
};
</script>
