<template>
  <div class="min-h-screen bg-cinema-bg flex flex-col justify-between">
    <Navbar />

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full flex-1 space-y-8">
      
      <!-- Page Title Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-cinema-border pb-6">
        <div class="space-y-1">
          <div class="flex items-center gap-2">
            <span class="p-2 rounded-2xl bg-cinema-accent/10 border border-cinema-accent/30 text-cinema-accent">
              <Ticket class="w-5 h-5" />
            </span>
            <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight">Vé Của Tôi</h1>
          </div>
          <p class="text-xs text-cinema-muted">
            Quản lý toàn bộ vé điện tử E-Ticket, lịch sử xem phim và mã QR check-in tại rạp
          </p>
        </div>

        <div v-if="authStore.isAuthenticated" class="flex items-center gap-3">
          <span class="text-xs font-mono text-slate-400 bg-slate-900/80 px-3 py-1.5 rounded-xl border border-white/5">
            Tổng cộng: <strong class="text-white">{{ totalTicketsCount }} vé</strong>
          </span>
        </div>
      </div>

      <!-- Unauthenticated State Banner -->
      <div 
        v-if="!authStore.isAuthenticated" 
        class="p-12 rounded-3xl bg-cinema-card/50 border border-cinema-border text-center space-y-4"
      >
        <div class="w-16 h-16 rounded-full bg-cinema-accent/10 flex items-center justify-center mx-auto text-cinema-accent">
          <LogIn class="w-8 h-8" />
        </div>
        <h3 class="text-lg font-bold text-white">Vui lòng đăng nhập để xem vé</h3>
        <p class="text-xs text-cinema-muted max-w-md mx-auto">
          Đăng nhập bằng tài khoản hoặc Google để tra cứu danh sách vé xem phim và điểm thưởng của bạn.
        </p>
        <BaseButton 
          variant="primary" 
          size="lg"
          @click="authStore.openAuth('login')"
        >
          Đăng Nhập Ngay
        </BaseButton>
      </div>

      <!-- Authenticated Content -->
      <div v-else class="space-y-6">
        
        <!-- Reusable Filters Component -->
        <TicketFilters 
          v-model:activeStatus="activeStatus"
          v-model:selectedMovieId="selectedMovieId"
          v-model:selectedCinemaId="selectedCinemaId"
          v-model:searchQuery="searchQuery"
          :availableMovies="availableMovies"
          :availableCinemas="availableCinemas"
          @filter-change="fetchTickets"
          @reset="resetFilters"
        />

        <!-- Loading Skeleton Grid -->
        <div v-if="isLoading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div v-for="i in 6" :key="i" class="h-64 rounded-3xl bg-cinema-card/40 border border-white/5 animate-pulse"></div>
        </div>

        <!-- Empty State -->
        <div 
          v-else-if="tickets.length === 0" 
          class="p-16 rounded-3xl bg-cinema-card/30 border border-cinema-border text-center space-y-4"
        >
          <div class="w-16 h-16 rounded-full bg-slate-800 flex items-center justify-center mx-auto text-slate-500">
            <Ticket class="w-8 h-8" />
          </div>
          <h3 class="text-base font-bold text-white">Không tìm thấy vé nào</h3>
          <p class="text-xs text-cinema-muted max-w-sm mx-auto">
            {{ searchQuery || selectedMovieId !== 'all' ? 'Thử thay đổi bộ lọc tìm kiếm' : 'Bạn chưa có vé xem phim nào. Đặt vé ngay hôm nay!' }}
          </p>
          <router-link 
            to="/" 
            class="inline-flex items-center gap-2 px-6 py-2.5 rounded-2xl bg-cinema-accent text-white text-xs font-bold shadow-glow-accent hover:bg-rose-600 transition-colors"
          >
            <Film class="w-4 h-4" />
            <span>Khám Phá Phim Chiếu</span>
          </router-link>
        </div>

        <!-- Ticket Cards Grid -->
        <div v-else class="space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <TicketCard 
              v-for="ticket in tickets" 
              :key="ticket.id" 
              :ticket="ticket"
              @view-detail="openTicketDetail"
            />
          </div>

          <!-- Reusable Pagination -->
          <BasePagination 
            :currentPage="currentPage"
            :totalPages="totalPages"
            :totalItems="totalTicketsCount"
            @change="changePage"
          />
        </div>

      </div>

    </main>

    <!-- Rich Ticket Detail QR Modal -->
    <TicketDetailModal 
      v-model="isDetailModalOpen"
      :ticket="selectedTicket"
    />

    <Footer />
  </div>
</template>

<script setup lang="ts">
import { Ticket, LogIn, Film } from 'lucide-vue-next';
import { useMyTickets } from '../composables/useMyTickets';
import Navbar from '../components/common/Navbar.vue';
import Footer from '../components/common/Footer.vue';
import TicketCard from '../components/ticket/TicketCard.vue';
import TicketDetailModal from '../components/ticket/TicketDetailModal.vue';
import TicketFilters from '../components/ticket/TicketFilters.vue';
import BaseButton from '../components/base/BaseButton.vue';
import BasePagination from '../components/base/BasePagination.vue';

const {
  authStore,
  tickets,
  totalTicketsCount,
  totalPages,
  currentPage,
  activeStatus,
  selectedMovieId,
  selectedCinemaId,
  searchQuery,
  isLoading,
  availableMovies,
  availableCinemas,
  isDetailModalOpen,
  selectedTicket,
  fetchTickets,
  openTicketDetail,
  resetFilters,
  changePage,
} = useMyTickets();
</script>
