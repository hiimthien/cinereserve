<template>
  <div class="min-h-screen bg-cinema-bg text-cinema-text">
    <router-view />
    <BaseToast />
  </div>
</template>

<script setup lang="ts">
import BaseToast from './components/base/BaseToast.vue';
</script>
