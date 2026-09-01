<template>
  <div class="space-y-6">
    
    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-black text-white flex items-center gap-2.5">
          <Users class="w-6 h-6 text-cinema-accent" />
          <span>Quản Lý Người Dùng & Phân Quyền (RBAC)</span>
        </h1>
        <p class="text-xs text-cinema-muted mt-1">
          Phân quyền quản trị viên, nhân viên soát vé, quản lý hạng thành viên và điểm thưởng CinePoints
        </p>
      </div>

      <BaseButton 
        variant="primary" 
        size="md" 
        @click="openCreateModal"
      >
        <template #prefix><UserPlus class="w-4 h-4" /></template>
        <span>Thêm Người Dùng Mới</span>
      </BaseButton>
    </div>

    <!-- Filter & Search Toolbar with Role Tabs -->
    <div class="p-4 rounded-3xl bg-cinema-surface/80 border border-cinema-border space-y-3 backdrop-blur-xl shadow-xl">
      
      <!-- Top Row: Role Tabs -->
      <div class="flex flex-wrap items-center justify-between gap-2 border-b border-white/5 pb-3">
        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 sm:pb-0 scrollbar-none">
          <button
            v-for="tab in roleTabs"
            :key="tab.value"
            @click="roleFilter = tab.value; onFilterChange()"
            class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer whitespace-nowrap"
            :class="[
              roleFilter === tab.value
                ? 'bg-cinema-accent text-white shadow-glow-accent'
                : 'bg-white/5 text-slate-400 hover:text-white'
            ]"
          >
            {{ tab.label }}
          </button>
        </div>

        <div class="w-full sm:w-56">
          <BaseSelect 
            v-model="tierFilter"
            @update:model-value="onFilterChange"
          >
            <option v-for="opt in tierOptions" :key="opt.value" :value="opt.value">
              {{ opt.label }}
            </option>
          </BaseSelect>
        </div>
      </div>

      <!-- Bottom Row: Search Box -->
      <div class="relative">
        <Search class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
        <input 
          v-model="searchQuery"
          @input="handleSearch"
          type="text"
          placeholder="Tìm theo họ tên, email hoặc số điện thoại..."
          class="w-full bg-slate-900/90 border border-cinema-border rounded-xl pl-10 pr-4 py-2 text-xs text-white placeholder:text-slate-500 focus:outline-none focus:border-cinema-accent transition-colors"
        />
      </div>

    </div>

    <!-- Users Table -->
    <div class="p-6 rounded-3xl bg-cinema-surface/80 border border-cinema-border shadow-2xl overflow-hidden space-y-4 backdrop-blur-md">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs text-slate-300">
          <thead class="text-[11px] uppercase tracking-wider text-slate-400 border-b border-white/5 pb-3 bg-slate-900/60">
            <tr>
              <th class="py-3 px-4">Thành Viên</th>
              <th class="py-3 px-4">Liên Hệ</th>
              <th class="py-3 px-4">Vai Trò (Role)</th>
              <th class="py-3 px-4">Cấp Bậc & Điểm</th>
              <th class="py-3 px-4 text-center">Đã Đặt Vé</th>
              <th class="py-3 px-4 text-right">Thao Tác</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-white/5 font-medium">
            <!-- Skeleton Loading -->
            <tr v-if="isLoading" v-for="i in 6" :key="i" class="animate-pulse">
              <td colspan="6" class="p-4"><div class="h-10 bg-white/5 rounded-xl w-full"></div></td>
            </tr>

            <!-- Empty State -->
            <tr v-else-if="users.length === 0">
              <td colspan="6" class="p-12 text-center text-slate-500 space-y-2">
                <Users class="w-8 h-8 mx-auto text-slate-600" />
                <p>Không tìm thấy người dùng nào phù hợp với bộ lọc</p>
              </td>
            </tr>

            <!-- Users List -->
            <tr 
              v-else
              v-for="user in users" 
              :key="user.id"
              class="hover:bg-white/5 transition-colors group"
            >
              <!-- Avatar & Name -->
              <td class="py-3.5 px-4 flex items-center gap-3">
                <div 
                  class="w-10 h-10 rounded-xl flex items-center justify-center font-black text-xs shrink-0 shadow-sm border border-white/10"
                  :class="[
                    user.role === 'admin' 
                      ? 'bg-linear-to-tr from-rose-600 to-amber-500 text-white shadow-glow-accent' 
                      : user.role === 'staff'
                      ? 'bg-linear-to-tr from-cyan-600 to-emerald-500 text-white'
                      : 'bg-slate-800 text-slate-300'
                  ]"
                >
                  {{ user.name?.substring(0, 2).toUpperCase() || 'US' }}
                </div>
                <div>
                  <h4 class="font-extrabold text-white text-sm leading-snug group-hover:text-amber-400 transition-colors">
                    {{ user.name }}
                  </h4>
                  <span class="text-[11px] text-slate-400 font-mono">ID: #{{ user.id }}</span>
                </div>
              </td>

              <!-- Email & Phone -->
              <td class="py-3.5 px-4 space-y-0.5">
                <div class="flex items-center gap-1.5 text-xs text-white">
                  <Mail class="w-3.5 h-3.5 text-slate-400 shrink-0" />
                  <span class="font-mono truncate">{{ user.email }}</span>
                </div>
                <div v-if="user.phone" class="flex items-center gap-1.5 text-[11px] text-slate-400 font-mono">
                  <Phone class="w-3 h-3 text-slate-500 shrink-0" />
                  <span>{{ user.phone }}</span>
                </div>
              </td>

              <!-- Role Selector Quick Change -->
              <td class="py-3.5 px-4">
                <div class="relative inline-block">
                  <select 
                    :value="user.role || 'user'"
                    @change="handleQuickRoleChange(user.id, ($event.target as HTMLSelectElement).value)"
                    class="bg-slate-900 border rounded-xl px-2.5 py-1 text-xs font-bold appearance-none cursor-pointer pr-6 transition-all"
                    :class="[
                      user.role === 'admin' 
                        ? 'border-rose-500/50 text-rose-400 bg-rose-500/10' 
                        : user.role === 'staff'
                        ? 'border-cyan-500/50 text-cyan-400 bg-cyan-500/10'
                        : 'border-slate-700 text-slate-300'
                    ]"
                  >
                    <option value="admin">👑 Admin</option>
                    <option value="staff">🎫 Staff</option>
                    <option value="user">👤 Khách Hàng</option>
                  </select>
                </div>
              </td>

              <!-- Membership & Points -->
              <td class="py-3.5 px-4">
                <div v-if="user.role === 'admin'" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-rose-500/15 border border-rose-500/30 text-rose-300 font-bold text-xs shadow-sm">
                  <ShieldCheck class="w-3.5 h-3.5 text-rose-400" />
                  <span>Admin Master</span>
                </div>
                <div v-else-if="user.role === 'staff'" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-cyan-500/15 border border-cyan-500/30 text-cyan-300 font-bold text-xs shadow-sm">
                  <QrCode class="w-3.5 h-3.5 text-cyan-400" />
                  <span>Staff Soát Vé</span>
                </div>
                <div v-else class="flex items-center gap-1.5">
                  <BaseBadge 
                    :variant="user.membership_tier === 'diamond' ? 'purple' : user.membership_tier === 'vip' ? 'gold' : 'blue'" 
                    size="xs"
                  >
                    {{ user.membership_tier === 'diamond' ? '💎 Kim Cương' : user.membership_tier === 'vip' ? '👑 CineVIP' : '🥈 CineMember' }}
                  </BaseBadge>
                  <button 
                    @click="openPointsModal(user)"
                    class="text-[11px] text-amber-400 font-mono font-bold hover:underline flex items-center gap-0.5 cursor-pointer bg-amber-500/10 px-1.5 py-0.5 rounded-md border border-amber-500/20"
                    title="Điều chỉnh CinePoints"
                  >
                    <Sparkles class="w-3 h-3 text-amber-400" />
                    <span>{{ user.points }} pts</span>
                  </button>
                </div>
              </td>

              <!-- Total Bookings -->
              <td class="py-3.5 px-4 text-center font-mono font-bold text-slate-300">
                <span class="px-2.5 py-1 rounded-xl bg-white/5 border border-white/5 text-slate-200">
                  {{ user.bookings_count || user.total_tickets_bought || 0 }} vé
                </span>
              </td>

              <!-- Actions -->
              <td class="py-3.5 px-4 text-right">
                <div class="flex items-center justify-end gap-2">
                  <button 
                    @click="openEditModal(user)"
                    class="p-2 rounded-xl bg-white/5 hover:bg-white/15 text-slate-300 hover:text-white transition-colors cursor-pointer"
                    title="Chỉnh sửa thông tin"
                  >
                    <Edit class="w-3.5 h-3.5" />
                  </button>
                  <button 
                    @click="handleDelete(user.id)"
                    class="p-2 rounded-xl bg-rose-500/10 hover:bg-rose-500/25 text-rose-400 transition-colors cursor-pointer"
                    title="Xóa tài khoản"
                  >
                    <Trash2 class="w-3.5 h-3.5" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <BasePagination 
        v-model:currentPage="currentPage"
        :totalPages="totalPages"
        :totalItems="totalUsers"
        @change="changePage"
      />
    </div>

    <!-- Create / Edit User Modal -->
    <BaseModal 
      v-model="isModalOpen" 
      :title="isEditing ? 'Chỉnh Sửa Tài Khoản' : 'Thêm Người Dùng / Nhân Viên Mới'"
      maxWidth="xl"
    >
      <form @submit.prevent="handleSubmit" class="space-y-4 p-1">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <BaseInput 
            v-model="form.name"
            label="Họ và Tên *"
            placeholder="Nguyễn Văn A"
            required
          />
          <BaseInput 
            v-model="form.email"
            type="email"
            label="Địa Chỉ Email *"
            placeholder="example@gmail.com"
            required
          />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <BaseInput 
            v-model="form.phone"
            label="Số Điện Thoại"
            placeholder="0988 123 456"
          />
          <BaseInput 
            v-model="form.password"
            type="password"
            :label="isEditing ? 'Mật Khẩu Mới (Bỏ trống nếu giữ nguyên)' : 'Mật Khẩu Khởi Tạo *'"
            :placeholder="isEditing ? '••••••••' : 'Ít nhất 6 ký tự'"
            :required="!isEditing"
          />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <BaseSelect 
            v-model="form.role"
            label="Phân Quyền (Role) *"
            required
          >
            <option value="user">👤 Khách Hàng (Customer)</option>
            <option value="staff">🎫 Nhân Viên (Staff Soát Vé)</option>
            <option value="admin">👑 Quản Trị Viên (Admin Master)</option>
          </BaseSelect>

          <BaseSelect 
            v-if="form.role === 'user'"
            v-model="form.membership_tier"
            label="Hạng Thành Viên *"
            required
          >
            <option value="member">🥈 CineMember (Bạc)</option>
            <option value="vip">👑 CineVIP (Vàng)</option>
            <option value="diamond">💎 CineDiamond (Kim Cương)</option>
          </BaseSelect>

          <BaseInput 
            v-if="form.role === 'user'"
            v-model="form.points"
            type="number"
            label="Điểm CinePoints"
            min="0"
            required
          />

          <div v-else class="sm:col-span-2 p-2.5 rounded-2xl bg-white/5 border border-white/5 flex items-center gap-2 text-xs text-slate-400">
            <Info class="w-4 h-4 text-cyan-400 shrink-0" />
            <span>Tài khoản <strong>{{ form.role === 'admin' ? 'Admin' : 'Staff' }}</strong> là vai trò nội bộ, không tích lũy điểm thưởng.</span>
          </div>
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
            {{ isEditing ? 'Lưu Thay Đổi' : 'Tạo Tài Khoản' }}
          </BaseButton>
        </div>
      </form>
    </BaseModal>

    <!-- Adjust Points Modal -->
    <BaseModal 
      v-model="isPointsModalOpen" 
      title="Điều Chỉnh Điểm Tích Lũy CinePoints"
      maxWidth="md"
    >
      <div v-if="selectedUserForPoints" class="space-y-4 p-1">
        <div class="p-3 rounded-2xl bg-slate-900 border border-white/10 flex items-center justify-between">
          <div>
            <span class="text-xs text-slate-400 block">Thành viên:</span>
            <strong class="text-white text-sm">{{ selectedUserForPoints.name }}</strong>
          </div>
          <div class="text-right">
            <span class="text-xs text-slate-400 block">Điểm hiện có:</span>
            <strong class="text-amber-400 font-mono text-sm">{{ selectedUserForPoints.points }} pts</strong>
          </div>
        </div>

        <BaseInput 
          v-model="pointsDelta"
          type="number"
          label="Số Điểm Muốn Thêm / Bớt (+ hoặc -) *"
          placeholder="+100 hoặc -50"
          required
        />

        <div class="flex items-center justify-end gap-3 pt-3 border-t border-white/5">
          <BaseButton 
            type="button" 
            variant="secondary" 
            @click="isPointsModalOpen = false"
          >
            Hủy Bỏ
          </BaseButton>
          <BaseButton 
            type="button" 
            variant="primary" 
            :loading="isSubmitting"
            @click="handleSavePoints"
          >
            Xác Nhận Điều Chỉnh
          </BaseButton>
        </div>
      </div>
    </BaseModal>

  </div>
</template>

<script setup lang="ts">
import { 
  Users, 
  UserPlus, 
  Search, 
  Edit, 
  Trash2, 
  Mail, 
  Phone, 
  Sparkles,
  ShieldCheck,
  QrCode,
  Info
} from 'lucide-vue-next';
import { useAdminUsers } from '../../composables/useAdminUsers';
import BaseModal from '../../components/base/BaseModal.vue';
import BaseInput from '../../components/base/BaseInput.vue';
import BaseButton from '../../components/base/BaseButton.vue';
import BaseBadge from '../../components/base/BaseBadge.vue';
import BaseSelect from '../../components/base/BaseSelect.vue';
import BasePagination from '../../components/base/BasePagination.vue';

const {
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
  roleTabs,
  tierOptions,
  form,
  handleSearch,
  onFilterChange,
  changePage,
  openCreateModal,
  openEditModal,
  openPointsModal,
  handleSubmit,
  handleDelete,
  handleQuickRoleChange,
  handleSavePoints,
} = useAdminUsers();
</script>
