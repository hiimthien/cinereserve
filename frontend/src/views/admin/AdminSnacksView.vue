<template>
  <div class="space-y-6">
    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-black text-white flex items-center gap-2.5">
          <Popcorn class="w-6 h-6 text-amber-400" />
          <span>Quản Lý Bắp Nước & Combo</span>
        </h1>
        <p class="text-xs text-cinema-muted mt-1">Cấu hình danh mục bắp rang bơ, nước giải khát và các gói combo khuyến mãi</p>
      </div>

      <button 
        @click="openCreateModal"
        class="flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-cinema-accent hover:bg-rose-600 text-white text-xs font-bold shadow-glow-accent transition-all cursor-pointer"
      >
        <Plus class="w-4 h-4" />
        <span>Thêm Món / Combo Mới</span>
      </button>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="bg-cinema-surface/70 border border-cinema-border rounded-2xl p-3 sm:p-4 flex flex-col sm:flex-row items-center justify-between gap-3 backdrop-blur-md">
      <!-- Category Tabs -->
      <div class="flex items-center gap-1.5 overflow-x-auto pb-1 sm:pb-0 w-full sm:w-auto scrollbar-none">
        <button
          v-for="cat in categories"
          :key="cat.value"
          @click="selectedCategory = cat.value"
          class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer whitespace-nowrap"
          :class="[
            selectedCategory === cat.value
              ? 'bg-cinema-accent text-white shadow-glow-accent'
              : 'bg-white/5 text-slate-400 hover:text-white'
          ]"
        >
          {{ cat.label }}
        </button>
      </div>

      <!-- Search Input -->
      <div class="relative w-full sm:w-64">
        <Search class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
        <input 
          v-model="searchQuery"
          type="text"
          placeholder="Tìm tên bắp, nước, combo..."
          class="w-full bg-slate-900/80 border border-cinema-border rounded-xl pl-9 pr-4 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-cinema-accent transition-colors"
        />
      </div>
    </div>

    <!-- Snacks Grid -->
    <div v-if="isLoading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
      <div v-for="i in 6" :key="i" class="p-5 rounded-3xl bg-cinema-surface/50 border border-white/5 animate-pulse space-y-3">
        <div class="h-36 bg-white/5 rounded-2xl w-full"></div>
        <div class="h-5 bg-white/10 rounded-lg w-2/3"></div>
      </div>
    </div>

    <div v-else-if="filteredSnacks.length === 0" class="p-12 text-center bg-cinema-surface/30 border border-cinema-border rounded-3xl space-y-3">
      <Popcorn class="w-10 h-10 text-slate-500 mx-auto" />
      <h3 class="text-base font-bold text-white">Chưa có món ăn hoặc combo nào</h3>
      <p class="text-xs text-cinema-muted">Hãy thêm món bắp nước đầu tiên cho rạp!</p>
    </div>

    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
      <div 
        v-for="snack in filteredSnacks" 
        :key="snack.id"
        class="bg-cinema-surface/80 border border-cinema-border rounded-3xl overflow-hidden shadow-lg backdrop-blur-md hover:border-white/20 transition-all flex flex-col justify-between group"
      >
        <!-- Image & Category Badge -->
        <div class="relative h-44 bg-slate-900 overflow-hidden">
          <img 
            :src="snack.image_url || 'https://images.unsplash.com/photo-1572177812156-58036aae439c?w=600'" 
            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
          />
          <div class="absolute inset-0 bg-gradient-to-t from-cinema-surface via-transparent to-transparent"></div>
          
          <div class="absolute top-3 left-3">
            <span 
              class="text-[10px] font-extrabold uppercase tracking-wider px-2.5 py-1 rounded-lg border backdrop-blur-md shadow-sm"
              :class="getCategoryColor(snack.category)"
            >
              {{ formatCategory(snack.category) }}
            </span>
          </div>

          <div class="absolute top-3 right-3">
            <span 
              class="text-[10px] font-bold px-2 py-0.5 rounded-md"
              :class="snack.is_available ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-rose-500/20 text-rose-400 border border-rose-500/30'"
            >
              {{ snack.is_available ? 'Còn hàng' : 'Tạm hết' }}
            </span>
          </div>
        </div>

        <!-- Content Details -->
        <div class="p-4 space-y-2 flex-1 flex flex-col justify-between">
          <div>
            <h3 class="font-extrabold text-white text-base line-clamp-1">{{ snack.name }}</h3>
            <p class="text-xs text-cinema-muted line-clamp-2 mt-0.5">{{ snack.description || 'Combo bắp nước rạp chiếu phim CineReserve' }}</p>
          </div>

          <div class="pt-2 border-t border-white/5 flex items-center justify-between">
            <span class="text-base font-black text-amber-400 font-mono">{{ formatVnd(snack.price) }}</span>

            <!-- Action Buttons -->
            <div class="flex items-center gap-1.5">
              <button 
                @click="openEditModal(snack)"
                class="p-2 rounded-xl bg-white/5 hover:bg-white/10 text-slate-300 hover:text-white transition-colors cursor-pointer"
                title="Sửa món"
              >
                <Edit2 class="w-3.5 h-3.5" />
              </button>
              <button 
                @click="handleDelete(snack.id)"
                class="p-2 rounded-xl bg-rose-500/10 hover:bg-rose-500/25 text-rose-400 transition-colors cursor-pointer"
                title="Xóa món"
              >
                <Trash2 class="w-3.5 h-3.5" />
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Create / Edit Modal -->
    <BaseModal 
      v-model="isModalOpen" 
      :title="isEditing ? 'Chỉnh Sửa Món Ăn / Combo' : 'Thêm Món Ăn / Combo Mới'"
      maxWidth="2xl"
    >

      <form @submit.prevent="handleSubmit" class="space-y-4 p-1">
        <BaseInput 
          v-model="form.name"
          label="Tên món / combo"
          placeholder="Ví dụ: Combo 2 Bắp + 2 Nước Sweet Love"
          required
        />

        <div class="grid grid-cols-2 gap-3">
          <div class="space-y-1.5">
            <label class="block text-xs font-semibold text-cinema-muted">Danh mục *</label>
            <select 
              v-model="form.category"
              class="w-full bg-cinema-card/80 border border-cinema-border rounded-xl px-3 py-2.5 text-xs text-white focus:outline-none focus:border-cinema-accent"
              required
            >
              <option value="combo">Combo Ưu Đãi</option>
              <option value="popcorn">Bắp Rang Bơ</option>
              <option value="drink">Nước Ngọt & Giải Khát</option>
              <option value="snack">Đồ Ăn Vặt Khác</option>
            </select>
          </div>

          <BaseInput 
            v-model="form.price"
            type="number"
            label="Giá bán (VNĐ)"
            placeholder="85000"
            required
          />
        </div>

        <BaseInput 
          v-model="form.image_url"
          label="Link ảnh món (URL)"
          placeholder="https://images.unsplash.com/..."
        />

        <div class="space-y-1.5">
          <label class="block text-xs font-semibold text-cinema-muted">Mô tả thành phần</label>
          <textarea 
            v-model="form.description"
            rows="2"
            placeholder="1 bắp phô mai lớn + 2 nước ngọt có ga 32oz..."
            class="w-full bg-cinema-card/80 border border-cinema-border rounded-xl p-3 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-cinema-accent transition-colors"
          ></textarea>
        </div>

        <div class="flex items-center gap-2 pt-1">
          <input 
            type="checkbox" 
            id="is_available" 
            v-model="form.is_available"
            class="w-4 h-4 rounded text-cinema-accent bg-slate-900 border-cinema-border cursor-pointer"
          />
          <label for="is_available" class="text-xs text-slate-300 font-semibold cursor-pointer">
            Món này đang có sẵn để khách đặt (In-stock)
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
            {{ isEditing ? 'Lưu Thay Đổi' : 'Tạo Món Mới' }}
          </BaseButton>
        </div>
      </form>
    </BaseModal>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Popcorn, Plus, Search, Edit2, Trash2 } from 'lucide-vue-next';
