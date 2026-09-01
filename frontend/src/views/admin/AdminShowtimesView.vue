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

      <div class="flex items-center gap-2">
        <BaseButton 
          variant="primary" 
          size="md" 
          @click="openCreateModal()"
        >
          <template #prefix><Plus class="w-4 h-4" /></template>
          <span>Tạo Suất Chiếu Mới</span>
        </BaseButton>
      </div>
    </div>

    <!-- Filter & Search Toolbar with Status Tabs -->
    <div class="p-4 rounded-3xl bg-cinema-surface/80 border border-cinema-border space-y-3 backdrop-blur-xl shadow-xl">
      
      <!-- Top Row: Status Tabs -->
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

      <!-- Bottom Row: Search Box -->
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
      <div 
        v-for="movie in paginatedMovies" 
        :key="movie.id"
        class="bg-cinema-surface/80 border border-cinema-border rounded-3xl overflow-hidden shadow-xl backdrop-blur-md flex flex-col justify-between hover:border-cinema-accent/40 transition-all group"
      >
        <!-- Top Poster & Banner Info -->
        <div class="p-5 flex gap-4 items-start border-b border-white/5 bg-gradient-to-b from-white/5 to-transparent">
          <img 
            :src="movie.poster_url || 'https://images.unsplash.com/photo-1534447677768-be436bb09401?w=400'" 
            class="w-20 h-28 object-cover rounded-2xl border border-white/10 shadow-lg shrink-0 group-hover:scale-105 transition-transform"
          />

          <div class="space-y-1.5 flex-1 min-w-0">
            <div class="flex items-center gap-1.5">
              <BaseBadge :variant="getBadgeVariant(movie.status)" size="xs">
                {{ formatStatus(movie.status) }}
              </BaseBadge>
              <span class="text-[11px] text-amber-400 font-mono font-bold">★ {{ movie.rating || 8.5 }}</span>
            </div>

            <h3 class="font-extrabold text-white text-base leading-snug line-clamp-1 group-hover:text-amber-400 transition-colors">
              {{ movie.title }}
            </h3>

            <p class="text-xs text-cinema-muted line-clamp-1">
              {{ movie.duration || 120 }} phút • Khởi chiếu: {{ formatDate(movie.release_date) }}
            </p>

            <div class="pt-1 flex items-center gap-2 text-[11px] text-slate-400">
              <span class="px-2 py-0.5 rounded-md bg-white/5 border border-white/5 font-semibold">
                {{ getMovieShowtimesCount(movie.id) }} suất chiếu
              </span>
            </div>
          </div>
        </div>

        <!-- Quick Summary of Next Showtimes -->
        <div class="p-5 space-y-3 flex-1 flex flex-col justify-between">
          <div class="space-y-2">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Các suất chiếu gần nhất:</span>
            
            <div v-if="getMovieSampleShowtimes(movie.id).length > 0" class="flex flex-wrap gap-1.5">
              <span 
                v-for="st in getMovieSampleShowtimes(movie.id)" 
                :key="st.id"
                class="px-2.5 py-1 rounded-xl text-xs font-mono font-bold border transition-all"
                :class="st.status === 'early_premiere' ? 'bg-purple-500/20 text-purple-300 border-purple-500/40' : 'bg-slate-900/90 text-slate-300 border-cinema-border'"
              >
                {{ st.start_time }} ({{ formatDateShort(st.show_date) }})
              </span>
            </div>

            <div v-else class="text-xs text-slate-500 italic py-1">
              {{ movie.status === 'coming_soon' ? 'Phim sắp chiếu. Bấm "Thêm Suất" để tạo lịch tự động từ ngày khởi chiếu!' : 'Chưa có lịch chiếu nào được tạo cho phim này.' }}
            </div>
          </div>

          <!-- Bottom Action Buttons -->
          <div class="pt-3 border-t border-white/5 flex items-center justify-between gap-2">
            <BaseButton 
              variant="secondary" 
              size="sm"
              @click="openDetailModal(movie)"
            >
              <template #prefix><Eye class="w-3.5 h-3.5" /></template>
              <span>Xem Lịch Chiếu</span>
            </BaseButton>

            <BaseButton 
              variant="primary" 
              size="sm"
              @click="openCreateModal(movie)"
            >
              <template #prefix><Plus class="w-3.5 h-3.5" /></template>
              <span>Thêm Suất</span>
            </BaseButton>
          </div>
        </div>
      </div>
    </div>

    <!-- Pagination for Movies Grid -->
    <BasePagination 
      v-model:currentPage="currentMoviePage"
      :totalPages="totalMoviePages"
      :totalItems="filteredMovies.length"
    />

    <!-- 🎬 Modal Chi Tiết Toàn Bộ Lịch Chiếu Của 1 Phim -->
    <BaseModal 
      v-model="isDetailModalOpen" 
      :title="`Lịch Chiếu Phim: ${selectedMovieDetail?.title || ''}`"
      maxWidth="4xl"
    >
      <div v-if="selectedMovieDetail" class="space-y-5 p-1">
        <!-- Movie Summary Banner -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between p-4 rounded-2xl bg-slate-900/90 border border-white/10 gap-4">
          <div class="flex items-center gap-3 min-w-0">
            <img :src="selectedMovieDetail.poster_url" class="w-12 h-16 object-cover rounded-xl border border-white/10 shrink-0" />
            <div class="min-w-0">
              <h4 class="font-extrabold text-white text-base leading-tight truncate">{{ selectedMovieDetail.title }}</h4>
              <p class="text-xs text-cinema-muted">{{ selectedMovieDetail.duration }} phút • Khởi chiếu: {{ formatDate(selectedMovieDetail.release_date) }}</p>
            </div>
          </div>

          <div class="flex items-center gap-2 shrink-0">
            <BaseButton 
              variant="primary" 
              size="sm"
              @click="openCreateModal(selectedMovieDetail)"
            >
              <template #prefix><Plus class="w-3.5 h-3.5" /></template>
              <span>Thêm Suất Mới</span>
            </BaseButton>
          </div>
        </div>

        <!-- Showtimes List Table -->
        <div class="max-h-[550px] overflow-y-auto rounded-2xl border border-white/5 bg-slate-900/50">
          <table class="w-full text-left text-xs text-slate-300">
            <thead class="sticky top-0 bg-slate-950 text-[11px] uppercase tracking-wider text-slate-400 border-b border-white/10 shadow-md">
              <tr>
                <th class="p-3.5">Cụm Rạp & Phòng</th>
                <th class="p-3.5">Ngày & Giờ Chiếu</th>
                <th class="p-3.5">Định Dạng</th>
                <th class="p-3.5">Loại Suất</th>
                <th class="p-3.5">Giá Vé (Thường / VIP / Đôi)</th>
                <th class="p-3.5 text-right">Thao Tác</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-white/5 font-medium">
              <tr v-if="movieDetailShowtimes.length === 0">
                <td colspan="6" class="p-8 text-center text-slate-500 italic space-y-3">
                  <p>Chưa có suất chiếu nào được tạo cho phim này.</p>
                  <BaseButton 
                    variant="primary" 
                    size="sm"
                    @click="openCreateModal(selectedMovieDetail)"
                  >
                    <template #prefix><Plus class="w-3.5 h-3.5" /></template>
                    <span>Tạo Lịch Chiếu Ngay</span>
                  </BaseButton>
                </td>
              </tr>
              <tr 
                v-else
                v-for="st in movieDetailShowtimes" 
                :key="st.id"
                class="hover:bg-white/5 transition-colors"
              >
                <td class="p-3.5">
                  <strong class="text-white block text-sm">{{ st.cinema?.name || 'Cụm rạp' }}</strong>
                  <span class="text-xs text-slate-400">{{ st.room?.name || 'Phòng 1' }} ({{ st.room?.room_type || '2D' }})</span>
                </td>
                <td class="p-3.5">
                  <span class="text-amber-400 font-mono font-bold text-sm block">{{ st.start_time }}</span>
                  <span class="text-xs text-slate-400 font-mono">{{ formatDate(st.show_date) }}</span>
                </td>
                <td class="p-3.5">
                  <BaseBadge variant="rose" size="xs">{{ st.format || '2D' }}</BaseBadge>
                </td>
                <td class="p-3.5">
                  <BaseBadge :variant="st.status === 'early_premiere' ? 'purple' : 'emerald'" size="xs">
                    {{ st.status === 'early_premiere' ? '✨ Chiếu Sớm' : '🟢 Tiêu Chuẩn' }}
                  </BaseBadge>
                </td>
                <td class="p-3.5 font-mono text-xs space-y-0.5">
                  <div class="text-emerald-400 font-bold">💺 {{ formatVnd(st.base_price) }}</div>
                  <div class="text-amber-300">👑 {{ formatVnd(st.price_vip || (st.base_price + 15000)) }}</div>
                  <div class="text-rose-300">💖 {{ formatVnd(st.price_couple || (st.base_price * 2)) }}</div>
                </td>
                <td class="p-3.5 text-right space-x-2">

                  <button 
                    @click="openEditShowtimeModal(st)"
                    class="p-2 rounded-xl bg-white/5 hover:bg-white/15 text-slate-300 hover:text-white transition-colors cursor-pointer"
                    title="Chỉnh sửa suất chiếu này"
                  >
                    <Edit2 class="w-4 h-4" />
                  </button>
                  <button 
                    @click="handleDelete(st.id)"
                    class="p-2 rounded-xl bg-rose-500/10 hover:bg-rose-500/25 text-rose-400 transition-colors cursor-pointer"
                    title="Xóa suất chiếu này"
                  >
                    <Trash2 class="w-4 h-4" />
                  </button>
                </td>


              </tr>
            </tbody>
          </table>
        </div>

        <div class="flex justify-end pt-2">
          <BaseButton variant="secondary" size="md" @click="isDetailModalOpen = false">
            Đóng
          </BaseButton>
        </div>
      </div>
    </BaseModal>

    <!-- 🎟️ Modal Tạo Suất Chiếu Mới (Layout 2 Cột Thông Minh & Sticky Footer) -->
    <BaseModal 
      v-model="isModalOpen" 
      :title="creationMode === 'single' ? 'Tạo 1 Suất Chiếu Đơn' : '⚡ Tạo Suất Chiếu Hàng Loạt (Nhiều Rạp & Nhiều Ngày)'"
      maxWidth="4xl"
      :zIndex="60"
    >

      <div class="space-y-4">
        
        <!-- Mode Tabs -->
        <div class="flex rounded-2xl bg-slate-950 p-1.5 border border-white/10">
          <button 
            @click="creationMode = 'single'"
            class="flex-1 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer text-center"
            :class="creationMode === 'single' ? 'bg-cinema-accent text-white shadow-glow-accent' : 'text-slate-400 hover:text-white'"
          >
            Tạo 1 Suất Đơn
          </button>
          <button 
            @click="creationMode = 'batch'"
            class="flex-1 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer text-center"
            :class="creationMode === 'batch' ? 'bg-cinema-accent text-white shadow-glow-accent' : 'text-slate-400 hover:text-white'"
          >
            ⚡ Tạo Hàng Loạt (Nhiều Rạp & Nhiều Khung Giờ)
          </button>
        </div>

        <!-- 2-Column Responsive Form Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
          
          <!-- LEFT COLUMN: Movie, Cinemas, Dates -->
          <div class="space-y-4">
            <!-- 1. Chọn Phim -->
            <BaseSelect 
              v-model="form.movie_id"
              label="Chọn Phim Chiếu *"
              required
              @update:model-value="onMovieSelectChange"
            >
              <option value="" disabled>-- Chọn bộ phim --</option>
              <option v-for="m in moviesList" :key="m.id" :value="m.id">
                {{ m.title }} ({{ m.duration || 120 }}p) - {{ formatStatus(m.status) }}
              </option>
            </BaseSelect>

            <!-- SINGLE MODE: 1 Cinema & 1 Room & Date/Time -->
            <div v-if="creationMode === 'single'" class="space-y-3.5">
              <div class="grid grid-cols-2 gap-3">
                <BaseSelect 
                  v-model="selectedCinemaId"
                  label="Cụm Rạp Chiếu *"
                  required
                  @update:model-value="onCinemaChange"
                >
                  <option value="" disabled>-- Chọn cụm rạp --</option>
                  <option v-for="c in cinemasList" :key="c.id" :value="c.id">
                    {{ c.name }}
                  </option>
                </BaseSelect>

                <BaseSelect 
                  v-model="form.room_id"
                  label="Phòng Chiếu *"
                  required
                >
                  <option value="" disabled>-- Chọn phòng chiếu --</option>
                  <option v-for="r in availableRooms" :key="r.id" :value="r.id">
                    {{ r.name }} ({{ r.room_type || '2D' }})
                  </option>
                </BaseSelect>
              </div>

              <div class="grid grid-cols-2 gap-3">
                <BaseInput 
                  v-model="form.show_date"
                  type="date"
                  label="Ngày Chiếu *"
                  required
                />
                <BaseInput 
                  v-model="form.start_time"
                  type="time"
                  label="Giờ Bắt Đầu *"
                  required
                />
              </div>
            </div>

            <!-- BATCH MODE: Multi Cinemas & Date Range -->
            <div v-else class="space-y-3.5">
              <!-- Multi Cinema Selection Box -->
              <div class="space-y-1.5">
                <div class="flex items-center justify-between">
                  <label class="block text-xs font-semibold text-cinema-muted">
                    Chọn Cụm Rạp Chiếu ({{ batchForm.cinema_ids.length }}/{{ cinemasList.length }}) *
                  </label>
                  <button 
                    type="button" 
                    @click="toggleSelectAllCinemas"
                    class="text-[11px] text-cinema-accent hover:underline font-bold cursor-pointer"
                  >
                    {{ batchForm.cinema_ids.length === cinemasList.length ? 'Bỏ chọn' : 'Chọn tất cả' }}
                  </button>
                </div>

                <div class="max-h-44 overflow-y-auto p-3 rounded-2xl bg-slate-900/90 border border-cinema-border grid grid-cols-1 sm:grid-cols-2 gap-2 scrollbar-thin">
                  <label 
                    v-for="c in cinemasList" 
                    :key="c.id"
                    class="flex items-center gap-2 text-xs text-slate-300 hover:text-white cursor-pointer"
                  >
                    <input 
                      type="checkbox" 
                      :value="c.id" 
                      v-model="batchForm.cinema_ids"
                      class="rounded text-cinema-accent bg-slate-950 border-cinema-border cursor-pointer"
                    />
                    <span class="truncate">{{ c.name }}</span>
                  </label>
                </div>
              </div>

              <!-- Date Range & Days Count -->
              <div class="grid grid-cols-2 gap-3">
                <BaseInput 
                  v-model="batchForm.start_date"
                  type="date"
                  label="Từ Ngày Khởi Chiếu *"
                  required
                />

                <BaseSelect 
                  v-model="batchForm.days_count"
                  label="Số Ngày Chiếu *"
                  required
                >
                  <option :value="3">3 Ngày</option>
                  <option :value="7">7 Ngày (1 Tuần)</option>
                  <option :value="14">14 Ngày (2 Tuần)</option>
                </BaseSelect>
              </div>
            </div>
          </div>

          <!-- RIGHT COLUMN: Timeslots, Format, Status, Pricing -->
          <div class="space-y-4">
            <!-- If Batch Mode: Multiple Time Slots -->
            <div v-if="creationMode === 'batch'" class="space-y-1.5">
              <label class="block text-xs font-semibold text-cinema-muted">Khung Giờ Chiếu Trong Ngày *</label>
              <div class="grid grid-cols-3 sm:grid-cols-4 gap-1.5">
                <label 
                  v-for="slot in availableTimeSlots" 
                  :key="slot"
                  class="p-2 rounded-xl border flex items-center justify-between text-xs cursor-pointer transition-all font-mono font-bold"
                  :class="batchForm.time_slots.includes(slot) ? 'bg-cinema-accent/20 border-cinema-accent text-white shadow-glow-accent' : 'bg-slate-900/90 border-cinema-border text-slate-400'"
                >
                  <span>{{ slot }}</span>
                  <input 
                    type="checkbox" 
                    :value="slot" 
                    v-model="batchForm.time_slots"
                    class="hidden"
                  />
                </label>
              </div>
            </div>

            <!-- Format & Status (Shared) -->
            <div class="grid grid-cols-2 gap-3">
              <BaseSelect 
                v-model="form.format"
                label="Định Dạng Chiếu *"
                required
              >
                <option value="2D Standard">2D Standard</option>
                <option value="3D">3D RealD</option>
                <option value="IMAX Laser">IMAX Laser</option>
                <option value="4DX">4DX Motion</option>
              </BaseSelect>

              <BaseSelect 
                v-model="form.status"
                label="Loại Suất Chiếu *"
                required
              >
                <option value="scheduled">🟢 Suất Thường</option>
                <option value="early_premiere">✨ Suất Chiếu Sớm</option>
              </BaseSelect>
            </div>

            <!-- 💺 Tùy Chỉnh Giá Chi Tiết Từng Loại Ghế -->
            <div class="p-3.5 rounded-2xl bg-slate-900/90 border border-cinema-border space-y-3">
              <span class="text-xs font-bold text-white block">💺 Cấu Hình Bảng Giá Riêng Từng Loại Ghế (VNĐ):</span>
              
              <div class="grid grid-cols-3 gap-2.5">
                <BaseInput 
                  v-model="form.base_price"
                  type="number"
                  label="Ghế Thường *"
                  placeholder="95000"
                  required
                  @input="onBasePriceChange"
                />

                <BaseInput 
                  v-model="form.price_vip"
                  type="number"
                  label="Ghế VIP *"
                  placeholder="115000"
                  required
                />

                <BaseInput 
                  v-model="form.price_couple"
                  type="number"
                  label="Ghế Đôi *"
                  placeholder="200000"
                  required
                />
              </div>
            </div>
          </div>

        </div>

      </div>

      <!-- Sticky Bottom Footer with Action Buttons -->
      <template #footer>
        <div class="flex items-center justify-between w-full">
          <span class="text-xs text-cinema-muted">
            {{ creationMode === 'single' ? 'Tạo 1 suất chiếu nhanh' : `Tạo lịch đồng loạt cho ${batchForm.cinema_ids.length} rạp` }}
          </span>

          <div class="flex items-center gap-3">
            <BaseButton 
              type="button" 
              variant="secondary" 
              size="md"
              @click="isModalOpen = false"
            >
              Hủy Bỏ
            </BaseButton>
            <BaseButton 
              type="button" 
              variant="primary" 
              size="md"
              :loading="isSubmitting"
              @click="creationMode === 'single' ? handleSubmitSingle() : handleSubmitBatch()"
            >
              {{ creationMode === 'single' ? 'Tạo Suất Chiếu' : `Tạo Hàng Loạt (${batchForm.cinema_ids.length} Rạp)` }}
            </BaseButton>
          </div>
        </div>
      </template>
    </BaseModal>

    <!-- ✏️ Modal Chỉnh Sửa Suất Chiếu & Bảng Giá Ghế -->
    <BaseModal 
      v-model="isEditModalOpen" 
      title="Chỉnh Sửa Suất Chiếu & Bảng Giá Ghế"
      maxWidth="2xl"
      :zIndex="60"
    >
      <form @submit.prevent="handleUpdateShowtime" class="space-y-4 p-1">
        <div class="grid grid-cols-2 gap-3">
          <BaseInput 
            v-model="editForm.show_date"
            type="date"
            label="Ngày Chiếu *"
            required
          />
          <BaseInput 
            v-model="editForm.start_time"
            type="time"
            label="Giờ Bắt Đầu *"
            required
          />
        </div>

        <div class="grid grid-cols-2 gap-3">
          <BaseSelect 
            v-model="editForm.format"
            label="Định Dạng *"
            required
          >
            <option value="2D Standard">2D Standard</option>
            <option value="3D">3D RealD</option>
            <option value="IMAX Laser">IMAX Laser</option>
            <option value="4DX">4DX Motion</option>
          </BaseSelect>

          <BaseSelect 
            v-model="editForm.status"
            label="Loại Suất Chiếu *"
            required
          >
            <option value="scheduled">🟢 Suất Chiếu Thường</option>
            <option value="early_premiere">✨ Suất Chiếu Sớm (Sneak Show)</option>
            <option value="cancelled">❌ Đã Hủy</option>
          </BaseSelect>
        </div>

        <!-- Custom Price Inputs for Edit -->
        <div class="p-3.5 rounded-2xl bg-slate-900/90 border border-cinema-border space-y-3">
          <span class="text-xs font-bold text-white block">💺 Cập Nhật Giá Từng Loại Ghế:</span>
          
          <div class="grid grid-cols-3 gap-2">
            <BaseInput 
              v-model="editForm.base_price"
              type="number"
              label="Ghế Thường *"
              required
              @input="onEditBasePriceChange"
            />
            <BaseInput 
              v-model="editForm.price_vip"
              type="number"
              label="Ghế VIP *"
              required
            />
            <BaseInput 
              v-model="editForm.price_couple"
              type="number"
              label="Ghế Đôi *"
              required
            />
          </div>
        </div>
      </form>

      <template #footer>
        <div class="flex items-center justify-end gap-3 w-full">
          <BaseButton 
            type="button" 
            variant="secondary" 
            @click="isEditModalOpen = false"
          >
            Hủy Bỏ
          </BaseButton>
          <BaseButton 
            type="button" 
            variant="primary" 
            :loading="isSubmitting"
            @click="handleUpdateShowtime"
          >
            Lưu Thay Đổi
          </BaseButton>
        </div>
      </template>
    </BaseModal>

  </div>
