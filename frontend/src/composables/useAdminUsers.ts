import { ref, onMounted } from 'vue';
import api from '../services/api';
import { useToast } from './useToast';
import { USER_ROLES, MEMBERSHIP_TIERS } from '../constants';

export function useAdminUsers() {
  const toast = useToast();

  const users = ref<any[]>([]);
  const totalUsers = ref(0);
  const totalPages = ref(1);
  const currentPage = ref(1);
  const perPage = ref(10);
  const roleFilter = ref('all');
  const tierFilter = ref('all');
  const searchQuery = ref('');
  const isLoading = ref(false);

  // Modal State
  const isModalOpen = ref(false);
  const isEditing = ref(false);
  const editingId = ref<number | null>(null);
  const isSubmitting = ref(false);

  // Points Adjustment Modal
  const isPointsModalOpen = ref(false);
  const selectedUserForPoints = ref<any | null>(null);
  const pointsDelta = ref(100);

  const form = ref({
    name: '',
    email: '',
    phone: '',
    password: '',
    role: 'user',
    membership_tier: 'member',
    points: 100,
  });

  const roleTabs = [
    { label: 'Tất Cả Vai Trò', value: 'all' },
    ...USER_ROLES,
  ];

  const tierOptions = [
    { label: 'Tất Cả Cấp Bậc', value: 'all' },
    ...MEMBERSHIP_TIERS,
  ];

  let searchDebounce: any = null;
  const handleSearch = () => {
    clearTimeout(searchDebounce);
    searchDebounce = setTimeout(() => {
      currentPage.value = 1;
      fetchUsers();
    }, 350);
  };

  const onFilterChange = () => {
    currentPage.value = 1;
    fetchUsers();
  };

  const changePage = (p: number) => {
    currentPage.value = p;
    fetchUsers();
  };

  const fetchUsers = async () => {
    isLoading.value = true;
    try {
      const params: any = {
        page: currentPage.value,
        per_page: perPage.value,
        role: roleFilter.value !== 'all' ? roleFilter.value : undefined,
        membership_tier: tierFilter.value !== 'all' ? tierFilter.value : undefined,
        search: searchQuery.value.trim() || undefined,
      };

      const res = await api.get('/admin/users', { params });
      if (res.data?.data) {
        users.value = res.data.data;
        if (res.data.meta) {
          totalUsers.value = res.data.meta.total;
          totalPages.value = res.data.meta.last_page;
          currentPage.value = res.data.meta.current_page;
        }
      }
    } catch (e: any) {
      toast.error(e.response?.data?.message || 'Lỗi tải danh sách người dùng', 'Admin Users');
    } finally {
      isLoading.value = false;
    }
  };

  const openCreateModal = () => {
    isEditing.value = false;
    editingId.value = null;
    form.value = {
      name: '',
      email: '',
      phone: '',
      password: 'password123',
      role: 'user',
      membership_tier: 'member',
      points: 100,
    };
    isModalOpen.value = true;
  };

  const openEditModal = (user: any) => {
    isEditing.value = true;
    editingId.value = user.id;
    form.value = {
      name: user.name,
      email: user.email,
      phone: user.phone || '',
      password: '',
      role: user.role || 'user',
      membership_tier: user.membership_tier || 'member',
      points: user.points || 0,
    };
    isModalOpen.value = true;
  };

  const openPointsModal = (user: any) => {
    selectedUserForPoints.value = user;
    pointsDelta.value = 100;
    isPointsModalOpen.value = true;
  };

  const handleSubmit = async () => {
    isSubmitting.value = true;
    try {
      if (isEditing.value && editingId.value) {
        await api.put(`/admin/users/${editingId.value}`, form.value);
        toast.success(`Đã cập nhật thông tin người dùng ${form.value.name}`, 'Thành công');
      } else {
        await api.post('/admin/users', form.value);
        toast.success(`Đã thêm mới người dùng ${form.value.name}`, 'Thành công');
      }
      isModalOpen.value = false;
      await fetchUsers();
    } catch (e: any) {
      const msg = e.response?.data?.message || 'Có lỗi xảy ra khi lưu thông tin người dùng.';
      toast.error(msg, 'Lỗi Lưu Dữ Liệu');
    } finally {
      isSubmitting.value = false;
    }
  };

  const handleQuickRoleChange = async (userId: number, newRole: string) => {
    try {
      await api.patch(`/admin/users/${userId}/role`, { role: newRole });
      toast.success(`Đã chuyển quyền thành công sang [${newRole.toUpperCase()}]`, 'Cập nhật phân quyền');
      await fetchUsers();
    } catch (e: any) {
      toast.error(e.response?.data?.message || 'Không thể cập nhật vai trò.', 'Lỗi phân quyền');
    }
  };

  const handleSavePoints = async () => {
    if (!selectedUserForPoints.value) return;
    isSubmitting.value = true;
    try {
      await api.patch(`/admin/users/${selectedUserForPoints.value.id}/points`, {
        delta: Number(pointsDelta.value),
      });
      const changeText = Number(pointsDelta.value) > 0 ? `+${pointsDelta.value}` : `${pointsDelta.value}`;
      toast.success(`Đã điều chỉnh ${changeText} CinePoints cho ${selectedUserForPoints.value.name}`, 'Cập nhật điểm thưởng');
      isPointsModalOpen.value = false;
      await fetchUsers();
    } catch (e: any) {
      toast.error(e.response?.data?.message || 'Không thể điều chỉnh điểm.', 'Lỗi');
    } finally {
      isSubmitting.value = false;
    }
  };

  const handleDelete = async (id: number) => {
    if (!confirm('Bạn có chắc chắn muốn xóa tài khoản người dùng này?')) return;
    try {
      await api.delete(`/admin/users/${id}`);
      toast.success('Đã xóa người dùng khỏi hệ thống', 'Đã xóa');
      await fetchUsers();
    } catch (e: any) {
      toast.error(e.response?.data?.message || 'Không thể xóa người dùng.', 'Lỗi');
    }
  };

  onMounted(() => {
    fetchUsers();
  });

  return {
    users,
    totalUsers,
    totalPages,
    currentPage,
    roleFilter,
    tierFilter,
    searchQuery,
    isLoading,
    isModalOpen,
    isEditing,
    isSubmitting,
    isPointsModalOpen,
    selectedUserForPoints,
    pointsDelta,
    form,
    roleTabs,
    tierOptions,
    handleSearch,
    onFilterChange,
    changePage,
    openCreateModal,
    openEditModal,
    openPointsModal,
    handleSubmit,
    handleQuickRoleChange,
    handleSavePoints,
    handleDelete,
  };
}
