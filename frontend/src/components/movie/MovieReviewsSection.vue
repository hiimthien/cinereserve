<template>
  <div class="space-y-8 bg-cinema-surface/60 border border-cinema-border rounded-3xl p-6 md:p-8 backdrop-blur-xl shadow-2xl">
    
    <!-- Section Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border-b border-white/5 pb-5">
      <div>
        <h2 class="text-xl font-extrabold text-white flex items-center gap-2.5">
          <MessageSquareQuote class="w-5 h-5 text-amber-400" />
          <span>Đánh Giá & Nhận Xét Từ Khán Giả</span>
        </h2>
        <p class="text-xs text-cinema-muted mt-1">
          Chia sẻ cảm nhận chân thực của bạn sau khi thưởng thức bộ phim
        </p>
      </div>

      <div class="flex items-center gap-2 bg-amber-500/10 border border-amber-500/20 px-4 py-2 rounded-2xl">
        <Star class="w-5 h-5 fill-amber-400 text-amber-400" />
        <div>
          <span class="text-lg font-black text-amber-400 font-mono leading-none">{{ currentRating || '8.5' }}</span>
          <span class="text-[10px] text-slate-400 block">Điểm cộng đồng</span>
        </div>
      </div>
    </div>

    <!-- Review Form -->
    <form @submit.prevent="handleSubmit" class="p-5 rounded-2xl bg-slate-900/90 border border-white/10 space-y-4 shadow-inner">
      <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
        <span class="text-xs font-bold text-slate-300">Chọn số điểm bạn đánh giá cho phim:</span>
        
        <!-- Interactive Star Selector (1 to 10) -->
        <div class="flex items-center gap-1">
          <button
            v-for="star in 10"
            :key="star"
            type="button"
            @click="newRating = star"
            class="p-1 text-slate-600 hover:text-amber-400 transition-colors cursor-pointer"
            :class="{ 'text-amber-400 scale-110': star <= newRating }"
          >
            <Star class="w-4 h-4" :class="star <= newRating ? 'fill-amber-400' : 'fill-transparent'" />
          </button>
          <span class="ml-2 font-mono font-black text-amber-400 text-sm">{{ newRating }}/10</span>
        </div>
      </div>

      <!-- Guest Name Input if not logged in -->
      <div v-if="!authStore.isAuthenticated" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <BaseInput 
          v-model="customName"
          label="Tên của bạn (Tùy chọn)"
          placeholder="Ví dụ: Nguyễn Hoàng"
        />
        <div class="flex items-center gap-2 text-xs text-cinema-muted pt-6">
          <span>💡 Hoặc</span>
          <button 
            type="button" 
            @click="authStore.openAuth('login')" 
            class="text-cinema-accent hover:underline font-bold cursor-pointer"
          >
            Đăng nhập để nhận điểm CinePoints
          </button>
        </div>
      </div>

      <!-- Comment Textarea -->
      <div class="space-y-1.5">
        <textarea 
          v-model="newComment"
          rows="3"
          placeholder="Bộ phim có kịch bản như thế nào? Kỹ xảo, diễn xuất, âm nhạc có ấn tượng không? Hãy để lại cảm nhận của bạn nhé..."
          class="w-full bg-slate-950/90 border border-white/10 rounded-2xl p-3.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-amber-400/80 transition-colors"
          required
        ></textarea>
      </div>

      <div class="flex items-center justify-end">
        <BaseButton 
          type="submit" 
          variant="primary" 
          size="md"
          :loading="isSubmitting"
        >
          <template #prefix><Send class="w-3.5 h-3.5" /></template>
          <span>Gửi Đánh Giá Ngay</span>
        </BaseButton>
      </div>
    </form>

    <!-- Reviews Community List -->
    <div class="space-y-4">
      <h3 class="text-sm font-extrabold text-white flex items-center gap-2">
        <span>Bình luận gần đây</span>
        <span class="px-2 py-0.5 rounded-full bg-white/5 text-[11px] font-mono text-slate-400">
          {{ reviews.length }} nhận xét
        </span>
      </h3>

      <div v-if="isLoading" class="space-y-3">
        <div v-for="i in 3" :key="i" class="p-4 rounded-2xl bg-white/5 animate-pulse space-y-2">
          <div class="h-4 bg-white/10 rounded w-1/4"></div>
          <div class="h-3 bg-white/5 rounded w-3/4"></div>
        </div>
      </div>

      <div v-else-if="reviews.length === 0" class="p-10 text-center text-slate-500 space-y-2 bg-slate-900/40 rounded-2xl border border-white/5">
        <MessageSquareQuote class="w-8 h-8 mx-auto text-slate-600" />
        <p class="text-xs">Chưa có đánh giá nào cho phim này. Hãy là người đầu tiên để lại nhận xét!</p>
      </div>

      <div v-else class="space-y-3">
        <div 
          v-for="r in reviews" 
          :key="r.id"
          class="p-4 rounded-2xl bg-slate-900/80 border border-white/5 space-y-2.5 hover:border-white/15 transition-all"
        >
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2.5">
              <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-amber-500/30 to-rose-500/30 border border-white/10 flex items-center justify-center font-bold text-xs text-white">
                {{ r.user_name?.substring(0, 2).toUpperCase() || 'KH' }}
              </div>
              <div>
                <div class="flex items-center gap-2">
                  <span class="text-xs font-extrabold text-white">{{ r.user_name }}</span>
                  <BaseBadge 
                    v-if="r.membership_tier === 'diamond'" 
                    variant="purple" 
                    size="xs"
                  >
                    💎 VIP Diamond
                  </BaseBadge>
                  <BaseBadge 
                    v-else-if="r.membership_tier === 'vip'" 
                    variant="gold" 
                    size="xs"
                  >
                    👑 CineVIP
                  </BaseBadge>
                </div>
                <span class="text-[10px] text-slate-500 font-mono">{{ r.created_at_human || 'Vừa xong' }}</span>
              </div>
            </div>

            <!-- Rating Stars Badge -->
            <div class="flex items-center gap-1 bg-amber-500/10 px-2.5 py-1 rounded-xl border border-amber-500/20 text-amber-400 text-xs font-mono font-black">
              <Star class="w-3.5 h-3.5 fill-amber-400" />
              <span>{{ r.rating }}/10</span>
            </div>
          </div>

          <p class="text-xs text-slate-300 leading-relaxed pl-10">
            {{ r.comment }}
          </p>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue';
import { MessageSquareQuote, Star, Send } from 'lucide-vue-next';
import { useMovieReviews } from '../../composables/useMovieReviews';
import BaseButton from '../base/BaseButton.vue';
import BaseInput from '../base/BaseInput.vue';
import BaseBadge from '../base/BaseBadge.vue';

const props = defineProps<{
  movieId?: number;
  currentRating?: number | string;
}>();

const emit = defineEmits<{
  (e: 'review-added', newRating: number): void;
}>();

const {
  reviews,
  isLoading,
  isSubmitting,
  newRating,
  newComment,
  customName,
  authStore,
  fetchReviews,
  submitReview,
} = useMovieReviews(() => props.movieId);

const handleSubmit = () => {
  submitReview((created) => {
    emit('review-added', created.rating);
  });
};

onMounted(() => {
  fetchReviews();
});
</script>
