<template>
  <BaseModal 
    v-model="authStore.showChangePasswordModal"
    title="Đổi Mật Khẩu Tài Khoản"
    maxWidth="md"
  >
    <div class="space-y-4 p-1">
      <div class="p-3 rounded-2xl bg-slate-900/80 border border-white/5 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-500/30 flex items-center justify-center shrink-0">
          <KeyRound class="w-5 h-5 text-amber-400" />
        </div>
        <div>
          <h4 class="text-xs font-bold text-white">{{ authStore.user?.name || 'Tài khoản' }}</h4>
          <p class="text-[11px] text-cinema-muted">{{ authStore.user?.email }}</p>
        </div>
      </div>

      <!-- Success Notification -->
      <div v-if="authStore.successMessage" class="p-3 rounded-xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-300 text-xs flex items-center gap-2 animate-in fade-in">
        <CheckCircle class="w-4 h-4 shrink-0 text-emerald-400" />
        <span>{{ authStore.successMessage }}</span>
      </div>

      <!-- Error Notification -->
      <div v-if="authStore.errorMessage" class="p-3 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs flex items-center gap-2 animate-in fade-in">
        <AlertCircle class="w-4 h-4 shrink-0" />
        <span>{{ authStore.errorMessage }}</span>
      </div>

      <form @submit.prevent="handleSubmit" class="space-y-3.5">
        <!-- Current Password -->
        <div class="space-y-1">
          <label class="block text-xs font-semibold text-slate-300">Mật khẩu hiện tại *</label>
          <input 
            v-model="currentPassword"
            type="password"
            placeholder="••••••••"
            required
            class="w-full bg-cinema-card/80 border border-cinema-border focus:border-cinema-accent rounded-xl py-2.5 px-4 text-sm text-white placeholder-slate-500 focus:outline-none transition-colors"
          />
        </div>

        <!-- New Password -->
        <div class="space-y-1">
          <label class="block text-xs font-semibold text-slate-300">Mật khẩu mới (tối thiểu 6 ký tự) *</label>
          <input 
            v-model="newPassword"
            type="password"
            placeholder="••••••••"
            required
            minlength="6"
            class="w-full bg-cinema-card/80 border border-cinema-border focus:border-cinema-accent rounded-xl py-2.5 px-4 text-sm text-white placeholder-slate-500 focus:outline-none transition-colors"
          />
        </div>

        <!-- Confirm New Password -->
        <div class="space-y-1">
          <label class="block text-xs font-semibold text-slate-300">Xác nhận mật khẩu mới *</label>
          <input 
            v-model="confirmNewPassword"
            type="password"
            placeholder="••••••••"
            required
            minlength="6"
            class="w-full bg-cinema-card/80 border border-cinema-border focus:border-cinema-accent rounded-xl py-2.5 px-4 text-sm text-white placeholder-slate-500 focus:outline-none transition-colors"
          />
          <p v-if="passwordMismatch" class="text-[11px] text-rose-400 font-medium pl-1">Mật khẩu xác nhận không khớp.</p>
        </div>

        <div class="pt-2 flex gap-3">
          <button 
            type="button"
            @click="authStore.showChangePasswordModal = false"
            class="flex-1 py-2.5 rounded-xl border border-white/10 hover:bg-white/5 text-xs font-bold text-slate-300 transition-colors cursor-pointer"
          >
            Hủy Bỏ
          </button>

          <BaseButton 
            type="submit"
            variant="primary"
            class="flex-1"
            :loading="authStore.isLoading"
            :disabled="passwordMismatch || !currentPassword || !newPassword || !confirmNewPassword"
          >
            Lưu Mật Khẩu
          </BaseButton>
        </div>
      </form>
    </div>
  </BaseModal>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { KeyRound, CheckCircle, AlertCircle } from 'lucide-vue-next';
import { useAuthStore } from '../../stores/authStore';
import BaseModal from '../base/BaseModal.vue';
import BaseButton from '../base/BaseButton.vue';

const authStore = useAuthStore();

const currentPassword = ref('');
const newPassword = ref('');
const confirmNewPassword = ref('');

const passwordMismatch = computed(() => {
  if (!newPassword.value || !confirmNewPassword.value) return false;
  return newPassword.value !== confirmNewPassword.value;
});

const handleSubmit = async () => {
  if (passwordMismatch.value) return;
  const success = await authStore.changePassword(
    currentPassword.value,
    newPassword.value,
    confirmNewPassword.value
  );
  if (success) {
    currentPassword.value = '';
    newPassword.value = '';
    confirmNewPassword.value = '';
  }
};
</script>
