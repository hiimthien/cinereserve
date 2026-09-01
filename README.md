# CineReserve 🎬

> Hệ thống đặt vé xem phim trực tuyến thời gian thực, tập trung giải quyết bài toán **Concurrency (Xử lý đồng thời)** và **Race Condition khi giữ ghế** trong các đợt mở bán vé cao điểm.

[![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=flat-square&logo=laravel&logoColor=white)](https://laravel.com)
[![Vue.js](https://img.shields.io/badge/Vue.js-3.x-4FC08D?style=flat-square&logo=vue.js&logoColor=white)](https://vuejs.org)
[![TypeScript](https://img.shields.io/badge/TypeScript-5.x-3178C6?style=flat-square&logo=typescript&logoColor=white)](https://www.typescriptlang.org/)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-v4-38B2AC?style=flat-square&logo=tailwind-css&logoColor=white)](https://tailwindcss.com)
[![Redis](https://img.shields.io/badge/Redis-Lock%20%26%20Queue-DC382D?style=flat-square&logo=redis&logoColor=white)](https://redis.io)
[![Laravel Reverb](https://img.shields.io/badge/Laravel_Reverb-WebSocket-FF2D20?style=flat-square&logo=laravel&logoColor=white)](https://laravel.com/docs/11.x/reverb)
[![Docker](https://img.shields.io/badge/Docker-Ready-2496ED?style=flat-square&logo=docker&logoColor=white)](https://docker.com)

---

## 📌 Bối Cảnh & Bài Toán Kỹ Thuật

Khi các cụm rạp mở bán vé cho các phim bom tấn (như *Avengers, Nhà Bà Nữ, Taylor Swift: The Eras Tour*), hệ thống thường đối mặt với 2 vấn đề lớn:
1. **Trùng ghế (Double-booking):** Hàng chục người cùng chọn và bấm thanh toán 1 ghế VIP trong cùng 1 giây.
2. **Quá tải Database:** Nếu mỗi lượt bấm chọn ghế đều ghi trực tiếp vào ổ đĩa MySQL, connection pool sẽ nhanh chóng bị cạn kiệt.

### 💡 Giải Pháp Kỹ Thuật Trong CineReserve:
* **Khóa phân tán trên Redis (Atomic Distributed Lock):** Khi người dùng chọn ghế, hệ thống thực hiện lệnh `SET lock:showtime:{id}:seat:{id} {user_id} NX EX 600`. Thao tác diễn ra trên RAM trong ~1-2ms, chặn ngay các request bấm sau mà không cần query xuống DB.
* **Đồng bộ trạng thái ghế qua WebSocket:** Sử dụng **Laravel Reverb**, ngay khi ghế được giữ, sự kiện `SeatHoldingEvent` sẽ được broadcast tới tất cả các client đang xem phòng chiếu đó để chuyển màu ghế sang trạng thái "Đang có người giữ" trong < 50ms.
* **Pessimistic Locking khi Checkout:** Ở bước xác nhận thanh toán, hệ thống sử dụng `SELECT ... FOR UPDATE` trong Database Transaction để đảm bảo tính toàn vẹn (ACID) tuyệt đối trước khi trừ tiền và xuất vé.
* **Tự động nhả ghế (Auto-release):** Nếu sau 10 phút người dùng không thanh toán, Redis TTL tự hết hạn và background worker sẽ giải phóng ghế cho người khác chọn.

---

## 🚀 Các Chức Năng Chính

### 1. Dành Cho Khách Hàng (Customer Portal)
* **Khám phá phim & lịch chiếu:** Lọc phim Đang chiếu / Sắp chiếu, xem trailer YouTube modal, duyệt lịch chiếu theo ngày và theo 30 cụm rạp (CGV, Lotte, BHD, Galaxy, Beta) trên 13 tỉnh thành.
* **Sơ đồ ghế trực quan:** Hiển thị ghế Thường, VIP, Ghế Đôi với cơ chế giữ ghế thời gian thực và đếm ngược 10 phút.
* **Combo Bắp Nước & Voucher:** Tích hợp chọn combo F&B, áp dụng mã giảm giá và tính toán tổng tiền động.
* **Cổng thanh toán:** Hỗ trợ mô phỏng thanh toán VNPAY / MoMo với cơ chế chống callback trùng lặp (Idempotent Webhook).
* **Vé điện tử & Lịch sử:** Nhận vé kèm mã QR định danh, xem lại vé đã mua tại mục "Vé Của Tôi".
* **Đánh giá phim & Tích điểm CinePoints:** Viết nhận xét kèm số sao, tích điểm nâng hạng thành viên (Member, Gold, VIP, Diamond) để đổi quà trực tiếp.

### 2. Dành Cho Nhân Viên Soát Vé (Staff Scanner PWA)
* Truy cập nhanh tại `/staff/scanner`: Bật camera quét mã QR trên vé của khách hoặc nhập mã vé.
* **Kiểm tra hợp lệ:** Kiểm tra đúng ngày, đúng suất chiếu, đúng phòng rạp.
* **Chống gian lận:** Báo động nếu vé đã qua sử dụng (chống quay vòng vé) hoặc đã hết hạn.
* **Cảnh báo độ tuổi:** Tự động hiển thị nhãn tuổi của phim (**P, K, T13, T16, T18** theo quy định Cục Điện Ảnh) để nhân viên đối chiếu CCCD của khách trước khi vào rạp.
* **Hiển thị combo bắp nước:** Giúp quầy vé chuẩn bị đúng phần bắp nước khách đã đặt online.

### 3. Dành Cho Quản Trị Viên (Admin Portal)
* **Dashboard Analytics:** Biểu đồ doanh thu 12 tháng, phân bố thị phần các cụm rạp, thống kê top 5 phim bán chạy nhất và tỷ lệ lấp đầy phòng chiếu.
* **Tạo suất chiếu hàng loạt (Batch Generation):** Cho phép tạo lịch chiếu đồng thời cho nhiều rạp và nhiều khung giờ trong tuần chỉ với 1 thao tác.
* **Quản lý toàn diện:** CRUD Phim (đồng bộ tự động từ TMDb API), Cụm rạp (30 rạp), Phòng chiếu & sơ đồ ghế (70 phòng), Đơn đặt vé, Menu Bắp Nước, Voucher khuyến mãi và Người dùng (RBAC: Admin / Staff / Customer).

---

## 🛠️ Tech Stack & Kiến Trúc

* **Backend:** PHP 8.3, Laravel 11, Laravel Reverb (WebSockets), Redis (Cache & Atomic Locks), MySQL 8.0.
* **Frontend:** Vue 3 (Composition API, `<script setup lang="ts">`), TypeScript, Pinia, Vite, Tailwind CSS v4, Lucide Icons, Chart.js.
* **Kiến trúc mã nguồn:** Service-Repository Pattern, Form Request Validation, API Resources, Typed Composables.
* **Hạ tầng:** Docker Compose (Nginx, PHP-FPM, MySQL, Redis, Reverb, Queue Worker).

```
[ Khách Hàng (Vue 3 SPA) ]            [ Nhân Viên (Staff PWA Scanner) ]
            │                                         │
            │ (REST API)                              │ (Camera QR Scan)
            ▼                                         ▼
[ Nginx Reverse Proxy ] ──────────────────► [ Laravel Reverb (Port 8080) ]
            │                                             ▲
            ▼                                             │ (Broadcasting)
[ Laravel 11 Backend (PHP 8.3) ] ─────────────────────────┘
   ├── MySQL 8.0 (ACID Transactions & Row-level Locks)
   ├── Redis 7.0 (10-min Distributed Seat Locks & Async Queues)
   └── VNPAY / MoMo Payment Simulation (Idempotent Callback)
```

---

## 💻 Hướng Dẫn Cài Đặt

### Chạy bằng Docker Compose (Khuyên dùng)

1. **Clone repository:**
   ```bash
   git clone https://github.com/hiimthien/cinereserve.git
   cd cinereserve
   ```

2. **Cấu hình file môi trường backend:**
   ```bash
   cp backend/.env.example backend/.env
   ```

3. **Khởi động Docker Containers:**
   ```bash
   docker compose up -d --build
   ```

4. **Nạp dữ liệu mẫu (Seeder):**
   ```bash
   docker exec cinereserve-php php artisan key:generate
   docker exec cinereserve-php php artisan migrate --seed
   ```
   > Seeder sẽ tự động nạp 36 phim chuẩn từ TMDb, 30 cụm rạp, 70+ phòng chiếu, 16 tài khoản mẫu và các đơn vé mẫu để xem biểu đồ doanh thu.

5. **Chạy Frontend:**
   ```bash
   cd frontend
   npm install
   npm run dev
   ```

Truy cập hệ thống tại:
* **Frontend:** `http://localhost:5173`
* **Backend API:** `http://localhost/api`

---

## 🔑 Tài Khoản Trải Nghiệm Demo

| Vai trò | Email | Mật khẩu | Mục đích kiểm thử |
|---|---|---|---|
| **Admin** | `admin@cinereserve.com` | `password` | Truy cập Trang Quản Trị `/admin` |
| **Staff** | `staff@cinereserve.com` | `password` | Truy cập Máy Soát Vé QR `/staff/scanner` |
| **Khách Diamond** | `diamond@cinereserve.com` | `password` | Tài khoản có 2,500 điểm CinePoints để test Đổi Quà |
| **Khách Member** | `member@cinereserve.com` | `password` | Tài khoản khách hàng thông thường để test Đặt Vé |

---

## 📄 License
Dự án được phát hành theo giấy phép [MIT License](LICENSE).
