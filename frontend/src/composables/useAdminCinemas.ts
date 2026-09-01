import { ref, onMounted } from 'vue';
import api from '../services/api';
import { useToast } from './useToast';
import { ADMIN_CITY_OPTIONS } from '../constants';

export function useAdminCinemas() {
  const toast = useToast();

  const cinemas = ref<any[]>([]);
  const totalCinemas = ref(0);
  const totalPages = ref(1);
  const currentPage = ref(1);
  const perPage = ref(8);
  const cityFilter = ref('all');
  const searchQuery = ref('');
  const isLoading = ref(false);

  const isModalOpen = ref(false);
  const isEditing = ref(false);
  const editingId = ref<number | null>(null);
  const isSubmitting = ref(false);

  const form = ref({
    name: '',
    address: '',
    city: 'Hồ Chí Minh',
    default_rooms_count: 2,
  });

  const cityOptions = ADMIN_CITY_OPTIONS;

  let searchDebounce: any = null;
  const handleSearch = () => {
    clearTimeout(searchDebounce);
    searchDebounce = setTimeout(() => {
      currentPage.value = 1;
      fetchCinemas();
    }, 350);
  };

  const onFilterChange = () => {
    currentPage.value = 1;
    fetchCinemas();
  };

  const changePage = (p: number) => {
    currentPage.value = p;
    fetchCinemas();
  };

  const fetchCinemas = async () => {
    isLoading.value = true;
    try {
      const params: any = {
        page: currentPage.value,
        per_page: perPage.value,
        city: cityFilter.value !== 'all' ? cityFilter.value : undefined,
        search: searchQuery.value.trim() || undefined,
      };

      const res = await api.get('/admin/cinemas', { params });
      if (res.data?.data) {
        cinemas.value = res.data.data;
        if (res.data.meta) {
          totalCinemas.value = res.data.meta.total;
          totalPages.value = res.data.meta.last_page;
          currentPage.value = res.data.meta.current_page;
        }
      }
    } catch (e: any) {
      toast.error(e.response?.data?.message || 'Lỗi tải danh sách rạp', 'Admin Cinemas');
    } finally {
      isLoading.value = false;
    }
  };

  const openCreateModal = () => {
    isEditing.value = false;
    editingId.value = null;
    form.value = {
      name: '',
      address: '',
      city: 'Hồ Chí Minh',
      default_rooms_count: 2,
    };
    isModalOpen.value = true;
  };

  const openEditModal = (cinema: any) => {
    isEditing.value = true;
    editingId.value = cinema.id;
    form.value = {
      name: cinema.name,
      address: cinema.address,
      city: cinema.city || 'Hồ Chí Minh',
      default_rooms_count: 2,
    };
    isModalOpen.value = true;
  };

  const handleSubmit = async () => {
    if (!form.value.name.trim()) {
      toast.error('Tên cụm rạp không được để trống.', 'Dữ liệu không hợp lệ');
      return;
    }
    if (!form.value.address.trim()) {
      toast.error('Địa chỉ cụm rạp không được để trống.', 'Dữ liệu không hợp lệ');
      return;
    }

    isSubmitting.value = true;
    try {
      if (isEditing.value && editingId.value) {
        await api.put(`/admin/cinemas/${editingId.value}`, form.value);
        toast.success(`Đã cập nhật thông tin rạp ${form.value.name}`, 'Thành công');
      } else {
        await api.post('/admin/cinemas', form.value);
        toast.success(`Đã thêm mới cụm rạp ${form.value.name} (${form.value.default_rooms_count} phòng chiếu)`, 'Thành công');
      }
      isModalOpen.value = false;
      await fetchCinemas();
    } catch (e: any) {
      const msg = e.response?.data?.message || 'Có lỗi xảy ra khi lưu thông tin cụm rạp.';
      toast.error(msg, 'Lỗi Lưu Dữ Liệu');
    } finally {
      isSubmitting.value = false;
    }
  };

  const handleDelete = async (id: number) => {
    if (!confirm('Bạn có chắc chắn muốn xóa cụm rạp này khỏi hệ thống? Các phòng chiếu và suất chiếu liên quan cũng sẽ bị ảnh hưởng.')) return;
    try {
      await api.delete(`/admin/cinemas/${id}`);
      toast.success('Đã xóa cụm rạp khỏi hệ thống', 'Đã xóa');
      await fetchCinemas();
    } catch (e: any) {
      toast.error(e.response?.data?.message || 'Không thể xóa cụm rạp.', 'Lỗi');
    }
  };

  onMounted(() => {
    fetchCinemas();
  });

  return {
    cinemas,
    totalCinemas,
    totalPages,
    currentPage,
    perPage,
    cityFilter,
    searchQuery,
    isLoading,
    isModalOpen,
    isEditing,
    isSubmitting,
    form,
    cityOptions,
    handleSearch,
    onFilterChange,
    changePage,
    fetchCinemas,
    openCreateModal,
    openEditModal,
    handleSubmit,
    handleDelete,
  };
}
