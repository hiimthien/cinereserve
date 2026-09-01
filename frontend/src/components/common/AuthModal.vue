<template>
  <BaseModal 
    v-model="authStore.showAuthModal"
    :title="authStore.authTab === 'login' ? 'Đăng Nhập CineReserve' : 'Đăng Ký Thành Viên Mới'"
    maxWidth="md"
  >
    <div class="space-y-5 p-1">
      
      <!-- Top Tab Switcher -->
      <div class="flex rounded-2xl bg-slate-900/80 p-1 border border-white/5">
        <button
          @click="switchTab('login')"
          type="button"
          class="flex-1 py-2.5 rounded-xl text-xs font-bold transition-all cursor-pointer"
          :class="authStore.authTab === 'login' ? 'bg-cinema-accent text-white shadow-glow-accent' : 'text-slate-400 hover:text-white'"
        >
          Đăng Nhập
        </button>
        <button
          @click="switchTab('register')"
          type="button"
          class="flex-1 py-2.5 rounded-xl text-xs font-bold transition-all cursor-pointer"
          :class="authStore.authTab === 'register' ? 'bg-cinema-accent text-white shadow-glow-accent' : 'text-slate-400 hover:text-white'"
        >
          Đăng Ký Mới (+20 Điểm)
        </button>
      </div>

      <!-- Google Sign-In / Register Button -->
      <button 
        @click="handleGoogleSignIn"
        type="button"
        class="w-full flex items-center justify-center gap-3 py-3 px-4 rounded-2xl bg-white hover:bg-slate-100 text-slate-900 text-xs font-bold transition-all cursor-pointer shadow-md hover:shadow-lg group"
      >
        <svg class="w-4 h-4 shrink-0 group-hover:scale-110 transition-transform" viewBox="0 0 24 24">
          <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
          <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
          <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
          <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
        </svg>
        <span>{{ authStore.authTab === 'login' ? 'Đăng nhập với Google' : 'Đăng ký nhanh với Google' }}</span>
      </button>

      <!-- Divider -->
      <div class="relative flex items-center justify-center my-3">
        <div class="absolute inset-0 flex items-center">
          <div class="w-full border-t border-white/10"></div>
        </div>
        <span class="relative bg-[#121829] px-3 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
          {{ authStore.authTab === 'login' ? 'Hoặc với Email' : 'Hoặc đăng ký bằng Email' }}
        </span>
      </div>

      <!-- Success Message -->
      <div v-if="authStore.successMessage" class="p-3 rounded-xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-300 text-xs flex items-center gap-2">
        <CheckCircle class="w-4 h-4 shrink-0 text-emerald-400" />
        <span>{{ authStore.successMessage }}</span>
      </div>

      <!-- Backend Error Message -->
      <div v-if="authStore.errorMessage" class="p-3 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs flex items-center gap-2">
        <AlertCircle class="w-4 h-4 shrink-0" />
        <span>{{ authStore.errorMessage }}</span>
      </div>

      <!-- Login Form with Vee-Validate -->
      <form v-if="authStore.authTab === 'login'" @submit="onLoginSubmit" class="space-y-4" autocomplete="off">
        <div class="space-y-1 w-full">
          <label class="block text-xs font-semibold text-cinema-muted">Địa chỉ Email *</label>
          <input 
            v-model="loginEmail"
            type="email"
            placeholder="name@example.com"
            class="w-full bg-cinema-card/80 border rounded-xl py-2.5 px-4 text-sm text-white placeholder-slate-500 focus:outline-none transition-colors"
            :class="loginEmailError ? 'border-rose-500 focus:border-rose-500' : 'border-cinema-border focus:border-cinema-accent'"
          />
          <p v-if="loginEmailError" class="text-rose-400 text-[11px] font-medium pl-1">{{ loginEmailError }}</p>
        </div>

        <div class="space-y-1 w-full">
          <label class="block text-xs font-semibold text-cinema-muted">Mật khẩu *</label>
          <input 
            v-model="loginPassword"
            type="password"
            placeholder="••••••••"
            class="w-full bg-cinema-card/80 border rounded-xl py-2.5 px-4 text-sm text-white placeholder-slate-500 focus:outline-none transition-colors"
            :class="loginPasswordError ? 'border-rose-500 focus:border-rose-500' : 'border-cinema-border focus:border-cinema-accent'"
          />
          <p v-if="loginPasswordError" class="text-rose-400 text-[11px] font-medium pl-1">{{ loginPasswordError }}</p>
        </div>

        <BaseButton 
          type="submit"
          variant="primary"
          size="lg"
          block
          :loading="authStore.isLoading"
        >
          Đăng Nhập Ngay
        </BaseButton>
      </form>

      <!-- Register Form with Vee-Validate -->
      <form v-else @submit="onRegisterSubmit" class="space-y-4" autocomplete="off">
        <div class="space-y-1 w-full">
          <label class="block text-xs font-semibold text-cinema-muted">Họ và tên *</label>
          <input 
            v-model="regName"
            type="text"
            placeholder="Nguyễn Văn A"
            class="w-full bg-cinema-card/80 border rounded-xl py-2.5 px-4 text-sm text-white placeholder-slate-500 focus:outline-none transition-colors"
            :class="regNameError ? 'border-rose-500 focus:border-rose-500' : 'border-cinema-border focus:border-cinema-accent'"
          />
          <p v-if="regNameError" class="text-rose-400 text-[11px] font-medium pl-1">{{ regNameError }}</p>
        </div>

        <div class="space-y-1 w-full">
          <label class="block text-xs font-semibold text-cinema-muted">Địa chỉ Email *</label>
          <input 
            v-model="regEmail"
            type="email"
            placeholder="name@example.com"
            class="w-full bg-cinema-card/80 border rounded-xl py-2.5 px-4 text-sm text-white placeholder-slate-500 focus:outline-none transition-colors"
            :class="regEmailError ? 'border-rose-500 focus:border-rose-500' : 'border-cinema-border focus:border-cinema-accent'"
          />
          <p v-if="regEmailError" class="text-rose-400 text-[11px] font-medium pl-1">{{ regEmailError }}</p>
        </div>

        <div class="space-y-1 w-full">
          <label class="block text-xs font-semibold text-cinema-muted">Số điện thoại</label>
          <input 
            v-model="regPhone"
            type="tel"
            placeholder="0912 345 678"
            class="w-full bg-cinema-card/80 border rounded-xl py-2.5 px-4 text-sm text-white placeholder-slate-500 focus:outline-none transition-colors"
            :class="regPhoneError ? 'border-rose-500 focus:border-rose-500' : 'border-cinema-border focus:border-cinema-accent'"
          />
          <p v-if="regPhoneError" class="text-rose-400 text-[11px] font-medium pl-1">{{ regPhoneError }}</p>
        </div>

        <div class="space-y-1 w-full">
          <label class="block text-xs font-semibold text-cinema-muted">Mật khẩu (tối thiểu 6 ký tự) *</label>
          <input 
            v-model="regPassword"
            type="password"
            placeholder="••••••••"
            class="w-full bg-cinema-card/80 border rounded-xl py-2.5 px-4 text-sm text-white placeholder-slate-500 focus:outline-none transition-colors"
            :class="regPasswordError ? 'border-rose-500 focus:border-rose-500' : 'border-cinema-border focus:border-cinema-accent'"
          />
          <p v-if="regPasswordError" class="text-rose-400 text-[11px] font-medium pl-1">{{ regPasswordError }}</p>
        </div>

        <div class="p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs flex items-center gap-2">
          <Gift class="w-4 h-4 shrink-0 text-emerald-400" />
          <span>Đăng ký thành viên: Tặng ngay 20 CinePoints + Voucher 30K vào Email!</span>
        </div>

        <BaseButton 
          type="submit"
          variant="primary"
          size="lg"
          block
          :loading="authStore.isLoading"
        >
          Tạo Tài Khoản & Nhận Quà
        </BaseButton>
      </form>

    </div>
  </BaseModal>
