<template>
  <div class="min-h-screen bg-cinema-bg text-cinema-text">
    <router-view />
    <BaseToast />
    <AuthModal />
    <LoyaltyRewardModal />
    <ChangePasswordModal />
  </div>
</template>

<script setup lang="ts">
import { defineAsyncComponent } from 'vue';
import BaseToast from './components/base/BaseToast.vue';

const AuthModal = defineAsyncComponent(() => import('./components/common/AuthModal.vue'));
const LoyaltyRewardModal = defineAsyncComponent(() => import('./components/common/LoyaltyRewardModal.vue'));
const ChangePasswordModal = defineAsyncComponent(() => import('./components/common/ChangePasswordModal.vue'));
</script>
