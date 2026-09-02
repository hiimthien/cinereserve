import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import api from '../services/api';

export interface UserProfile {
  id: number;
  name: string;
  email: string;
  role: 'user' | 'staff' | 'admin';
  phone?: string;
  avatar?: string;
  points: number;
  membership_tier: 'member' | 'vip' | 'diamond';
  total_spent: number;
  total_tickets_bought: number;
}

export const useAuthStore = defineStore('auth', () => {
  const token = ref<string | null>(localStorage.getItem('cinereserve_token'));
  
  // Read persisted user if token exists, otherwise Guest (null)
  const user = ref<UserProfile | null>(
    localStorage.getItem('cinereserve_token') && localStorage.getItem('cinereserve_user')
      ? JSON.parse(localStorage.getItem('cinereserve_user')!)
      : null
  );

  const showAuthModal = ref(false);
  const showRewardModal = ref(false);
  const showChangePasswordModal = ref(false);
  const authTab = ref<'login' | 'register' | 'forgot'>('login');
  const isLoading = ref(false);
  const errorMessage = ref('');
  const successMessage = ref('');

  const isAuthenticated = computed(() => !!user.value);
  const isAdmin = computed(() => user.value?.role === 'admin');
  const isStaff = computed(() => user.value?.role === 'admin' || user.value?.role === 'staff');

  const tierBadgeInfo = computed(() => {
    switch (user.value?.membership_tier) {
      case 'diamond':
        return { label: 'CineDiamond', color: 'from-cyan-400 to-blue-600', icon: '💎', multiplier: '15%' };
      case 'vip':
        return { label: 'CineVIP', color: 'from-amber-400 to-amber-600', icon: '👑', multiplier: '10%' };
      default:
        return { label: 'CineMember', color: 'from-slate-400 to-slate-600', icon: '🥈', multiplier: '5%' };
    }
  });

  const nextTierProgress = computed(() => {
    if (!user.value) return { text: '0/5 vé', percent: 0, nextTier: 'CineVIP' };
    const tickets = user.value.total_tickets_bought || 0;
    if (user.value.membership_tier === 'diamond') {
      return { text: 'Đạt cấp tối đa', percent: 100, nextTier: 'Cực Đại' };
    }
    if (user.value.membership_tier === 'vip') {
      const remaining = Math.max(0, 20 - tickets);
      const pct = Math.min(100, Math.round((tickets / 20) * 100));
      return { text: `${tickets}/20 vé (còn ${remaining} vé để lên Diamond)`, percent: pct, nextTier: 'CineDiamond' };
    }
    const remaining = Math.max(0, 5 - tickets);
    const pct = Math.min(100, Math.round((tickets / 5) * 100));
    return { text: `${tickets}/5 vé (còn ${remaining} vé để lên VIP)`, percent: pct, nextTier: 'CineVIP' };
  });

  const setAuthSession = (authToken: string, authUser: UserProfile) => {
    token.value = authToken;
    user.value = authUser;
    localStorage.setItem('cinereserve_token', authToken);
    localStorage.setItem('cinereserve_user', JSON.stringify(authUser));
  };

  const login = async (email: string, pass: string) => {
    isLoading.value = true;
    errorMessage.value = '';
    successMessage.value = '';
    try {
      const res = await api.post('/auth/login', { email, password: pass });
      if (res.data?.success && res.data?.data) {
        setAuthSession(res.data.data.token, res.data.data.user);
        successMessage.value = 'Đăng nhập thành công!';
        setTimeout(() => {
          showAuthModal.value = false;
        }, 500);
        return true;
      }
    } catch (err: any) {
      errorMessage.value = err.response?.data?.message || 'Email hoặc mật khẩu không chính xác.';
      return false;
    } finally {
      isLoading.value = false;
    }
  };

  const register = async (name: string, email: string, pass: string, phone?: string) => {
    isLoading.value = true;
    errorMessage.value = '';
    successMessage.value = '';
    try {
      const res = await api.post('/auth/register', { name, email, password: pass, phone });
      if (res.data?.success && res.data?.data) {
        setAuthSession(res.data.data.token, res.data.data.user);
        successMessage.value = 'Đăng ký thành công! Đã gửi voucher 30K vào email.';
        setTimeout(() => {
          showAuthModal.value = false;
        }, 800);
        return true;
      }
    } catch (err: any) {
      errorMessage.value = err.response?.data?.message || 'Đăng ký không thành công. Email có thể đã tồn tại.';
      return false;
    } finally {
      isLoading.value = false;
    }
  };

  const forgotPassword = async (email: string) => {
    isLoading.value = true;
    errorMessage.value = '';
    successMessage.value = '';
    try {
      const res = await api.post('/auth/forgot-password', { email });
      if (res.data?.success) {
        successMessage.value = res.data.message || 'Mã OTP đã được gửi về email của bạn.';
        return { success: true, debugOtp: res.data?.data?.debug_otp };
      }
      return { success: false };
    } catch (err: any) {
      errorMessage.value = err.response?.data?.message || 'Không tìm thấy tài khoản với email này.';
      return { success: false, error: errorMessage.value };
    } finally {
      isLoading.value = false;
    }
  };

  const resetPassword = async (email: string, otp: string, pass: string, passConfirm: string) => {
    isLoading.value = true;
    errorMessage.value = '';
    successMessage.value = '';
    try {
      const res = await api.post('/auth/reset-password', {
        email,
        otp,
        password: pass,
        password_confirmation: passConfirm,
      });
      if (res.data?.success && res.data?.data) {
        setAuthSession(res.data.data.token, res.data.data.user);
        successMessage.value = 'Đặt lại mật khẩu thành công! Đã tự động đăng nhập.';
        setTimeout(() => {
          showAuthModal.value = false;
        }, 800);
        return true;
      }
      return false;
    } catch (err: any) {
      errorMessage.value = err.response?.data?.message || 'Mã OTP không hợp lệ hoặc đã hết hạn.';
      return false;
    } finally {
      isLoading.value = false;
    }
  };

  const changePassword = async (currentPass: string, newPass: string, newPassConfirm: string) => {
    isLoading.value = true;
    errorMessage.value = '';
    successMessage.value = '';
    try {
      const res = await api.post('/auth/change-password', {
        current_password: currentPass,
        new_password: newPass,
        new_password_confirmation: newPassConfirm,
        user_id: user.value?.id,
        email: user.value?.email,
      });
      if (res.data?.success) {
        successMessage.value = 'Đổi mật khẩu thành công!';
        setTimeout(() => {
          showChangePasswordModal.value = false;
        }, 800);
        return true;
      }
      return false;
    } catch (err: any) {
      errorMessage.value = err.response?.data?.message || 'Mật khẩu hiện tại không chính xác.';
      return false;
    } finally {
      isLoading.value = false;
    }
  };

  const googleAuth = async (email: string, name: string, avatar?: string) => {
    isLoading.value = true;
    errorMessage.value = '';
    successMessage.value = '';
    try {
      const res = await api.post('/auth/google', { email, name, avatar });
      if (res.data?.success && res.data?.data) {
        setAuthSession(res.data.data.token, res.data.data.user);
        successMessage.value = 'Đăng nhập Google thành công!';
        setTimeout(() => {
          showAuthModal.value = false;
        }, 500);
        return true;
      }
    } catch (err: any) {
      errorMessage.value = err.response?.data?.message || 'Đăng nhập Google thất bại.';
      return false;
    } finally {
      isLoading.value = false;
    }
  };

  const logout = async () => {
    try {
      await api.post('/auth/logout');
    } catch (e) {
      // Ignore
    } finally {
      token.value = null;
      user.value = null;
      localStorage.removeItem('cinereserve_token');
      localStorage.removeItem('cinereserve_user');
    }
  };

  const openAuth = (tab: 'login' | 'register' | 'forgot' = 'login') => {
    authTab.value = tab;
    errorMessage.value = '';
    successMessage.value = '';
    showAuthModal.value = true;
  };

  const openChangePasswordModal = () => {
    errorMessage.value = '';
    successMessage.value = '';
    showChangePasswordModal.value = true;
  };

  const fetchUser = async () => {
    if (!token.value) return;
    try {
      const res = await api.get('/auth/me');
      if (res.data?.data) {
        user.value = res.data.data;
        localStorage.setItem('cinereserve_user', JSON.stringify(res.data.data));
      }
    } catch (e) {
      console.warn('Sync user profile error', e);
    }
  };

  const openRewardModal = () => {
    showRewardModal.value = true;
  };

  return {
    token,
    user,
    isAuthenticated,
    isAdmin,
    isStaff,
    showAuthModal,
    showRewardModal,
    showChangePasswordModal,
    authTab,
    isLoading,
    errorMessage,
    successMessage,
    tierBadgeInfo,
    nextTierProgress,
    login,
    register,
    forgotPassword,
    resetPassword,
    changePassword,
    googleAuth,
    logout,
    openAuth,
    openChangePasswordModal,
    openRewardModal,
    fetchUser,
  };
});

