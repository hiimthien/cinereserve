<template>
  <div class="space-y-6">
    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-black text-white flex items-center gap-2.5">
          <TicketPercent class="w-6 h-6 text-emerald-400" />
          <span>Quản Lý Voucher & Mã Giảm Giá</span>
        </h1>
        <p class="text-xs text-cinema-muted mt-1">Cấu hình mã khuyến mãi, voucher đổi điểm thành viên VIP và chương trình giảm giá</p>
      </div>

      <button 
        @click="openCreateModal"
        class="flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-cinema-accent hover:bg-rose-600 text-white text-xs font-bold shadow-glow-accent transition-all cursor-pointer"
      >
        <Plus class="w-4 h-4" />
        <span>Tạo Mã Voucher Mới</span>
      </button>
    </div>

    <!-- Search Toolbar -->
    <div class="bg-cinema-surface/70 border border-cinema-border rounded-2xl p-3 sm:p-4 flex items-center justify-between gap-3 backdrop-blur-md">
      <div class="relative w-full sm:w-80">
        <Search class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
        <input 
          v-model="searchQuery"
          type="text"
          placeholder="Tìm mã voucher (VD: CINE50, VIP...)"
          class="w-full bg-slate-900/80 border border-cinema-border rounded-xl pl-9 pr-4 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-cinema-accent transition-colors"
        />
      </div>

      <span class="text-xs text-cinema-muted hidden sm:inline">
        Tổng số: <strong class="text-white">{{ filteredVouchers.length }}</strong> mã voucher
      </span>
    </div>

    <!-- Vouchers Grid -->
    <div v-if="isLoading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
      <div v-for="i in 6" :key="i" class="p-5 rounded-3xl bg-cinema-surface/50 border border-white/5 animate-pulse space-y-3">
        <div class="h-6 bg-white/10 rounded-lg w-1/2"></div>
        <div class="h-10 bg-white/5 rounded-xl w-full"></div>
      </div>
    </div>

    <div v-else-if="filteredVouchers.length === 0" class="p-12 text-center bg-cinema-surface/30 border border-cinema-border rounded-3xl space-y-3">
      <Gift class="w-10 h-10 text-slate-500 mx-auto" />
      <h3 class="text-base font-bold text-white">Chưa có mã voucher nào</h3>
      <p class="text-xs text-cinema-muted">Hãy tạo mã giảm giá đầu tiên cho khách hàng!</p>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
      <div 
        v-for="v in filteredVouchers" 
        :key="v.id"
        class="bg-cinema-surface/80 border border-cinema-border rounded-3xl p-5 backdrop-blur-md hover:border-white/20 transition-all flex flex-col justify-between space-y-4 shadow-lg group relative overflow-hidden"
      >
        <!-- Top Row -->
        <div class="flex items-start justify-between gap-2">
          <div class="space-y-1">
            <span class="text-xs font-mono font-black px-2.5 py-1 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 tracking-wider inline-block">
              {{ v.code }}
            </span>
            <h3 class="font-black text-white text-base mt-2 leading-tight">{{ v.title }}</h3>
            <p class="text-xs text-cinema-muted line-clamp-2">{{ v.description || 'Voucher ưu đãi đặt vé xem phim CineReserve' }}</p>
          </div>

          <span 
            class="text-[10px] font-bold px-2 py-0.5 rounded-md shrink-0"
            :class="v.is_active ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-rose-500/20 text-rose-400 border border-rose-500/30'"
          >
            {{ v.is_active ? 'Kích hoạt' : 'Tạm khóa' }}
          </span>
        </div>

        <!-- Voucher Rules Detail -->
        <div class="bg-slate-900/60 rounded-2xl p-3.5 space-y-1.5 text-xs border border-white/5">
          <div class="flex justify-between">
            <span class="text-slate-400">Mức giảm:</span>
            <strong class="text-amber-400 font-bold">
              {{ v.discount_type === 'percent' ? `${v.discount_value}% (Tối đa ${formatVnd(v.max_discount || 0)})` : formatVnd(v.discount_value) }}
            </strong>
          </div>
          <div class="flex justify-between">
            <span class="text-slate-400">Đơn tối thiểu:</span>
            <span class="text-slate-200">{{ formatVnd(v.min_order_amount || 0) }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-slate-400">Điểm đổi (VIP):</span>
            <span class="text-amber-300 font-mono font-bold">{{ v.points_cost || 0 }} Pts</span>
          </div>
          <div class="flex justify-between">
            <span class="text-slate-400">Đã dùng / Giới hạn:</span>
            <span class="text-slate-200 font-mono">{{ v.used_count || 0 }} / {{ v.usage_limit || 'Không giới hạn' }}</span>
          </div>
        </div>

        <!-- Bottom Actions -->
        <div class="pt-2 border-t border-white/5 flex items-center justify-end gap-2">
          <button 
            @click="openEditModal(v)"
            class="px-3 py-1.5 rounded-xl bg-white/5 hover:bg-white/10 text-slate-300 hover:text-white text-xs font-bold transition-colors flex items-center gap-1.5 cursor-pointer"
          >
            <Edit2 class="w-3.5 h-3.5" />
            <span>Sửa</span>
          </button>
          <button 
            @click="handleDelete(v.id)"
            class="px-3 py-1.5 rounded-xl bg-rose-500/10 hover:bg-rose-500/25 text-rose-400 text-xs font-bold transition-colors flex items-center gap-1.5 cursor-pointer"
          >
            <Trash2 class="w-3.5 h-3.5" />
            <span>Xóa</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Create / Edit Modal -->
    <BaseModal 
      v-model="isModalOpen" 
      :title="isEditing ? 'Chỉnh Sửa Voucher' : 'Tạo Mã Voucher Khuyến Mãi'"
      maxWidth="2xl"
    >

      <form @submit.prevent="handleSubmit" class="space-y-4 p-1">
        <div class="grid grid-cols-2 gap-3">
          <BaseInput 
            v-model="form.code"
            label="Mã Code *"
            placeholder="VD: CINE50K"
            required
          />
          <BaseInput 
            v-model="form.title"
            label="Tên chương trình *"
            placeholder="Giảm 50K vé xem phim"
            required
          />
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div class="space-y-1.5">
            <label class="block text-xs font-semibold text-cinema-muted">Loại giảm giá *</label>
            <select 
              v-model="form.discount_type"
              class="w-full bg-cinema-card/80 border border-cinema-border rounded-xl px-3 py-2.5 text-xs text-white focus:outline-none focus:border-cinema-accent"
              required
            >
              <option value="fixed">Số tiền cố định (VNĐ)</option>
              <option value="percent">Phần trăm (%)</option>
            </select>
          </div>

          <BaseInput 
            v-model="form.discount_value"
            type="number"
            :label="form.discount_type === 'percent' ? 'Số % giảm (VD: 20)' : 'Số tiền giảm (VNĐ)'"
            placeholder="50000"
            required
          />
        </div>

        <div class="grid grid-cols-2 gap-3">
          <BaseInput 
            v-model="form.min_order_amount"
            type="number"
            label="Đơn hàng tối thiểu (VNĐ)"
            placeholder="100000"
          />
          <BaseInput 
            v-model="form.points_cost"
            type="number"
            label="Điểm CinePoints đổi (VIP)"
            placeholder="50"
          />
        </div>

        <BaseInput 
          v-model="form.description"
          label="Mô tả ưu đãi"
          placeholder="Áp dụng cho mọi suất chiếu 2D/3D tại toàn hệ thống..."
        />

        <div class="flex items-center gap-2 pt-1">
          <input 
            type="checkbox" 
            id="voucher_active" 
            v-model="form.is_active"
            class="w-4 h-4 rounded text-cinema-accent bg-slate-900 border-cinema-border cursor-pointer"
          />
          <label for="voucher_active" class="text-xs text-slate-300 font-semibold cursor-pointer">
            Kích hoạt voucher cho phép khách sử dụng ngay
          </label>
        </div>

        <div class="flex items-center justify-end gap-3 pt-3">
          <BaseButton 
            type="button" 
            variant="secondary" 
            @click="isModalOpen = false"
          >
            Hủy Bỏ
          </BaseButton>
          <BaseButton 
            type="submit" 
            variant="primary" 
            :loading="isSubmitting"
          >
            {{ isEditing ? 'Lưu Thay Đổi' : 'Tạo Voucher' }}
          </BaseButton>
        </div>
      </form>
    </BaseModal>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { TicketPercent, Plus, Search, Edit2, Trash2, Gift } from 'lucide-vue-next';
import api from '../../services/api';
import BaseModal from '../../components/base/BaseModal.vue';
import BaseInput from '../../components/base/BaseInput.vue';
import BaseButton from '../../components/base/BaseButton.vue';

const vouchers = ref<any[]>([]);
const isLoading = ref(false);
const isSubmitting = ref(false);
const isModalOpen = ref(false);
const isEditing = ref(false);
const editingId = ref<number | null>(null);
const searchQuery = ref('');

const form = ref({
  code: '',
  title: '',
  discount_type: 'fixed',
  discount_value: 30000,
  min_order_amount: 0,
  max_discount: 0,
  description: '',
  points_cost: 0,
  usage_limit: 1000,
  is_active: true,
});

const formatVnd = (val: number) => {
  if (!val) return '0 đ';
  return new Intl.NumberFormat('vi-VN').format(val) + ' đ';
};

const filteredVouchers = computed(() => {
  let list = vouchers.value;
  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase();
    list = list.filter(v => v.code.toLowerCase().includes(q) || v.title.toLowerCase().includes(q));
  }
  return list;
});

