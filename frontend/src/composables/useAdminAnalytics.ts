import { ref, computed, onMounted } from 'vue';
import api from '../services/api';

export function useAdminAnalytics() {
  const analytics = ref<any>(null);
  const cinemas = ref<any[]>([]);
  const movies = ref<any[]>([]);
  const isLoading = ref(false);

  const selectedPeriod = ref('7days');
  const selectedCinemaId = ref<string | number>('all');
  const selectedMovieId = ref<string | number>('all');

  const formatVnd = (val?: number) => {
    if (!val) return '0 đ';
    return new Intl.NumberFormat('vi-VN').format(val) + ' đ';
  };

  const maxDailyRev = computed(() => {
    if (!analytics.value?.daily_revenue?.length) return 10000000;
    return Math.max(...analytics.value.daily_revenue.map((d: any) => d.revenue || 0), 1000000);
  });

  const loadFilterMetadata = async () => {
    try {
      const [cinemasRes, moviesRes] = await Promise.all([
        api.get('/cinemas'),
        api.get('/movies'),
      ]);
      if (cinemasRes.data?.data) cinemas.value = cinemasRes.data.data;
      if (moviesRes.data?.data) movies.value = moviesRes.data.data;
    } catch (e) {
      console.warn('Error loading analytics metadata:', e);
    }
  };

  const fetchAnalytics = async () => {
    isLoading.value = true;
    try {
      const params: any = {
        period: selectedPeriod.value,
        cinema_id: selectedCinemaId.value !== 'all' ? selectedCinemaId.value : undefined,
        movie_id: selectedMovieId.value !== 'all' ? selectedMovieId.value : undefined,
      };

      const res = await api.get('/admin/analytics', { params });
      if (res.data?.data) {
        analytics.value = res.data.data;
      }
    } catch (e) {
      console.warn('Error fetching analytics:', e);
    } finally {
      isLoading.value = false;
    }
  };

  onMounted(async () => {
    await Promise.all([loadFilterMetadata(), fetchAnalytics()]);
  });

  return {
    analytics,
    cinemas,
    movies,
    isLoading,
    selectedPeriod,
    selectedCinemaId,
    selectedMovieId,
    maxDailyRev,
    formatVnd,
    loadFilterMetadata,
    fetchAnalytics,
  };
}
