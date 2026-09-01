<template>
  <div class="space-y-6">
    
    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-black text-white flex items-center gap-2.5">
          <Building2 class="w-6 h-6 text-cinema-accent" />
          <span>Quản Lý Hệ Thống Cụm Rạp Chiếu</span>
        </h1>
        <p class="text-xs text-cinema-muted mt-1">Thêm mới chi nhánh, cập nhật địa chỉ, hotline và quản lý các phòng chiếu trực thuộc</p>
      </div>

      <BaseButton 
        variant="primary" 
        size="md" 
        @click="openCreateModal"
      >
        <template #prefix><Plus class="w-4 h-4" /></template>
        <span>Thêm Cụm Rạp Mới</span>
      </BaseButton>
    </div>

    <!-- Search & Filter Bar -->
    <div class="p-4 rounded-3xl bg-cinema-surface/80 border border-cinema-border flex flex-col sm:flex-row gap-3 items-center justify-between backdrop-blur-xl shadow-xl">
      <div class="relative w-full sm:w-80">
        <Search class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
        <input 
          v-model="searchQuery"
          @input="handleSearch"
          type="text"
          placeholder="Tìm tên rạp, địa chỉ hoặc thành phố..."
          class="w-full bg-slate-900/90 border border-cinema-border rounded-xl pl-10 pr-4 py-2 text-xs text-white placeholder:text-slate-500 focus:outline-none focus:border-cinema-accent transition-colors"
        />
      </div>

      <div class="w-full sm:w-64">
        <BaseSelect 
          v-model="cityFilter"
          @update:model-value="onFilterChange"
        >
          <option v-for="opt in cityOptions" :key="opt.value" :value="opt.value">
            {{ opt.label }}
          </option>
        </BaseSelect>
      </div>
    </div>

    <!-- Cinemas Table -->
    <div class="p-6 rounded-3xl bg-cinema-surface/80 border border-cinema-border shadow-2xl overflow-hidden space-y-4 backdrop-blur-md">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs text-slate-300">
          <thead class="text-[11px] uppercase tracking-wider text-slate-400 border-b border-white/5 pb-3 bg-slate-900/60">
            <tr>
              <th class="py-3 px-4">Tên Cụm Rạp</th>
              <th class="py-3 px-4">Địa Chỉ Chi Tiết</th>
              <th class="py-3 px-4">Tỉnh / Thành Phố</th>
              <th class="py-3 px-4 text-center">Số Phòng Chiếu</th>
              <th class="py-3 px-4 text-right">Thao Tác</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-white/5 font-medium">
            <!-- Skeleton Loading -->
            <tr v-if="isLoading" v-for="i in 5" :key="i" class="animate-pulse">
              <td colspan="5" class="p-4"><div class="h-10 bg-white/5 rounded-xl w-full"></div></td>
            </tr>

            <!-- Empty State -->
            <tr v-else-if="cinemas.length === 0">
              <td colspan="5" class="p-12 text-center text-slate-500 space-y-2">
                <Building2 class="w-8 h-8 mx-auto text-slate-600" />
                <p>Không tìm thấy cụm rạp nào phù hợp với bộ lọc</p>
              </td>
            </tr>

            <!-- Cinemas List -->
            <tr 
              v-else
              v-for="cinema in cinemas" 
              :key="cinema.id"
              class="hover:bg-white/5 transition-colors group"
            >
              <td class="py-3.5 px-4 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-rose-500/20 to-amber-500/20 border border-white/10 flex items-center justify-center shrink-0 shadow-sm text-cinema-accent font-bold">
                  <Film class="w-5 h-5" />
                </div>
                <div>
                  <h4 class="font-extrabold text-white text-sm leading-snug group-hover:text-amber-400 transition-colors">{{ cinema.name }}</h4>
                  <span class="text-[11px] text-emerald-400 font-mono">Đang hoạt động</span>
                </div>
              </td>
              <td class="py-3.5 px-4 text-slate-300 max-w-xs">
                <div class="flex items-center gap-1.5 text-xs text-slate-400">
                  <MapPin class="w-3.5 h-3.5 text-rose-400 shrink-0" />
                  <span class="truncate">{{ cinema.address }}</span>
                </div>
              </td>
              <td class="py-3.5 px-4 font-bold text-white">
                <BaseBadge variant="purple" size="xs">
                  {{ cinema.city }}
                </BaseBadge>
              </td>
              <td class="py-3.5 px-4 text-center">
                <router-link 
                  to="/admin/rooms" 
                  class="inline-flex items-center gap-1 text-xs font-bold text-cinema-accent hover:underline bg-cinema-accent/10 px-2.5 py-1 rounded-xl border border-cinema-accent/20"
                >
                  <span>{{ cinema.rooms_count || (cinema.rooms ? cinema.rooms.length : 2) }} phòng</span>
                  <ExternalLink class="w-3 h-3" />
                </router-link>
              </td>
              <td class="py-3.5 px-4 text-right">
                <div class="flex items-center justify-end gap-2">
                  <button 
                    @click="openEditModal(cinema)"
                    class="p-2 rounded-xl bg-white/5 hover:bg-white/15 text-slate-300 hover:text-white transition-colors cursor-pointer"
                    title="Chỉnh sửa thông tin rạp"
                  >
                    <Edit class="w-3.5 h-3.5" />
                  </button>
                  <button 
                    @click="handleDelete(cinema.id)"
                    class="p-2 rounded-xl bg-rose-500/10 hover:bg-rose-500/25 text-rose-400 transition-colors cursor-pointer"
                    title="Xóa cụm rạp"
                  >
                    <Trash2 class="w-3.5 h-3.5" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Reusable Base Pagination -->
      <BasePagination 
        v-model:currentPage="currentPage"
        :totalPages="totalPages"
        :totalItems="totalCinemas"
        @change="changePage"
      />
    </div>

    <!-- Create / Edit Cinema Modal -->
    <BaseModal 
      v-model="isModalOpen" 
      :title="isEditing ? 'Chỉnh Sửa Thông Tin Cụm Rạp' : 'Thêm Cụm Rạp Chiếu Mới'"
      maxWidth="2xl"
    >
      <form @submit.prevent="handleSubmit" class="space-y-4 p-1">
        <BaseInput 
          v-model="form.name"
          label="Tên Cụm Rạp Chiếu *"
          placeholder="Ví dụ: CGV Vincom Landmark 81"
          required
        />

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <BaseSelect 
            v-model="form.city"
            label="Tỉnh / Thành Phố *"
            required
          >
            <option value="Hồ Chí Minh">Hồ Chí Minh</option>
            <option value="Hà Nội">Hà Nội</option>
            <option value="Đà Nẵng">Đà Nẵng</option>
            <option value="Cần Thơ">Cần Thơ</option>
            <option value="Hải Phòng">Hải Phòng</option>
            <option value="Bình Dương">Bình Dương</option>
            <option value="Nha Trang">Nha Trang</option>
          </BaseSelect>

          <BaseInput 
            v-if="!isEditing"
            v-model="form.default_rooms_count"
            type="number"
            label="Số Phòng Chiếu Mẫu Khởi Tạo *"
            placeholder="2"
            min="1"
            max="10"
            required
          />
        </div>

        <BaseInput 
          v-model="form.address"
          label="Địa Chỉ Chi Tiết *"
          placeholder="Ví dụ: Tầng B1 , TTTM Vincom Center Landmark 81, 772 Điện Biên Phủ, Q. Bình Thạnh"
          required
        />

        <div class="flex items-center justify-end gap-3 pt-3 border-t border-white/5">
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
            {{ isEditing ? 'Lưu Thay Đổi' : 'Tạo Cụm Rạp' }}
          </BaseButton>
        </div>
      </form>
    </BaseModal>

  </div>
</template>

<script setup lang="ts">
import { 
  Building2, 
  Film, 
  Plus, 
  Search, 
  Edit, 
  Trash2, 
  MapPin, 
  ExternalLink 
} from 'lucide-vue-next';
import { useAdminCinemas } from '../../composables/useAdminCinemas';
import BaseModal from '../../components/base/BaseModal.vue';
import BaseInput from '../../components/base/BaseInput.vue';
import BaseButton from '../../components/base/BaseButton.vue';
import BaseBadge from '../../components/base/BaseBadge.vue';
import BaseSelect from '../../components/base/BaseSelect.vue';
import BasePagination from '../../components/base/BasePagination.vue';

const {
  cinemas,
  totalCinemas,
  totalPages,
  currentPage,
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
  openCreateModal,
  openEditModal,
  handleSubmit,
  handleDelete,
} = useAdminCinemas();
</script>