const fetchVouchers = async () => {
  isLoading.value = true;
  try {
    const res = await api.get('/admin/vouchers');
    if (res.data?.data) {
      vouchers.value = res.data.data;
    }
  } catch (e) {
    console.warn('Error fetching vouchers:', e);
  } finally {
    isLoading.value = false;
  }
};

const openCreateModal = () => {
  isEditing.value = false;
  editingId.value = null;
  form.value = {
    code: '',
    title: '',
    discount_type: 'fixed',
    discount_value: 30000,
    min_order_amount: 0,
    max_discount: 0,
    description: '',
    points_cost: 0,
    usage_limit: 1000,
    is_active: true,
  };
  isModalOpen.value = true;
};

const openEditModal = (v: any) => {
  isEditing.value = true;
  editingId.value = v.id;
  form.value = {
    code: v.code,
    title: v.title,
    discount_type: v.discount_type || 'fixed',
    discount_value: v.discount_value,
    min_order_amount: v.min_order_amount || 0,
    max_discount: v.max_discount || 0,
    description: v.description || '',
    points_cost: v.points_cost || 0,
    usage_limit: v.usage_limit || 1000,
    is_active: Boolean(v.is_active),
  };
  isModalOpen.value = true;
};

const handleSubmit = async () => {
  isSubmitting.value = true;
  try {
    if (isEditing.value && editingId.value) {
      await api.put(`/admin/vouchers/${editingId.value}`, form.value);
    } else {
      await api.post('/admin/vouchers', form.value);
    }
    isModalOpen.value = false;
    await fetchVouchers();
  } catch (e: any) {
    alert(e.response?.data?.message || 'Có lỗi xảy ra khi lưu voucher.');
  } finally {
    isSubmitting.value = false;
  }
};

const handleDelete = async (id: number) => {
  if (!confirm('Bạn có chắc chắn muốn xóa voucher này không?')) return;
  try {
    await api.delete(`/admin/vouchers/${id}`);
    await fetchVouchers();
  } catch (e: any) {
    alert(e.response?.data?.message || 'Có lỗi xảy ra khi xóa voucher.');
  }
};

onMounted(() => {
  fetchVouchers();
});
</script>
