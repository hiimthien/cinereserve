<template>
  <div class="space-y-6">
    
    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-black text-white flex items-center gap-2.5">
          <Clapperboard class="w-6 h-6 text-cinema-accent" />
          <span>Quản Lý Danh Mục Phim</span>
        </h1>
        <p class="text-xs text-cinema-muted mt-1">Thêm mới, cấu hình suất chiếu sớm, poster, trailer và trạng thái phim</p>
      </div>

      <BaseButton 
        variant="primary" 
        size="md" 
        @click="openCreateModal"
      >
        <template #prefix><Plus class="w-4 h-4" /></template>
        <span>Thêm Phim Mới</span>
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
          placeholder="Tìm tên phim, đạo diễn hoặc diễn viên..."
          class="w-full bg-slate-900/90 border border-cinema-border rounded-xl pl-10 pr-4 py-2 text-xs text-white placeholder:text-slate-500 focus:outline-none focus:border-cinema-accent transition-colors"
        />
      </div>

      <div class="w-full sm:w-64">
        <BaseSelect 
          v-model="statusFilter"
          @update:model-value="onFilterChange"
        >
          <option value="all">Tất cả trạng thái phim</option>
          <option value="now_showing">🟢 Đang Chiếu (Now Showing)</option>
          <option value="early_premiere">✨ Suất Chiếu Sớm (Sneak Show)</option>
          <option value="coming_soon">⏳ Sắp Chiếu (Coming Soon)</option>
        </BaseSelect>
      </div>
    </div>

    <!-- Movies Table -->
    <div class="p-6 rounded-3xl bg-cinema-surface/80 border border-cinema-border shadow-2xl overflow-hidden space-y-4 backdrop-blur-md">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs text-slate-300">
          <thead class="text-[11px] uppercase tracking-wider text-slate-400 border-b border-white/5 pb-3 bg-slate-900/60">
            <tr>
              <th class="py-3 px-4">Poster & Tên Phim</th>
              <th class="py-3 px-4">Thời Lượng</th>
              <th class="py-3 px-4">Đánh Giá</th>
              <th class="py-3 px-4">Ngày Khởi Chiếu</th>
              <th class="py-3 px-4">Trạng Thái</th>
              <th class="py-3 px-4 text-right">Thao Tác</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-white/5 font-medium">
            <!-- Skeleton Loading -->
            <tr v-if="isLoading" v-for="i in 6" :key="i" class="animate-pulse">
              <td colspan="6" class="p-4"><div class="h-10 bg-white/5 rounded-xl w-full"></div></td>
            </tr>

            <!-- Empty State -->
            <tr v-else-if="movies.length === 0">
              <td colspan="6" class="p-12 text-center text-slate-500 space-y-2">
                <Film class="w-8 h-8 mx-auto text-slate-600" />
                <p>Không tìm thấy phim nào phù hợp với bộ lọc</p>
              </td>
            </tr>

            <!-- Movies List -->
            <tr 
              v-else
              v-for="movie in movies" 
              :key="movie.id"
              class="hover:bg-white/5 transition-colors group"
            >
              <td class="py-3.5 px-4 flex items-center gap-3">
                <img 
                  :src="movie.poster_url || 'https://images.unsplash.com/photo-1534447677768-be436bb09401?w=300'" 
                  class="w-10 h-14 object-cover rounded-lg border border-white/10 shrink-0 shadow-sm group-hover:scale-105 transition-transform"
                />
                <div>
                  <h4 class="font-extrabold text-white text-sm leading-snug group-hover:text-amber-400 transition-colors">{{ movie.title }}</h4>
                  <span class="text-[11px] text-slate-400">{{ movie.original_title || movie.slug }}</span>
                </div>
              </td>
              <td class="py-3.5 px-4 text-slate-300 font-mono">{{ movie.duration || movie.duration_minutes || 120 }} phút</td>
              <td class="py-3.5 px-4 text-amber-400 font-bold font-mono">★ {{ movie.rating || '8.5' }}</td>
              <td class="py-3.5 px-4 font-mono text-slate-300">{{ formatReleaseDate(movie.release_date) }}</td>
              <td class="py-3.5 px-4">
                <BaseBadge 
                  :variant="getBadgeVariant(movie.status)" 
                  size="xs"
                >
                  {{ formatStatus(movie.status) }}
                </BaseBadge>
              </td>
              <td class="py-3.5 px-4 text-right">
                <div class="flex items-center justify-end gap-2">
                  <button 
                    @click="openEditModal(movie)"
                    class="p-2 rounded-xl bg-white/5 hover:bg-white/15 text-slate-300 hover:text-white transition-colors cursor-pointer"
                    title="Chỉnh sửa phim"
                  >
                    <Edit class="w-3.5 h-3.5" />
                  </button>
                  <button 
                    @click="handleDelete(movie.id)"
                    class="p-2 rounded-xl bg-rose-500/10 hover:bg-rose-500/25 text-rose-400 transition-colors cursor-pointer"
                    title="Xóa phim"
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
        :totalItems="totalMovies"
        @change="changePage"
      />
    </div>

    <!-- Create / Edit Movie Modal -->
    <BaseModal 
      v-model="isModalOpen" 
      :title="isEditing ? 'Chỉnh Sửa Thông Tin Phim' : 'Thêm Phim Chiếu Rạp Mới'"
      maxWidth="3xl"
    >
      <form @submit.prevent="handleSubmit" class="space-y-4 p-1">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <BaseInput 
            v-model="form.title"
            label="Tên Phim (Tiếng Việt) *"
            placeholder="Ví dụ: Đất Rừng Phương Nam"
            required
          />
          <BaseInput 
            v-model="form.original_title"
            label="Tên Gốc / Tiếng Anh"
            placeholder="Ví dụ: Song of the South"
          />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <BaseSelect 
            v-model="form.status"
            label="Trạng Thái Chiếu *"
            required
          >
            <option value="now_showing">🟢 Đang Chiếu</option>
            <option value="early_premiere">✨ Suất Chiếu Sớm</option>
            <option value="coming_soon">⏳ Sắp Chiếu</option>
          </BaseSelect>

          <BaseSelect 
            v-model="form.age_rating"
            label="Nhãn Độ Tuổi *"
            required
          >
            <option value="P">🟢 P - Mọi lứa tuổi</option>
            <option value="K">🔵 K - Dưới 13 tuổi (kèm PH)</option>
            <option value="T13">🟡 T13 - Khán giả từ 13+</option>
            <option value="T16">🟠 T16 - Khán giả từ 16+</option>
            <option value="T18">🔴 T18 - Khán giả từ 18+</option>
          </BaseSelect>

          <BaseInput 
            v-model="form.duration"
            type="number"
            label="Thời Lượng (Phút) *"
            placeholder="120"
            required
          />

          <BaseInput 
            v-model="form.release_date"
            type="date"
            label="Ngày Khởi Chiếu *"
            required
          />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <BaseInput 
            v-model="form.poster_url"
            label="URL Poster Dọc *"
            placeholder="https://image.tmdb.org/t/p/w500/..."
            required
          />
          <BaseInput 
            v-model="form.trailer_url"
            label="URL Video Trailer (Youtube/MP4)"
            placeholder="https://www.youtube.com/watch?v=..."
          />
        </div>

        <div class="space-y-1.5">
          <label class="block text-xs font-semibold text-cinema-muted">Tóm Tắt Nội Dung Phim *</label>
          <textarea 
            v-model="form.description"
            rows="3"
            placeholder="Nhập nội dung tóm tắt cốt truyện của bộ phim..."
            class="w-full bg-cinema-card/80 border border-cinema-border rounded-xl p-3 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-cinema-accent transition-colors"
            required
          ></textarea>
        </div>

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
            {{ isEditing ? 'Lưu Thay Đổi' : 'Thêm Phim Mới' }}
          </BaseButton>
        </div>
      </form>
    </BaseModal>

  </div>
</template>

<script setup lang="ts">
import { 
  Clapperboard, 
  Film, 
  Plus, 
  Search, 
  Edit, 
  Trash2 
} from 'lucide-vue-next';
import { useAdminMovies } from '../../composables/useAdminMovies';
import BaseModal from '../../components/base/BaseModal.vue';
import BaseInput from '../../components/base/BaseInput.vue';
import BaseButton from '../../components/base/BaseButton.vue';
import BaseBadge from '../../components/base/BaseBadge.vue';
import BaseSelect from '../../components/base/BaseSelect.vue';
import BasePagination from '../../components/base/BasePagination.vue';

const {
  movies,
  totalMovies,
  totalPages,
  currentPage,
  statusFilter,
  searchQuery,
  isLoading,
  isModalOpen,
  isEditing,
  isSubmitting,
  form,
  formatReleaseDate,
  formatStatus,
  getBadgeVariant,
  handleSearch,
  onFilterChange,
  changePage,
  openCreateModal,
  openEditModal,
  handleSubmit,
  handleDelete,
} = useAdminMovies();
</script>
