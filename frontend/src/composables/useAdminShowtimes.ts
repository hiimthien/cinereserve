import { ref, computed, onMounted } from 'vue';
import api from '../services/api';
import { useToast } from './useToast';

export function useAdminShowtimes() {
  const toast = useToast();
  const moviesList = ref<any[]>([]);
  const cinemasList = ref<any[]>([]);
  const allShowtimes = ref<any[]>([]);
  const isLoading = ref(false);
  const isSubmitting = ref(false);

  const searchQuery = ref('');
  const movieStatusFilter = ref('all');
  const currentMoviePage = ref(1);
  const moviesPerPage = 6;

  const movieStatusTabs = [
    { label: 'Tất Cả Phim', value: 'all' },
    { label: '🟢 Đang Chiếu', value: 'now_showing' },
    { label: '✨ Suất Chiếu Sớm', value: 'early_premiere' },
    { label: '⏳ Sắp Khởi Chiếu', value: 'coming_soon' },
  ];

  // Detail Modal State
  const isDetailModalOpen = ref(false);
  const selectedMovieDetail = ref<any | null>(null);

  // Create / Batch Modal State
  const isModalOpen = ref(false);
  const creationMode = ref<'single' | 'batch'>('single');
  const selectedCinemaId = ref<string | number>('');
  const availableRooms = ref<any[]>([]);

  const availableTimeSlots = ['09:30', '11:45', '14:00', '16:30', '19:15', '21:45', '23:30'];

  const form = ref({
    movie_id: '' as string | number,
    room_id: '' as string | number,
    show_date: new Date().toISOString().split('T')[0],
    start_time: '19:30',
    format: '2D Standard',
    status: 'scheduled',
    base_price: 95000,
    price_vip: 115000,
    price_couple: 200000,
  });

  const batchForm = ref({
    cinema_ids: [] as number[],
    start_date: new Date().toISOString().split('T')[0],
    days_count: 7,
    time_slots: ['10:15', '14:30', '19:30'],
  });

  // Edit Showtime Modal State
  const isEditModalOpen = ref(false);
  const editingShowtimeId = ref<number | null>(null);
  const editForm = ref({
    show_date: '',
    start_time: '',
    format: '2D Standard',
    status: 'scheduled',
    base_price: 95000,
    price_vip: 115000,
    price_couple: 200000,
  });

  const formatVnd = (val: number) => {
    if (!val) return '0 đ';
    return new Intl.NumberFormat('vi-VN').format(val) + ' đ';
  };

  const formatDate = (val?: string) => {
    if (!val) return 'Hôm nay';
    const clean = val.split('T')[0].split(' ')[0];
    const parts = clean.split('-');
    if (parts.length === 3) return `${parts[2]}/${parts[1]}/${parts[0]}`;
    return clean;
  };

  const formatDateShort = (val?: string) => {
    if (!val) return 'Hôm nay';
    const clean = val.split('T')[0].split(' ')[0];
    const parts = clean.split('-');
    if (parts.length === 3) return `${parts[2]}/${parts[1]}`;
    return clean;
  };

  const formatStatus = (status: string) => {
    switch (status) {
      case 'now_showing': return '🟢 Đang Chiếu';
      case 'early_premiere': return '✨ Suất Chiếu Sớm';
      default: return '⏳ Sắp Chiếu';
    }
  };

  const getBadgeVariant = (status: string): 'emerald' | 'purple' | 'amber' => {
    switch (status) {
      case 'now_showing': return 'emerald';
      case 'early_premiere': return 'purple';
      default: return 'amber';
    }
  };

  const onBasePriceChange = () => {
    const base = Number(form.value.base_price) || 95000;
    form.value.price_vip = base + 15000;
    form.value.price_couple = base * 2;
  };

  const onEditBasePriceChange = () => {
    const base = Number(editForm.value.base_price) || 95000;
    editForm.value.price_vip = base + 15000;
    editForm.value.price_couple = base * 2;
  };

  const filteredMovies = computed(() => {
    let list = moviesList.value;
    if (movieStatusFilter.value !== 'all') {
      list = list.filter(m => m.status === movieStatusFilter.value);
    }
    if (searchQuery.value.trim()) {
      const q = searchQuery.value.toLowerCase();
      list = list.filter(m => m.title?.toLowerCase().includes(q) || m.original_title?.toLowerCase().includes(q));
    }
    return list;
  });

  const totalMoviePages = computed(() => Math.ceil(filteredMovies.value.length / moviesPerPage) || 1);

  const paginatedMovies = computed(() => {
    const start = (currentMoviePage.value - 1) * moviesPerPage;
    return filteredMovies.value.slice(start, start + moviesPerPage);
  });

  const getMovieShowtimesCount = (movieId: number) => {
    return allShowtimes.value.filter(st => st.movie_id === movieId).length;
  };

  const getMovieSampleShowtimes = (movieId: number) => {
    return allShowtimes.value.filter(st => st.movie_id === movieId).slice(0, 4);
  };

  const movieDetailShowtimes = computed(() => {
    if (!selectedMovieDetail.value) return [];
    return allShowtimes.value.filter(st => st.movie_id === selectedMovieDetail.value.id);
  });

  const onCinemaChange = (cinemaId: string | number) => {
    const cinema = cinemasList.value.find(c => c.id == cinemaId);
    if (cinema && cinema.rooms?.length > 0) {
      availableRooms.value = cinema.rooms;
      form.value.room_id = cinema.rooms[0].id;
    } else {
      availableRooms.value = [
        { id: 1, name: 'Phòng Chiếu 1', room_type: '2D Standard' },
        { id: 2, name: 'Phòng Chiếu 2', room_type: 'IMAX Laser' },
      ];
      form.value.room_id = availableRooms.value[0].id;
    }
  };

  const onMovieSelectChange = (movieId: string | number) => {
    const movie = moviesList.value.find(m => m.id == movieId);
    if (movie?.release_date) {
      const cleanDate = movie.release_date.split('T')[0];
      form.value.show_date = cleanDate;
      batchForm.value.start_date = cleanDate;
    }
  };

  const toggleSelectAllCinemas = () => {
    if (batchForm.value.cinema_ids.length === cinemasList.value.length) {
      batchForm.value.cinema_ids = [];
    } else {
      batchForm.value.cinema_ids = cinemasList.value.map(c => c.id);
    }
  };

  const openDetailModal = (movie: any) => {
    selectedMovieDetail.value = movie;
    isDetailModalOpen.value = true;
  };

  const openCreateModal = (movie?: any) => {
    creationMode.value = 'single';
    
    if (movie) {
      form.value.movie_id = movie.id;
      if (movie.release_date) {
        const cleanDate = movie.release_date.split('T')[0];
        form.value.show_date = cleanDate;
        batchForm.value.start_date = cleanDate;
      }
    } else if (moviesList.value.length > 0) {
      form.value.movie_id = moviesList.value[0].id;
    }

    if (cinemasList.value.length > 0) {
      selectedCinemaId.value = cinemasList.value[0].id;
      onCinemaChange(selectedCinemaId.value);
      batchForm.value.cinema_ids = cinemasList.value.map(c => c.id);
    }

    form.value.start_time = '19:30';
    form.value.format = '2D Standard';
    form.value.status = 'scheduled';
    form.value.base_price = 95000;
    form.value.price_vip = 115000;
    form.value.price_couple = 200000;
    isModalOpen.value = true;
  };

  const openEditShowtimeModal = (st: any) => {
    editingShowtimeId.value = st.id;
    editForm.value = {
      show_date: st.show_date ? st.show_date.split('T')[0] : '',
      start_time: st.start_time,
      format: st.format || '2D Standard',
      status: st.status || 'scheduled',
      base_price: st.base_price || 95000,
      price_vip: st.price_vip || (Number(st.base_price || 95000) + 15000),
      price_couple: st.price_couple || (Number(st.base_price || 95000) * 2),
    };
    isEditModalOpen.value = true;
  };

  const loadData = async () => {
    isLoading.value = true;
    try {
      const [moviesRes, cinemasRes, showtimesRes] = await Promise.all([
        api.get('/movies'),
        api.get('/cinemas'),
        api.get('/admin/showtimes?per_page=500'),
      ]);

      if (moviesRes.data?.data) moviesList.value = moviesRes.data.data;
      if (cinemasRes.data?.data) cinemasList.value = cinemasRes.data.data;
      if (showtimesRes.data?.data) allShowtimes.value = showtimesRes.data.data;
    } catch (e) {
      console.warn('Error loading showtimes data:', e);
    } finally {
      isLoading.value = false;
    }
  };

  const handleSubmitSingle = async () => {
    isSubmitting.value = true;
    try {
      await api.post('/admin/showtimes', form.value);
      toast.success('Tạo suất chiếu mới thành công!', 'Thành Công');
      isModalOpen.value = false;
      await loadData();
    } catch (e: any) {
      toast.error(e.response?.data?.message || 'Có lỗi xảy ra khi tạo suất chiếu.', 'Lỗi Tạo Suất Chiếu');
    } finally {
      isSubmitting.value = false;
    }
  };

  const handleSubmitBatch = async () => {
    if (batchForm.value.cinema_ids.length === 0) {
      toast.warning('Vui lòng chọn ít nhất 1 cụm rạp để tạo lịch chiếu!', 'Thiếu Thông Tin');
      return;
    }
    if (batchForm.value.time_slots.length === 0) {
      toast.warning('Vui lòng chọn ít nhất 1 khung giờ chiếu!', 'Thiếu Thông Tin');
      return;
    }

    isSubmitting.value = true;
    try {
      const payload = {
        movie_id: form.value.movie_id,
        cinema_ids: batchForm.value.cinema_ids,
        start_date: batchForm.value.start_date,
        days_count: batchForm.value.days_count,
        time_slots: batchForm.value.time_slots,
        base_price: form.value.base_price,
        price_vip: form.value.price_vip,
        price_couple: form.value.price_couple,
        format: form.value.format,
        status: form.value.status,
      };

      const res = await api.post('/admin/showtimes/batch', payload);
      toast.success(res.data?.message || 'Đã tạo hàng loạt suất chiếu thành công!', 'Tạo Lịch Thành Công');
      isModalOpen.value = false;
      await loadData();
    } catch (e: any) {
      toast.error(e.response?.data?.message || 'Có lỗi xảy ra khi tạo hàng loạt suất chiếu.', 'Lỗi Tạo Lịch Chiếu');
    } finally {
      isSubmitting.value = false;
    }
  };

  const handleUpdateShowtime = async () => {
    if (!editingShowtimeId.value) return;
    isSubmitting.value = true;
    try {
      await api.put(`/admin/showtimes/${editingShowtimeId.value}`, editForm.value);
      toast.success('Đã cập nhật thông tin suất chiếu!', 'Thành Công');
      isEditModalOpen.value = false;
      await loadData();
    } catch (e: any) {
      toast.error(e.response?.data?.message || 'Không thể cập nhật suất chiếu.', 'Lỗi Cập Nhật');
    } finally {
      isSubmitting.value = false;
    }
  };

  const handleDelete = async (id: number) => {
    if (!confirm('Bạn có chắc chắn muốn xóa suất chiếu này không?')) return;
    try {
      await api.delete(`/admin/showtimes/${id}`);
      toast.success('Đã xóa suất chiếu khỏi hệ thống!', 'Đã Xóa');
      await loadData();
    } catch (e: any) {
      toast.error(e.response?.data?.message || 'Không thể xóa suất chiếu.', 'Lỗi Xóa');
    }
  };

  onMounted(() => {
    loadData();
  });

  return {
    moviesList,
    cinemasList,
    allShowtimes,
    isLoading,
    isSubmitting,
    searchQuery,
    movieStatusFilter,
    currentMoviePage,
    moviesPerPage,
    movieStatusTabs,
    isDetailModalOpen,
    selectedMovieDetail,
    isModalOpen,
    creationMode,
    selectedCinemaId,
    availableRooms,
    availableTimeSlots,
    form,
    batchForm,
    isEditModalOpen,
    editingShowtimeId,
    editForm,
    filteredMovies,
    totalMoviePages,
    paginatedMovies,
    movieDetailShowtimes,
    formatVnd,
    formatDate,
    formatDateShort,
    formatStatus,
    getBadgeVariant,
    onBasePriceChange,
    onEditBasePriceChange,
    getMovieShowtimesCount,
    getMovieSampleShowtimes,
    onCinemaChange,
    onMovieSelectChange,
    toggleSelectAllCinemas,
    openDetailModal,
    openCreateModal,
    openEditShowtimeModal,
    loadData,
    handleSubmitSingle,
    handleSubmitBatch,
    handleUpdateShowtime,
    handleDelete,
  };
}
