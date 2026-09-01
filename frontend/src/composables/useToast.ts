import { ref } from 'vue';

export type ToastType = 'success' | 'error' | 'warning' | 'info';

export interface ToastItem {
  id: string;
  type: ToastType;
  title?: string;
  message: string;
  duration?: number;
}

const toasts = ref<ToastItem[]>([]);

export const useToast = () => {
  const addToast = (
    message: string, 
    type: ToastType = 'info', 
    title?: string, 
    duration: number = 4000
  ) => {
    const id = Math.random().toString(36).substring(2, 9);
    const item: ToastItem = { id, type, title, message, duration };
    toasts.value.push(item);

    if (duration > 0) {
      setTimeout(() => {
        removeToast(id);
      }, duration);
    }

    return id;
  };

  const removeToast = (id: string) => {
    toasts.value = toasts.value.filter(t => t.id !== id);
  };

  const success = (message: string, title: string = 'Thành công!') => {
    return addToast(message, 'success', title);
  };

  const error = (message: string, title: string = 'Đã có lỗi xảy ra') => {
    return addToast(message, 'error', title, 5000);
  };

  const warning = (message: string, title: string = 'Cảnh báo') => {
    return addToast(message, 'warning', title);
  };

  const info = (message: string, title: string = 'Thông báo') => {
    return addToast(message, 'info', title);
  };

  const clear = () => {
    toasts.value = [];
  };

  return {
    toasts,
    addToast,
    removeToast,
    success,
    error,
    warning,
    info,
    clear,
  };
};