import api from '../../services/api';
import BaseModal from '../../components/base/BaseModal.vue';
import BaseInput from '../../components/base/BaseInput.vue';
import BaseButton from '../../components/base/BaseButton.vue';
import { useToast } from '../../composables/useToast';

const toast = useToast();

const snacks = ref<any[]>([]);
const isLoading = ref(false);
const isSubmitting = ref(false);
const isModalOpen = ref(false);
const isEditing = ref(false);
const editingId = ref<number | null>(null);

const selectedCategory = ref('all');
const searchQuery = ref('');

const categories = [
  { label: 'Tất Cả Món', value: 'all' },
  { label: '🍿 Combo Bắp Nước', value: 'combo' },
  { label: '🌽 Bắp Rang Bơ', value: 'popcorn' },
  { label: '🥤 Nước Giải Khát', value: 'drink' },
  { label: '🍟 Snack & Khác', value: 'snack' },
];

const form = ref({
  name: '',
  category: 'combo',
  price: 85000,
  image_url: '',
  description: '',
  is_available: true,
});

const formatVnd = (val: number) => {
  if (!val) return '0 đ';
  return new Intl.NumberFormat('vi-VN').format(val) + ' đ';
};

const formatCategory = (cat: string) => {
  switch (cat) {
    case 'combo': return 'Combo Bắp Nước';
    case 'popcorn': return 'Bắp Rang';
    case 'drink': return 'Nước Ngọt';
    default: return 'Ăn Vặt';
  }
};

