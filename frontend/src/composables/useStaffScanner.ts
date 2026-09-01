import { ref } from 'vue';
import api from '../services/api';
import { useAudioFeedback } from './useAudioFeedback';
import { useToast } from './useToast';

export interface ScanResultData {
  status: 'VALID' | 'ALREADY_USED' | 'INVALID' | 'UNPAID';
  message: string;
  ticket?: any;
}

export function useStaffScanner() {
  const { playBeep } = useAudioFeedback();
  const toast = useToast();

  const staffName = ref('Cao Lương Thiện');
  const soundEnabled = ref(true);
  const manualCode = ref('');
  const isChecking = ref(false);
  const isCameraActive = ref(false);

  const scanResult = ref<ScanResultData | null>(null);
  const recentHistory = ref<Array<{ code: string; name: string; seats: string; time: string; status: string }>>([]);

  const executeCheckIn = async (codeStr: string) => {
    if (!codeStr || isChecking.value) return;

    isChecking.value = true;
    scanResult.value = null;

    try {
      const res = await api.post('/tickets/check-in', {
        qr_code: codeStr.trim(),
        staff_name: staffName.value,
      });

      if (res.data?.success) {
        scanResult.value = {
          status: 'VALID',
          message: res.data.message || 'Soát vé thành công!',
          ticket: res.data.data,
        };

        if (soundEnabled.value) playBeep(true);
        toast.success(res.data.message, 'Vé Hợp Lệ');

        // Add to recent history log
        const t = res.data.data;
        recentHistory.value.unshift({
          code: t.booking_code,
          name: t.user_name || 'Khách',
          seats: Array.isArray(t.seats) ? t.seats.join(', ') : 'Ghế',
          time: new Date().toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit', second: '2-digit' }),
          status: 'VALID',
        });
      }
    } catch (err: any) {
      const errStatus = err.response?.data?.status || 'INVALID';
      const errMsg = err.response?.data?.message || 'Lỗi soát vé. Mã không hợp lệ.';
      
      scanResult.value = {
        status: errStatus,
        message: errMsg,
        ticket: err.response?.data?.data,
      };

      if (soundEnabled.value) playBeep(false);
      toast.error(errMsg, 'Cảnh Báo Soát Vé');
    } finally {
      isChecking.value = false;
      manualCode.value = '';
    }
  };

  const handleManualSubmit = () => {
    if (manualCode.value.trim()) {
      executeCheckIn(manualCode.value);
    }
  };

  return {
    staffName,
    soundEnabled,
    manualCode,
    isChecking,
    isCameraActive,
    scanResult,
    recentHistory,
    executeCheckIn,
    handleManualSubmit,
  };
}