</template>


<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Calendar, Film, Plus, Search, Eye, Trash2, Edit2 } from 'lucide-vue-next';
import api from '../../services/api';
import BaseModal from '../../components/base/BaseModal.vue';
import BaseInput from '../../components/base/BaseInput.vue';
import BaseButton from '../../components/base/BaseButton.vue';
import BaseBadge from '../../components/base/BaseBadge.vue';
import BaseSelect from '../../components/base/BaseSelect.vue';
import BasePagination from '../../components/base/BasePagination.vue';

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
    if (cinemasRes.data?.data) {
      cinemasList.value = cinemasRes.data.data;
    }
    if (showtimesRes.data?.data) {
      allShowtimes.value = showtimesRes.data.data;
    }
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
    isModalOpen.value = false;
    await loadData();
  } catch (e: any) {
    alert(e.response?.data?.message || 'Có lỗi xảy ra khi tạo suất chiếu.');
  } finally {
    isSubmitting.value = false;
  }
};

const handleSubmitBatch = async () => {
  if (batchForm.value.cinema_ids.length === 0) {
    alert('Vui lòng chọn ít nhất 1 cụm rạp để tạo lịch chiếu!');
    return;
  }
  if (batchForm.value.time_slots.length === 0) {
    alert('Vui lòng chọn ít nhất 1 khung giờ chiếu!');
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
    alert(res.data?.message || 'Đã tạo hàng loạt suất chiếu thành công!');
    isModalOpen.value = false;
    await loadData();
  } catch (e: any) {
    alert(e.response?.data?.message || 'Có lỗi xảy ra khi tạo hàng loạt suất chiếu.');
  } finally {
    isSubmitting.value = false;
  }
};

const handleUpdateShowtime = async () => {
  if (!editingShowtimeId.value) return;
  isSubmitting.value = true;
  try {
    await api.put(`/admin/showtimes/${editingShowtimeId.value}`, editForm.value);
    isEditModalOpen.value = false;
    await loadData();
  } catch (e: any) {
    alert(e.response?.data?.message || 'Không thể cập nhật suất chiếu.');
  } finally {
    isSubmitting.value = false;
  }
};

const handleDelete = async (id: number) => {
  if (!confirm('Bạn có chắc chắn muốn xóa suất chiếu này không?')) return;
  try {
    await api.delete(`/admin/showtimes/${id}`);
    await loadData();
  } catch (e: any) {
    alert(e.response?.data?.message || 'Không thể xóa suất chiếu.');
  }
};

onMounted(() => {
  loadData();
});
</script>
