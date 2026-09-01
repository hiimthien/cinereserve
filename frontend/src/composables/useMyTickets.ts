import { ref, watch, onMounted } from 'vue';
import { useAuthStore } from '../stores/authStore';
import { useToast } from './useToast';
import api from '../services/api';

export function useMyTickets() {
  const authStore = useAuthStore();
  const toast = useToast();

  const tickets = ref<any[]>([]);
  const totalTicketsCount = ref(0);
  const totalPages = ref(1);
  const currentPage = ref(1);
  const perPage = ref(6);
  const activeStatus = ref('all');
  const selectedMovieId = ref<string | number>('all');
  const selectedCinemaId = ref<string | number>('all');
  const searchQuery = ref('');
  const isLoading = ref(false);

  const availableMovies = ref<any[]>([]);
  const availableCinemas = ref<any[]>([]);

  // Detail Modal state
  const isDetailModalOpen = ref(false);
  const selectedTicket = ref<any | null>(null);

  const fetchFiltersCatalog = async () => {
    try {
      const [moviesRes, cinemasRes] = await Promise.all([
        api.get('/movies'),
        api.get('/cinemas'),
      ]);
      if (moviesRes.data?.data) availableMovies.value = moviesRes.data.data;
      if (cinemasRes.data?.data) availableCinemas.value = cinemasRes.data.data;
    } catch (e) {
      console.warn('Could not fetch filter options:', e);
    }
  };

  const fetchTickets = async () => {
    if (!authStore.isAuthenticated && !authStore.user?.email) {
      tickets.value = [];
      totalTicketsCount.value = 0;
      return;
    }

    isLoading.value = true;
    try {
      const params: any = {
        email: authStore.user?.email,
        page: currentPage.value,
        per_page: perPage.value,
        status: activeStatus.value !== 'all' ? activeStatus.value : undefined,
        movie_id: selectedMovieId.value !== 'all' ? selectedMovieId.value : undefined,
        cinema_id: selectedCinemaId.value !== 'all' ? selectedCinemaId.value : undefined,
        search: searchQuery.value.trim() || undefined,
      };

      const res = await api.get('/bookings', { params });
      if (res.data?.data) {
        tickets.value = res.data.data;
        if (res.data.meta) {
          totalTicketsCount.value = res.data.meta.total;
          totalPages.value = res.data.meta.last_page;
          currentPage.value = res.data.meta.current_page;
        }
      }
    } catch (err: any) {
      toast.error(err.response?.data?.message || 'Không thể tải danh sách vé.', 'Lỗi tải vé');
    } finally {
      isLoading.value = false;
    }
  };

  const openTicketDetail = (ticket: any) => {
    selectedTicket.value = ticket;
    isDetailModalOpen.value = true;
  };

  const resetFilters = () => {
    activeStatus.value = 'all';
    selectedMovieId.value = 'all';
    selectedCinemaId.value = 'all';
    searchQuery.value = '';
    currentPage.value = 1;
    fetchTickets();
  };

  const changePage = (page: number) => {
    currentPage.value = page;
    fetchTickets();
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };

  watch(() => authStore.user?.email, () => {
    currentPage.value = 1;
    fetchTickets();
  });

  onMounted(async () => {
    await fetchFiltersCatalog();
    if (authStore.isAuthenticated) {
      await fetchTickets();
    }
  });

  return {
    authStore,
    tickets,
    totalTicketsCount,
    totalPages,
    currentPage,
    activeStatus,
    selectedMovieId,
    selectedCinemaId,
    searchQuery,
    isLoading,
    availableMovies,
    availableCinemas,
    isDetailModalOpen,
    selectedTicket,
    fetchTickets,
    openTicketDetail,
    resetFilters,
    changePage,
  };
}
