import { ref } from 'vue';
import api from '../services/api';
import { useAuthStore } from '../stores/authStore';
import { useToast } from './useToast';

export function useMovieReviews(movieId: () => number | undefined) {
  const authStore = useAuthStore();
  const toast = useToast();

  const reviews = ref<any[]>([]);
  const isLoading = ref(false);
  const isSubmitting = ref(false);

  const newRating = ref(10);
  const newComment = ref('');
  const customName = ref('');

  const fetchReviews = async () => {
    const id = movieId();
    if (!id) return;

    isLoading.value = true;
    try {
      const res = await api.get(`/movies/${id}/reviews`);
      if (res.data?.data) {
        reviews.value = res.data.data;
      }
    } catch (e) {
      console.warn('Error fetching movie reviews:', e);
    } finally {
      isLoading.value = false;
    }
  };

  const submitReview = async (onSuccess?: (newReview: any) => void) => {
    const id = movieId();
    if (!id) return;

    if (!newComment.value.trim() || newComment.value.trim().length < 5) {
      toast.warning('Vui lòng viết nhận xét từ 5 ký tự trở lên để chia sẻ cảm nhận của bạn!', 'Nhận Xét Chưa Đủ Dài');
      return;
    }

    isSubmitting.value = true;
    try {
      const payload: any = {
        rating: Number(newRating.value),
        comment: newComment.value.trim(),
      };

      if (authStore.isAuthenticated && authStore.user) {
        payload.user_name = authStore.user.name;
      } else if (customName.value.trim()) {
        payload.user_name = customName.value.trim();
      }

      const res = await api.post(`/movies/${id}/reviews`, payload);
      if (res.data?.data) {
        reviews.value.unshift(res.data.data);
        newComment.value = '';
        newRating.value = 10;
        toast.success(res.data?.message || 'Cảm ơn bạn đã gửi đánh giá bộ phim!', 'Đánh Giá Thành Công');
        if (onSuccess) {
          onSuccess(res.data.data);
        }
      }
    } catch (e: any) {
      toast.error(e.response?.data?.message || 'Có lỗi xảy ra khi gửi đánh giá.', 'Không Thể Gửi Đánh Giá');
    } finally {
      isSubmitting.value = false;
    }
  };

  return {
    reviews,
    isLoading,
    isSubmitting,
    newRating,
    newComment,
    customName,
    authStore,
    fetchReviews,
    submitReview,
  };
}