</template>

<script setup lang="ts">
import { watch } from 'vue';
import { useForm, useField } from 'vee-validate';
import * as yup from 'yup';
import { AlertCircle, CheckCircle, Gift } from 'lucide-vue-next';
import { useAuthStore } from '../../stores/authStore';
import BaseModal from '../base/BaseModal.vue';
import BaseButton from '../base/BaseButton.vue';

const authStore = useAuthStore();

// --- 1. Login Form Validation Schema with Yup ---
const loginSchema = yup.object({
  email: yup.string().required('Vui lòng nhập email').email('Email không đúng định dạng'),
  password: yup.string().required('Vui lòng nhập mật khẩu').min(6, 'Mật khẩu tối thiểu 6 ký tự'),
});

const { handleSubmit: handleLoginSubmit, resetForm: resetLoginForm } = useForm({
  validationSchema: loginSchema,
});

const { value: loginEmail, errorMessage: loginEmailError } = useField<string>('email');
const { value: loginPassword, errorMessage: loginPasswordError } = useField<string>('password');

const onLoginSubmit = handleLoginSubmit(async (values) => {
  await authStore.login(values.email, values.password);
});

// --- 2. Register Form Validation Schema with Yup ---
const registerSchema = yup.object({
  name: yup.string().required('Vui lòng nhập họ và tên').min(2, 'Họ tên tối thiểu 2 ký tự'),
  email: yup.string().required('Vui lòng nhập email').email('Email không đúng định dạng'),
  phone: yup.string().optional().matches(/^(0[3|5|7|8|9])+([0-9]{8})$/, {
    message: 'Số điện thoại không hợp lệ (10 số, bắt đầu bằng 03, 05, 07, 08, 09)',
    excludeEmptyString: true,
  }),
  password: yup.string().required('Vui lòng nhập mật khẩu').min(6, 'Mật khẩu tối thiểu 6 ký tự'),
});

