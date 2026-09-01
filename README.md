# 🎬 CineReserve - Nền Tảng Đặt Vé Xem Phim Real-Time & Xử Lý Đồng Thời (Full-Stack Concurrency Engine)

[![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![Vue.js](https://img.shields.io/badge/Vue.js-3.x-4FC08D?style=for-the-badge&logo=vue.js&logoColor=white)](https://vuejs.org)
[![TypeScript](https://img.shields.io/badge/TypeScript-5.x-3178C6?style=for-the-badge&logo=typescript&logoColor=white)](https://www.typescriptlang.org/)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-v4-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com)
[![Redis](https://img.shields.io/badge/Redis-Distributed_Lock_%26_Queue-DC382D?style=for-the-badge&logo=redis&logoColor=white)](https://redis.io)
[![Laravel Reverb](https://img.shields.io/badge/Laravel_Reverb-WebSocket_Real--Time-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com/docs/11.x/reverb)
[![Docker](https://img.shields.io/badge/Docker-Containerized-2496ED?style=for-the-badge&logo=docker&logoColor=white)](https://docker.com)

**CineReserve** là hệ thống đặt vé xem phim trực tuyến toàn diện đạt chuẩn Production, được xây dựng trên nền tảng **Laravel 11 (PHP 8.3)**, **Vue 3 (Composition API + TypeScript + Pinia + Tailwind v4)**, kết hợp cùng **Laravel Reverb (WebSockets)** và cơ chế khóa phân tán **Redis Atomic Lock**. Dự án giải quyết triệt để bài toán **Xử lý đồng thời (High Concurrency)** và **Chống mua trùng ghế (Zero Race Condition / Double-Booking)** trong các đợt mở bán vé bom tấn.

---

## 🌟 Điểm Sáng Kỹ Thuật (Key Technical Highlights)

### 1. ⚡ Đồng Bộ Ghế Thời Gian Thực (Real-Time WebSockets Broadcasting)
* Tích hợp **Laravel Reverb** và **Laravel Echo** phát sự kiện tức thì qua WebSockets với độ trễ dưới **50ms**.
* Sơ đồ ghế ma trận hiển thị 4 trạng thái linh hoạt:
  - 🟢 **Ghế Trống (Available):** Sẵn sàng để chọn (Thường, VIP, Đôi).
  - 🟢 **Đang Chọn (Selected):** Ghế người dùng hiện tại đang chọn với hiệu ứng phát sáng Emerald.
  - 🟡 **Đang Giữ Chỗ (Holding):** Hiệu ứng nhấp nháy vàng khi người khác đang trong phiên giữ ghế 10 phút.
  - 🔴 **Đã Bán (Booked):** Ghế đã thanh toán hoàn tất, vô hiệu hóa chọn lại.

### 2. 🛡️ Cơ Chế Chống Mua Trùng Ghế 2 Lớp (Two-Tier Concurrency Control)
* **Lớp 1 (RAM Level - Tốc độ cao):** **Redis Atomic Distributed Lock** (`SET lock:seat:{id} {user_id} NX EX 600`) khóa giữ ghế trong **10 phút**. Tốc độ phản hồi cực nhanh (1–2ms), loại bỏ xung đột ngay từ tầng bộ nhớ trước khi chạm tới Database.
* **Lớp 2 (Database Level - Toàn vẹn dữ liệu):** Sử dụng **Pessimistic Locking (`lockForUpdate()`)** kết hợp **ACID Database Transaction** khi thanh toán, đảm bảo **100% không bao giờ xảy ra tình trạng 2 người mua cùng 1 ghế**.
* **Tự động giải phóng ghế:** Background Queue Worker tự động mở khóa ghế khi hết thời gian giữ 10 phút.

### 3. 🎟️ Máy Quét Soát Vé Chuyên Dụng (Staff PWA Scanner `/staff/scanner`)
* Giao diện PWA dành riêng cho nhân viên rạp: Bật camera quét mã QR trên vé điện tử của khách hoặc nhập mã vé nhanh.
* **Chống gian lận (Anti-Fraud):** Báo động đỏ ngay lập tức nếu vé **Đã qua sử dụng** hoặc **Quá hạn**.
* **Cảnh báo nhãn tuổi Cục Điện Ảnh:** Nhắc nhở Staff kiểm tra CCCD khách hàng theo quy định nhãn phim (**P, K, T13, T16, T18**).
* **Nhắc combo F&B:** Hiển thị phần Bắp & Nước khách đã đặt để quầy chuẩn bị giao kèm.

### 4. 📊 Cổng Quản Trị Hệ Thống Toàn Diện (Admin Master Portal `/admin`)
* **Dashboard Phân Tích Doanh Thu:** Biểu đồ doanh thu 12 tháng, KPIs tổng quan, biểu đồ thị phần 5 hệ thống rạp lớn (CGV, Lotte, Galaxy, BHD, Beta) và Top 5 phim ăn khách.
* **Quản Lý Suất Chiếu Đột Phá:** Hỗ trợ tạo suất chiếu đơn lẻ hoặc **Tạo hàng loạt (Batch Generation)** đồng thời cho nhiều rạp và nhiều khung giờ trong tuần.
* **Quản Lý Cụm Rạp & Phòng Chiếu:** Quản trị 30 cụm rạp trên 13 tỉnh thành, 70+ phòng chiếu chuẩn 2D, 3D, IMAX, 4DX.
* **Phân Quyền Người Dùng (RBAC):** Quản trị 3 vai trò rõ ràng (`Admin`, `Staff`, `Customer`).
* **Hệ Thống Khách Hàng Thân Thiết (CinePoints & Loyalty):** 4 hạng thành viên (**Diamond, VIP, Gold, Member**) và tính năng Đổi Quà Voucher / F&B.
* **Quản Lý Bắp Nước (F&B) & Voucher Khuyến Mãi.**

### 5. 🌐 Đồng Bộ Dữ Liệu Phim Tự Động Từ TMDb API
* Tự động lấy dữ liệu 36+ bộ phim bom tấn chiếu rạp thực tế (Poster 4K, Trailer Youtube, đạo diễn, diễn viên, thời lượng, phân loại độ tuổi).

---

## 🏗️ Kiến Trúc Hệ Thống (System Architecture)

```
[ Khách Hàng / Vue 3 SPA ]          [ Nhân Viên / Staff Scanner ]
           │                                      │
           │ (REST API via Axios)                 │ (Camera QR Scanner)
           ▼                                      ▼
[ Nginx Reverse Proxy (Cổng 80) ] ───────────────► [ Laravel Reverb WebSockets (Cổng 8080) ]
           │                                                  ▲
           ▼                                                  │ (Event Broadcasting)
[ Laravel 11 Backend (PHP 8.3 FPM) ] ─────────────────────────┘
   ├── MySQL 8.0 (ACID Transactions, Bookings, Payments, Users, Theaters)
   ├── Redis 7.0 (Atomic Seat Locks, System Cache & Async Queue Jobs)
   └── VNPAY / MoMo Payment Gateway Simulation (Idempotency Protected)
```

---

## 🚀 Hướng Dẫn Cài Đặt & Khởi Chạy Nhanh

### Cách 1: Chạy Bằng Docker Compose (Khuyên Dùng - Tự Động 100%)

#### 1. Clone repository:
```bash
git clone https://github.com/hiimthien/cinereserve.git
cd cinereserve
```

#### 2. Cấu hình file môi trường Backend:
```bash
cp backend/.env.example backend/.env
```

#### 3. Khởi động toàn bộ cụm Container Docker:
```bash
docker compose up -d --build
```
> Lệnh trên sẽ tự động khởi chạy 6 dịch vụ: `cinereserve-nginx`, `cinereserve-php`, `cinereserve-mysql`, `cinereserve-redis`, `cinereserve-reverb`, `cinereserve-queue`.

#### 4. Chạy Migration và Nạp Dữ Liệu Mẫu Chuẩn (Seeder):
```bash
docker exec cinereserve-php php artisan key:generate
docker exec cinereserve-php php artisan migrate --seed
```

#### 5. Khởi động Frontend (Vue 3):
```bash
cd frontend
npm install
npm run dev
```

Truy cập ứng dụng tại: **`http://localhost:5173`** (Frontend) hoặc **`http://localhost`** (API Backend).

---

## 🔑 Danh Sách Tài Khoản Mẫu Để Trải Nghiệm (Demo Accounts)

| Vai Trò (Role) | Email Đăng Nhập | Mật Khẩu | Quyền Hạn & Trang Trải Nghiệm |
|---|---|---|---|
| 👑 **Admin Master** | `admin@cinereserve.com` | `password` | Toàn quyền Quản Trị Hệ Thống (`/admin`) |
| 🎫 **Staff Soát Vé** | `staff@cinereserve.com` | `password` | Máy Quét QR Soát Vé Tại Rạp (`/staff/scanner`) |
| 💎 **Khách Diamond** | `diamond@cinereserve.com` | `password` | 2,500 CinePoints (Hạng Kim Cương - Đổi quà VIP) |
| 👑 **Khách VIP (Gold)** | `vip@cinereserve.com` | `password` | 1,200 CinePoints (Hạng Vàng) |
| 🥈 **Khách Member** | `member@cinereserve.com` | `password` | 300 CinePoints (Hạng Bạc) |

---

## 📁 Cấu Trúc Thư Mục Dự Án

```
cinereserve/
├── backend/                        # Laravel 11 REST API Backend
│   ├── app/
│   │   ├── Http/Controllers/Api/   # API Controllers (Admin, Auth, Booking, Movie, Showtimes...)
│   │   ├── Models/                 # Eloquent Models (Movie, Showtime, Booking, Seat, User, Cinema...)
│   │   ├── Repositories/           # Service-Repository Pattern
│   │   ├── Services/               # Business Logic & TmdbMovieSyncService
│   │   ├── Events/                 # Real-time WebSocket Events (SeatSelectedEvent...)
│   │   └── Jobs/                   # Asynchronous Queue Jobs
│   ├── database/seeders/           # Realistic Showtimes, TMDb Movies & Bookings Seeders
│   └── routes/api.php              # API Endpoints
├── frontend/                       # Vue 3 Single Page Application (SPA)
│   ├── src/
│   │   ├── components/             # Reusable UI & Modal Components
│   │   │   ├── base/               # BaseButton, BaseInput, BaseSelect, BaseModal, BaseBadge...
│   │   │   ├── admin/              # Admin Analytics, Showtimes, Movies, Users Components
│   │   │   ├── booking/            # Real-time SeatGridMap, FoodBeverageSelector...
│   │   │   └── scanner/            # Staff QR Scanner & History
│   │   ├── views/                  # Home, SeatSelection, Checkout, Admin, Staff Scanner Views
│   │   ├── stores/                 # Pinia Global State Management (seatStore, authStore, bookingStore...)
│   │   └── services/               # Axios API Interceptors & WebSocket Echo Listeners
└── docker-compose.yml              # Multi-container Docker Setup (Nginx, PHP, MySQL, Redis, Reverb, Queue)
```

---

## 🛠️ Công Nghệ Sử Dụng (Tech Stack)

* **Backend:** PHP 8.3, Laravel 11, Laravel Reverb (WebSockets), Redis (Atomic Distributed Locks), MySQL 8.0.
* **Frontend:** Vue 3 (Composition API), TypeScript, Pinia, Vite, Tailwind CSS v4, Lucide Icons, Chart.js, HTML5-QRCode.
* **DevOps & Hạ tầng:** Docker, Docker Compose, Nginx Reverse Proxy, Git, GitHub Actions.

---

## 📄 Bản Quyền (License)
Dự án được phát triển với mục đích học tập và xây dựng sản phẩm chất lượng cao trong Portfolio cá nhân.
Phát hành theo giấy phép **MIT License**.
