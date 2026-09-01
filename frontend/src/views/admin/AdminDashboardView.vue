<template>
  <div class="space-y-6">
    
    <!-- 🎛️ Dashboard Header & Filter Toolbar -->
    <div class="bg-cinema-surface/80 border border-cinema-border rounded-3xl p-5 backdrop-blur-xl shadow-xl space-y-4">
      
      <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl font-black text-white flex items-center gap-2.5">
            <LayoutDashboard class="w-6 h-6 text-cinema-accent" />
            <span>Trung Tâm Phân Tích & Báo Cáo Doanh Thu</span>
          </h1>
          <p class="text-xs text-cinema-muted mt-1">Dữ liệu thời gian thực đồng bộ từ mọi rạp chiếu phim và phòng vé trên toàn quốc</p>
        </div>

        <button 
          @click="fetchAnalytics"
          class="flex items-center gap-2 px-4 py-2 rounded-2xl bg-white/10 hover:bg-white/20 text-white text-xs font-bold transition-all cursor-pointer shrink-0"
        >
          <RotateCcw class="w-3.5 h-3.5" :class="{ 'animate-spin': isLoading }" />
          <span>Cập Nhật Ngay</span>
        </button>
      </div>

      <!-- Filters Row -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 pt-3 border-t border-white/5">
        
        <!-- 1. Period Selector -->
        <div class="space-y-1">
          <label class="block text-[11px] font-bold text-slate-400">📅 Khoảng Thời Gian</label>
          <div class="relative">
            <select 
              v-model="selectedPeriod"
              @change="fetchAnalytics"
              class="w-full bg-slate-900/90 border border-cinema-border rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-cinema-accent appearance-none cursor-pointer pr-8"
            >
              <option value="today">Hôm nay (24 Giờ)</option>
              <option value="7days">7 Ngày gần nhất</option>
              <option value="30days">30 Ngày qua (Tháng này)</option>
              <option value="this_year">Toàn bộ Năm nay (12 Tháng)</option>
            </select>
            <ChevronDown class="w-3.5 h-3.5 text-slate-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" />
          </div>
        </div>

        <!-- 2. Cinema Selector -->
        <div class="space-y-1">
          <label class="block text-[11px] font-bold text-slate-400">🏢 Lọc Theo Cụm Rạp</label>
          <div class="relative">
            <select 
              v-model="selectedCinemaId"
              @change="fetchAnalytics"
              class="w-full bg-slate-900/90 border border-cinema-border rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-cinema-accent appearance-none cursor-pointer pr-8"
            >
              <option value="all">Tất cả cụm rạp trên toàn quốc</option>
              <option v-for="c in cinemas" :key="c.id" :value="c.id">
                {{ c.name }} ({{ c.city }})
              </option>
            </select>
            <ChevronDown class="w-3.5 h-3.5 text-slate-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" />
          </div>
        </div>

        <!-- 3. Movie Selector -->
        <div class="space-y-1 sm:col-span-2">
          <label class="block text-[11px] font-bold text-slate-400">🎬 Lọc Theo Bộ Phim</label>
          <div class="relative">
            <select 
              v-model="selectedMovieId"
              @change="fetchAnalytics"
              class="w-full bg-slate-900/90 border border-cinema-border rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-cinema-accent appearance-none cursor-pointer pr-8"
            >
              <option value="all">Tất cả các phim đang chiếu & sắp chiếu</option>
              <option v-for="m in movies" :key="m.id" :value="m.id">
                {{ m.title }}
              </option>
            </select>
            <ChevronDown class="w-3.5 h-3.5 text-slate-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" />
          </div>
        </div>

      </div>

    </div>
    
    <!-- Top Metrics Overview Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      
      <!-- Metric 1: Total Revenue -->
      <div class="p-5 rounded-3xl bg-cinema-surface/90 border border-cinema-border space-y-3 relative overflow-hidden shadow-xl hover:border-emerald-500/30 transition-all">
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold text-slate-400">Tổng Doanh Thu</span>
          <div class="w-8 h-8 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center border border-emerald-500/20">
            <DollarSign class="w-4 h-4" />
          </div>
        </div>
        <div v-if="isLoading" class="space-y-2">
          <div class="h-7 w-32 bg-slate-800 rounded-lg animate-pulse"></div>
          <div class="h-4 w-24 bg-slate-800/60 rounded-md animate-pulse"></div>
        </div>
        <div v-else>
          <div class="text-2xl font-black text-white font-mono">
            {{ formatVnd(analytics?.metrics?.total_revenue || 0) }}
          </div>
          <div class="flex items-center gap-1 text-[11px] text-emerald-400 font-bold mt-1">
            <TrendingUp class="w-3 h-3" />
            <span>Doanh thu vé & combo</span>
          </div>
        </div>
        <div class="absolute -right-4 -bottom-4 w-20 h-20 bg-emerald-500/5 rounded-full blur-xl pointer-events-none"></div>
      </div>

      <!-- Metric 2: Tickets Sold -->
      <div class="p-5 rounded-3xl bg-cinema-surface/90 border border-cinema-border space-y-3 relative overflow-hidden shadow-xl hover:border-blue-500/30 transition-all">
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold text-slate-400">Số Vé Đã Bán</span>
          <div class="w-8 h-8 rounded-xl bg-blue-500/10 text-blue-400 flex items-center justify-center border border-blue-500/20">
            <Ticket class="w-4 h-4" />
          </div>
        </div>
        <div v-if="isLoading" class="space-y-2">
          <div class="h-7 w-24 bg-slate-800 rounded-lg animate-pulse"></div>
          <div class="h-4 w-28 bg-slate-800/60 rounded-md animate-pulse"></div>
        </div>
        <div v-else>
          <div class="text-2xl font-black text-white font-mono">
            {{ analytics?.metrics?.total_tickets || 0 }} <span class="text-xs text-slate-400 font-normal">vé</span>
          </div>
          <div class="flex items-center gap-1 text-[11px] text-blue-400 font-bold mt-1">
            <Users class="w-3 h-3" />
            <span>Đã xác nhận & check-in</span>
          </div>
        </div>
        <div class="absolute -right-4 -bottom-4 w-20 h-20 bg-blue-500/5 rounded-full blur-xl pointer-events-none"></div>
      </div>

      <!-- Metric 3: Occupancy Rate -->
      <div class="p-5 rounded-3xl bg-cinema-surface/90 border border-cinema-border space-y-3 relative overflow-hidden shadow-xl hover:border-amber-500/30 transition-all">
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold text-slate-400">Tỉ Lệ Lấp Đầy Ghế</span>
          <div class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center border border-amber-500/20">
            <Percent class="w-4 h-4" />
          </div>
        </div>
        <div v-if="isLoading" class="space-y-2">
          <div class="h-7 w-20 bg-slate-800 rounded-lg animate-pulse"></div>
          <div class="h-4 w-32 bg-slate-800/60 rounded-md animate-pulse"></div>
        </div>
        <div v-else>
          <div class="text-2xl font-black text-white font-mono">
            {{ analytics?.metrics?.occupancy_rate || 0 }}%
          </div>
          <div class="flex items-center gap-1 text-[11px] text-amber-400 font-bold mt-1">
            <span>Giờ vàng & cuối tuần</span>
          </div>
        </div>
        <div class="absolute -right-4 -bottom-4 w-20 h-20 bg-amber-500/5 rounded-full blur-xl pointer-events-none"></div>
      </div>

      <!-- Metric 4: Active Showtimes -->
      <div class="p-5 rounded-3xl bg-cinema-surface/90 border border-cinema-border space-y-3 relative overflow-hidden shadow-xl hover:border-purple-500/30 transition-all">
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold text-slate-400">Suất Chiếu Đang Mở</span>
          <div class="w-8 h-8 rounded-xl bg-purple-500/10 text-purple-400 flex items-center justify-center border border-purple-500/20">
            <Calendar class="w-4 h-4" />
          </div>
        </div>
        <div v-if="isLoading" class="space-y-2">
          <div class="h-7 w-28 bg-slate-800 rounded-lg animate-pulse"></div>
          <div class="h-4 w-28 bg-slate-800/60 rounded-md animate-pulse"></div>
        </div>
        <div v-else>
          <div class="text-2xl font-black text-white font-mono">
            {{ analytics?.metrics?.active_showtimes_count || 0 }} <span class="text-xs text-slate-400 font-normal">suất</span>
          </div>
          <div class="flex items-center gap-1 text-[11px] text-purple-400 font-bold mt-1">
            <span>Trên {{ analytics?.metrics?.total_cinemas_count || 30 }} cụm rạp toàn quốc</span>
          </div>
        </div>
        <div class="absolute -right-4 -bottom-4 w-20 h-20 bg-purple-500/5 rounded-full blur-xl pointer-events-none"></div>
      </div>

    </div>

    <!-- Charts Section: Main Trend Bar Chart & Cinema Share -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
      
      <!-- Left: Revenue Trend Chart (8 Cols) -->
      <div class="lg:col-span-8 p-6 rounded-3xl bg-cinema-surface/90 border border-cinema-border space-y-6 shadow-2xl">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-base font-extrabold text-white flex items-center gap-2">
              <TrendingUp class="w-4 h-4 text-cinema-accent" />
              <span>Biểu Đồ Doanh Thu Chi Tiết</span>
            </h3>
            <p class="text-xs text-slate-400 mt-0.5">Biểu đồ cột thể hiện biến động doanh thu theo mốc thời gian đã chọn</p>
          </div>
        </div>

        <!-- Custom SVG/HTML Bar Chart -->
        <div v-if="isLoading" class="h-64 flex items-end gap-3 pt-8 pb-2 border-b border-white/10 px-2">
          <div v-for="i in 10" :key="i" class="flex-1 flex flex-col items-center gap-2 h-full justify-end">
            <div class="w-full max-w-[36px] bg-slate-800 rounded-t-xl animate-pulse" :style="{ height: `${25 + (i * 7) % 65}%` }"></div>
            <div class="w-6 h-3 bg-slate-800/60 rounded animate-pulse"></div>
          </div>
        </div>
        <div v-else class="h-64 flex items-end gap-2 sm:gap-4 pt-8 pb-2 border-b border-white/10 px-2 overflow-x-auto">
          <div 
            v-for="(bar, i) in analytics?.daily_revenue || []" 
            :key="i"
            class="flex-1 flex flex-col items-center gap-2 h-full justify-end group min-w-[28px]"
          >
            <!-- Hover Tooltip -->
            <div class="opacity-0 group-hover:opacity-100 transition-opacity bg-slate-950/95 border border-cinema-border rounded-xl px-2.5 py-1.5 text-[10px] text-white text-center pointer-events-none absolute -translate-y-16 shadow-2xl z-20 whitespace-nowrap">
              <div class="font-bold text-amber-400 font-mono">{{ formatVnd(bar.revenue) }}</div>
              <div class="text-slate-400">{{ bar.tickets }} vé đã bán</div>
            </div>

            <!-- Bar -->
            <div 
              class="w-full max-w-[36px] bg-gradient-to-t from-cinema-accent to-rose-400 rounded-t-xl group-hover:brightness-125 transition-all shadow-glow-accent cursor-pointer"
              :style="{ height: `${Math.max(12, Math.min(100, (bar.revenue / maxDailyRev) * 100))}%` }"
            ></div>

            <!-- Date Label -->
            <span class="text-[10px] text-slate-400 font-bold group-hover:text-white transition-colors">
              {{ bar.date }}
            </span>
          </div>
        </div>

      </div>

      <!-- Right: Cinema Share Pie/List (4 Cols) -->
      <div class="lg:col-span-4 p-6 rounded-3xl bg-cinema-surface/90 border border-cinema-border space-y-6 shadow-2xl">
        <div>
          <h3 class="text-base font-extrabold text-white">Thị Phần Theo Cụm Rạp</h3>
          <p class="text-xs text-slate-400 mt-0.5">Tỉ trọng doanh thu các hệ thống rạp</p>
        </div>

        <div v-if="isLoading" class="space-y-4">
          <div v-for="i in 5" :key="i" class="space-y-2">
            <div class="flex justify-between">
              <div class="h-3.5 w-24 bg-slate-800 rounded animate-pulse"></div>
              <div class="h-3.5 w-8 bg-slate-800 rounded animate-pulse"></div>
            </div>
            <div class="h-2 w-full bg-slate-800/60 rounded-full animate-pulse"></div>
          </div>
        </div>
        <div v-else class="space-y-4">
          <div 
            v-for="c in analytics?.cinema_distribution || []" 
            :key="c.name"
            class="space-y-1.5"
          >
            <div class="flex items-center justify-between text-xs">
              <span class="font-bold text-white flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full" :style="{ backgroundColor: c.color }"></span>
                {{ c.name }}
              </span>
              <span class="font-mono text-amber-400 font-bold">{{ c.share }}%</span>
            </div>
            <div class="w-full h-2 rounded-full bg-slate-800 overflow-hidden">
              <div 
                class="h-full rounded-full transition-all duration-500" 
                :style="{ width: `${c.share}%`, backgroundColor: c.color }"
              ></div>
            </div>
          </div>
        </div>
      </div>

    </div>

    <!-- Top Movies Ranking Table -->
    <div class="p-6 rounded-3xl bg-cinema-surface/90 border border-cinema-border space-y-4 shadow-2xl">
      <div class="flex items-center justify-between">
        <div>
          <h3 class="text-base font-extrabold text-white">Top 5 Phim Có Doanh Thu Cao Nhất</h3>
          <p class="text-xs text-slate-400 mt-0.5">Xếp hạng các bom tấn chiếu rạp ăn khách nhất</p>
        </div>
        <router-link 
          to="/admin/movies" 
          class="text-xs font-bold text-cinema-accent hover:underline flex items-center gap-1"
        >
          <span>Xem tất cả phim</span>
          <ChevronRight class="w-3.5 h-3.5" />
        </router-link>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs text-slate-300">
          <thead class="bg-slate-900/60 text-[11px] uppercase tracking-wider text-slate-400 border-b border-white/5">
            <tr>
              <th class="p-3">#</th>
              <th class="p-3">Tên Phim</th>
              <th class="p-3">Điểm Đánh Giá</th>
              <th class="p-3">Thời Lượng</th>
              <th class="p-3">Vé Đã Bán</th>
              <th class="p-3 text-right">Doanh Thu</th>
            </tr>
          </thead>
          <tbody v-if="isLoading" class="divide-y divide-white/5">
            <tr v-for="i in 5" :key="i">
              <td class="p-3"><div class="h-4 w-6 bg-slate-800 rounded animate-pulse"></div></td>
              <td class="p-3 flex items-center gap-3">
                <div class="w-9 h-12 bg-slate-800 rounded-lg animate-pulse"></div>
                <div class="h-4 w-40 bg-slate-800 rounded animate-pulse"></div>
              </td>
              <td class="p-3"><div class="h-4 w-12 bg-slate-800 rounded animate-pulse"></div></td>
              <td class="p-3"><div class="h-4 w-16 bg-slate-800 rounded animate-pulse"></div></td>
              <td class="p-3"><div class="h-4 w-14 bg-slate-800 rounded animate-pulse"></div></td>
              <td class="p-3 text-right"><div class="h-4 w-20 bg-slate-800 rounded ml-auto animate-pulse"></div></td>
            </tr>
          </tbody>
          <tbody v-else class="divide-y divide-white/5">
            <tr 
              v-for="(m, idx) in analytics?.top_movies || []" 
              :key="m.id"
              class="hover:bg-white/5 transition-colors"
            >
              <td class="p-3 font-bold text-amber-400 font-mono">0{{ Number(idx) + 1 }}</td>

              <td class="p-3 flex items-center gap-3">
                <img :src="m.poster_url" class="w-9 h-12 rounded-lg object-cover border border-white/10" />
                <span class="font-bold text-white line-clamp-1">{{ m.title }}</span>
              </td>
              <td class="p-3 font-bold text-amber-400">★ {{ m.rating || 8.5 }}</td>
              <td class="p-3 text-slate-400">{{ m.duration || 120 }} phút</td>
              <td class="p-3 font-bold text-blue-400 font-mono">{{ m.tickets_sold }} vé</td>
              <td class="p-3 text-right font-bold text-emerald-400 font-mono">{{ formatVnd(m.revenue) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</template>

<script setup lang="ts">
import { 
  LayoutDashboard,
  DollarSign, 
  Ticket, 
  Percent, 
  Calendar, 
  TrendingUp, 
  Users, 
  RotateCcw,
  ChevronDown,
  ChevronRight
} from 'lucide-vue-next';
import { useAdminAnalytics } from '../../composables/useAdminAnalytics';

const {
  analytics,
  cinemas,
  movies,
  isLoading,
  selectedPeriod,
  selectedCinemaId,
  selectedMovieId,
  maxDailyRev,
  formatVnd,
  fetchAnalytics,
} = useAdminAnalytics();
</script>
