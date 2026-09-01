<template>
  <div class="space-y-6">
    
    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-black text-white flex items-center gap-2.5">
          <Calendar class="w-6 h-6 text-cinema-accent" />
          <span>Quản Lý Lịch & Suất Chiếu Theo Phim</span>
        </h1>
        <p class="text-xs text-cinema-muted mt-1">
          Lên lịch chiếu theo từng phim, hỗ trợ tạo hàng loạt nhiều rạp cùng lúc, thiết lập suất chiếu sớm và cấu hình giá vé riêng từng loại ghế
        </p>
      </div>

      <BaseButton 
        variant="primary" 
        size="md" 
        @click="openCreateModal()"
      >
        <template #prefix><Plus class="w-4 h-4" /></template>
        <span>Tạo Suất Chiếu Mới</span>
      </BaseButton>
    </div>

    <!-- Filter & Search Toolbar with Status Tabs -->
    <div class="p-4 rounded-3xl bg-cinema-surface/80 border border-cinema-border space-y-3 backdrop-blur-xl shadow-xl">
      <div class="flex flex-wrap items-center justify-between gap-2 border-b border-white/5 pb-3">
        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 sm:pb-0 scrollbar-none">
          <button
            v-for="tab in movieStatusTabs"
            :key="tab.value"
            @click="movieStatusFilter = tab.value"
            class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer whitespace-nowrap"
            :class="[
              movieStatusFilter === tab.value
                ? 'bg-cinema-accent text-white shadow-glow-accent'
                : 'bg-white/5 text-slate-400 hover:text-white'
            ]"
          >
            {{ tab.label }}
          </button>
        </div>

        <span class="text-xs text-cinema-muted">
          Tìm thấy <strong class="text-white">{{ filteredMovies.length }}</strong> bộ phim
        </span>
      </div>

      <!-- Search Box -->
      <div class="relative">
        <Search class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
        <input 
          v-model="searchQuery"
          type="text"
          placeholder="Tìm phim theo tên, đạo diễn, diễn viên..."
          class="w-full bg-slate-900/90 border border-cinema-border rounded-xl pl-10 pr-4 py-2 text-xs text-white placeholder:text-slate-500 focus:outline-none focus:border-cinema-accent transition-colors"
        />
      </div>
    </div>

    <!-- Movies Showtimes Grid (Grouped by Movie) -->
    <div v-if="isLoading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div v-for="i in 6" :key="i" class="p-6 rounded-3xl bg-cinema-surface/50 border border-white/5 animate-pulse space-y-4">
        <div class="h-40 bg-white/5 rounded-2xl w-full"></div>
        <div class="h-6 bg-white/10 rounded-lg w-2/3"></div>
      </div>
    </div>

    <div v-else-if="filteredMovies.length === 0" class="p-16 text-center bg-cinema-surface/30 border border-cinema-border rounded-3xl space-y-3 shadow-xl">
      <Film class="w-12 h-12 mx-auto text-slate-500" />
      <h3 class="text-base font-bold text-white">Không tìm thấy bộ phim nào</h3>
      <p class="text-xs text-cinema-muted">Thử tìm kiếm với từ khóa khác hoặc chuyển sang tab trạng thái khác.</p>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
      <MovieShowtimeCard 
        v-for="movie in paginatedMovies"
        :key="movie.id"
        :movie="movie"
        :showtimesCount="getMovieShowtimesCount(movie.id)"
        :sampleShowtimes="getMovieSampleShowtimes(movie.id)"
        @view-detail="openDetailModal"
        @add-showtime="openCreateModal"
      />
    </div>

    <!-- Pagination for Movies Grid -->
    <BasePagination 
      v-model:currentPage="currentMoviePage"
      :totalPages="totalMoviePages"
      :totalItems="filteredMovies.length"
    />

    <!-- 🎬 Modal Chi Tiết Lịch Chiếu Phim -->
    <MovieShowtimeDetailModal 
      v-model="isDetailModalOpen"
      :movie="selectedMovieDetail"
      :showtimes="movieDetailShowtimes"
      @add-showtime="openCreateModal"
      @edit-showtime="openEditShowtimeModal"
      @delete-showtime="handleDelete"
    />

    <!-- 🎟️ Modal Tạo Suất Chiếu (Single / Batch) -->
    <ShowtimeCreateModal 
      v-model="isModalOpen"
      v-model:creationMode="creationMode"
      :form="form"
      :batchForm="batchForm"
      :moviesList="moviesList"
      :cinemasList="cinemasList"
      :availableRooms="availableRooms"
      :availableTimeSlots="availableTimeSlots"
      :selectedCinemaId="selectedCinemaId"
      :isSubmitting="isSubmitting"
      @movie-select-change="onMovieSelectChange"
      @cinema-change="onCinemaChange"
      @toggle-select-all-cinemas="toggleSelectAllCinemas"
      @base-price-change="onBasePriceChange"
      @submit-single="handleSubmitSingle"
      @submit-batch="handleSubmitBatch"
    />

    <!-- ✏️ Modal Chỉnh Sửa Suất Chiếu -->
    <ShowtimeEditModal 
      v-model="isEditModalOpen"
      :editForm="editForm"
      :isSubmitting="isSubmitting"
      @base-price-change="onEditBasePriceChange"
      @submit="handleUpdateShowtime"
    />

  </div>
</template>

<script setup lang="ts">
import { Calendar, Film, Plus, Search } from 'lucide-vue-next';
import { useAdminShowtimes } from '../../composables/useAdminShowtimes';
import BaseButton from '../../components/base/BaseButton.vue';
import BasePagination from '../../components/base/BasePagination.vue';
import MovieShowtimeCard from '../../components/admin/showtimes/MovieShowtimeCard.vue';
import MovieShowtimeDetailModal from '../../components/admin/showtimes/MovieShowtimeDetailModal.vue';
import ShowtimeCreateModal from '../../components/admin/showtimes/ShowtimeCreateModal.vue';
import ShowtimeEditModal from '../../components/admin/showtimes/ShowtimeEditModal.vue';

const {
  moviesList,
  cinemasList,
  isLoading,
  isSubmitting,
  searchQuery,
  movieStatusFilter,
  currentMoviePage,
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
  editForm,
  filteredMovies,
  totalMoviePages,
  paginatedMovies,
  movieDetailShowtimes,
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
  handleSubmitSingle,
  handleSubmitBatch,
  handleUpdateShowtime,
  handleDelete,
} = useAdminShowtimes();
</script>