const getCategoryColor = (cat: string) => {
  switch (cat) {
    case 'combo': return 'bg-amber-500/20 text-amber-300 border-amber-500/30';
    case 'popcorn': return 'bg-orange-500/20 text-orange-300 border-orange-500/30';
    case 'drink': return 'bg-cyan-500/20 text-cyan-300 border-cyan-500/30';
    default: return 'bg-purple-500/20 text-purple-300 border-purple-500/30';
  }
};

const filteredSnacks = computed(() => {
  let list = snacks.value;
  if (selectedCategory.value !== 'all') {
    list = list.filter(s => s.category === selectedCategory.value);
  }
  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase();
    list = list.filter(s => s.name.toLowerCase().includes(q) || (s.description && s.description.toLowerCase().includes(q)));
  }
  return list;
});

const fetchSnacks = async () => {
  isLoading.value = true;
  try {
    const res = await api.get('/admin/snacks');
    if (res.data?.data) {
      snacks.value = res.data.data;
    }
  } catch (e) {
    console.warn('Error fetching snacks:', e);
  } finally {
    isLoading.value = false;
  }
};

const openCreateModal = () => {
  isEditing.value = false;
  editingId.value = null;
  form.value = {
    name: '',
    category: 'combo',
    price: 85000,
    image_url: 'https://images.unsplash.com/photo-1572177812156-58036aae439c?w=600',
    description: '',
    is_available: true,
  };
  isModalOpen.value = true;
};

const openEditModal = (snack: any) => {
  isEditing.value = true;
  editingId.value = snack.id;
  form.value = {
    name: snack.name,
    category: snack.category,
    price: snack.price,
    image_url: snack.image_url || '',
    description: snack.description || '',
    is_available: Boolean(snack.is_available),
  };
  isModalOpen.value = true;
};

const handleSubmit = async () => {
  isSubmitting.value = true;
  try {
    if (isEditing.value && editingId.value) {
      await api.put(`/admin/snacks/${editingId.value}`, form.value);
      toast.success(`Cập nhật món "${form.value.name}" thành công!`, 'Thành Công');
    } else {
      await api.post('/admin/snacks', form.value);
      toast.success(`Tạo mới món "${form.value.name}" thành công!`, 'Thành Công');
    }
    isModalOpen.value = false;
    await fetchSnacks();
  } catch (e: any) {
    toast.error(e.response?.data?.message || 'Có lỗi xảy ra khi lưu món ăn.', 'Lỗi Lưu Món');
  } finally {
    isSubmitting.value = false;
  }
};

const handleDelete = async (id: number) => {
  if (!confirm('Bạn có chắc chắn muốn xóa món này không?')) return;
  try {
    await api.delete(`/admin/snacks/${id}`);
    toast.success('Đã xóa món khỏi thực đơn!', 'Đã Xóa');
    await fetchSnacks();
  } catch (e: any) {
    toast.error(e.response?.data?.message || 'Có lỗi xảy ra khi xóa món.', 'Lỗi Xóa');
  }
};

onMounted(() => {
  fetchSnacks();
});
</script>
