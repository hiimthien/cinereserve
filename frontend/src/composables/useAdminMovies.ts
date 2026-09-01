import { ref, onMounted } from 'vue';
import api from '../services/api';
import { useToast } from './useToast';

export function useAdminMovies() {
  const toast = useToast();
  const movies = ref<any[]>([]);
  const totalMovies = ref(0);
  const totalPages = ref(1);
  const currentPage = ref(1);
  const perPage = ref(8);
  const statusFilter = ref('all');
  const searchQuery = ref('');
  const isLoading = ref(false);

  const isModalOpen = ref(false);
  const isEditing = ref(false);
  const editingId = ref<number | null>(null);
  const isSubmitting = ref(false);

  const form = ref({
    title: '',
    original_title: '',
    description: '',
    duration: 120,
    release_date: '',
    rating: 8.5,
    poster_url: '',
    backdrop_url: '',
    trailer_url: '',
    age_rating: 'T18',
    status: 'now_showing',
  });

  const formatReleaseDate = (val?: string) => {
    if (!val) return 'Chưa xác định';
    const clean = val.split('T')[0].split(' ')[0];
    const parts = clean.split('-');
    if (parts.length === 3) {
      return `${parts[2]}/${parts[1]}/${parts[0]}`;
    }
    return clean;
  };

  const formatStatus = (status: string) => {
    switch (status) {
      case 'now_showing': return '🟢 ĐANG CHIẾU';
      case 'early_premiere': return '✨ SUẤT CHIẾU SỚM';
      default: return '⏳ SẮP CHIẾU';
    }
  };

  const getBadgeVariant = (status: string): 'emerald' | 'purple' | 'amber' => {
    switch (status) {
      case 'now_showing': return 'emerald';
      case 'early_premiere': return 'purple';
      default: return 'amber';
    }
  };

  let searchDebounce: any = null;
  const handleSearch = () => {
    clearTimeout(searchDebounce);
    searchDebounce = setTimeout(() => {
      currentPage.value = 1;
      fetchMovies();
    }, 350);
  };

  const onFilterChange = () => {
    currentPage.value = 1;
    fetchMovies();
  };

  const changePage = (p: number) => {
    currentPage.value = p;
    fetchMovies();
  };

  const fetchMovies = async () => {
    isLoading.value = true;
    try {
      const params: any = {
        page: currentPage.value,
        per_page: perPage.value,
        status: statusFilter.value !== 'all' ? statusFilter.value : undefined,
        search: searchQuery.value.trim() || undefined,
      };

      const res = await api.get('/admin/movies', { params });
      if (res.data?.data) {
        movies.value = res.data.data;
        if (res.data.meta) {
          totalMovies.value = res.data.meta.total;
          totalPages.value = res.data.meta.last_page;
          currentPage.value = res.data.meta.current_page;
        } else if (res.data.pagination) {
          totalMovies.value = res.data.pagination.total;
          totalPages.value = res.data.pagination.last_page;
          currentPage.value = res.data.pagination.current_page;
        }
      }
    } catch (e) {
      console.warn('Error fetching admin movies:', e);
    } finally {
      isLoading.value = false;
    }
  };

  const openCreateModal = () => {
    isEditing.value = false;
    editingId.value = null;
    form.value = {
      title: '',
      original_title: '',
      description: '',
      duration: 120,
      release_date: new Date().toISOString().split('T')[0],
      rating: 8.5,
      poster_url: 'https://images.unsplash.com/photo-1534447677768-be436bb09401?w=600',
      backdrop_url: '',
      trailer_url: '',
      age_rating: 'T18',
      status: 'now_showing',
    };
    isModalOpen.value = true;
  };

  const openEditModal = (movie: any) => {
    isEditing.value = true;
    editingId.value = movie.id;
    form.value = {
      title: movie.title,
      original_title: movie.original_title || '',
      description: movie.description || '',
      duration: movie.duration || movie.duration_minutes || 120,
      release_date: movie.release_date ? movie.release_date.split('T')[0] : '',
      rating: movie.rating || 8.5,
      poster_url: movie.poster_url || '',
      backdrop_url: movie.backdrop_url || '',
      trailer_url: movie.trailer_url || '',
      age_rating: movie.age_rating || 'T18',
      status: movie.status || 'now_showing',
    };
    isModalOpen.value = true;
  };

  const handleSubmit = async () => {
    isSubmitting.value = true;
    try {
      if (isEditing.value && editingId.value) {
        await api.put(`/admin/movies/${editingId.value}`, form.value);
        toast.success(`Cập nhật thông tin phim "${form.value.title}" thành công!`, 'Thành Công');
      } else {
        await api.post('/admin/movies', form.value);
        toast.success(`Thêm mới phim "${form.value.title}" thành công!`, 'Thành Công');
      }
      isModalOpen.value = false;
      await fetchMovies();
    } catch (e: any) {
      toast.error(e.response?.data?.message || 'Có lỗi xảy ra khi lưu thông tin phim.', 'Lỗi Lưu Phim');
    } finally {
      isSubmitting.value = false;
    }
  };

  const handleDelete = async (id: number) => {
    if (!confirm('Bạn có chắc chắn muốn xóa phim này khỏi hệ thống?')) return;
    try {
      await api.delete(`/admin/movies/${id}`);
      toast.success('Đã xóa phim khỏi hệ thống!', 'Đã Xóa');
      await fetchMovies();
    } catch (e: any) {
      toast.error(e.response?.data?.message || 'Không thể xóa phim.', 'Lỗi Xóa');
    }
  };

  onMounted(() => {
    fetchMovies();
  });

  return {
    movies,
    totalMovies,
    totalPages,
    currentPage,
    perPage,
    statusFilter,
    searchQuery,
    isLoading,
    isModalOpen,
    isEditing,
    editingId,
    isSubmitting,
    form,
    formatReleaseDate,
    formatStatus,
    getBadgeVariant,
    handleSearch,
    onFilterChange,
    changePage,
    fetchMovies,
    openCreateModal,
    openEditModal,
    handleSubmit,
    handleDelete,
  };
}