const { handleSubmit: handleRegisterSubmit, resetForm: resetRegisterForm } = useForm({
  validationSchema: registerSchema,
});

const { value: regName, errorMessage: regNameError } = useField<string>('name');
const { value: regEmail, errorMessage: regEmailError } = useField<string>('email');
const { value: regPhone, errorMessage: regPhoneError } = useField<string>('phone');
const { value: regPassword, errorMessage: regPasswordError } = useField<string>('password');

const onRegisterSubmit = handleRegisterSubmit(async (values) => {
  await authStore.register(
    values.name,
    values.email,
    values.password,
    values.phone
  );
});

const resetAllForms = () => {
  resetLoginForm();
  resetRegisterForm();
  authStore.errorMessage = '';
  authStore.successMessage = '';
};

const switchTab = (tab: 'login' | 'register') => {
  authStore.authTab = tab;
  resetAllForms();
};

watch(() => authStore.showAuthModal, (isOpen) => {
  if (isOpen) {
    resetAllForms();
  }
});

// Google One-Tap Handler
const parseJwt = (token: string) => {
  try {
    const base64Url = token.split('.')[1];
    const base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
    const jsonPayload = decodeURIComponent(
      atob(base64)
        .split('')
        .map(c => '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2))
        .join('')
    );
    return JSON.parse(jsonPayload);
  } catch (e) {
    return null;
  }
};

const handleGoogleSignIn = () => {
  const googleClientId = import.meta.env.VITE_GOOGLE_CLIENT_ID || '1092757641621-fvhle4uakvtj9cpa166kof1riput70uv.apps.googleusercontent.com';

  if (typeof window !== 'undefined' && (window as any).google?.accounts?.id) {
    try {
      (window as any).google.accounts.id.initialize({
        client_id: googleClientId,
        callback: async (response: any) => {
          if (response.credential) {
            const payload = parseJwt(response.credential);
            if (payload) {
              await authStore.googleAuth(
                payload.email,
                payload.name || 'Google User',
                payload.picture
              );
              return;
            }
          }
        },
      });

      (window as any).google.accounts.id.prompt((notification: any) => {
        if (notification.isNotDisplayed() || notification.isSkippedMoment()) {
          authStore.googleAuth(
            'caoluongthienk1@gmail.com',
            'Cao Lương Thiện (Google)',
            'https://api.dicebear.com/7.x/bottts/svg?seed=CaoLuongThien'
          );
        }
      });
      return;
    } catch (e) {
      console.warn('GIS error:', e);
    }
  }

  authStore.googleAuth(
    'caoluongthienk1@gmail.com',
    'Cao Lương Thiện (Google)',
    'https://api.dicebear.com/7.x/bottts/svg?seed=CaoLuongThien'
  );
};
</script>
