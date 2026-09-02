# CineReserve

Hệ thống đặt vé xem phim trực tuyến thời gian thực (Real-time Cinema Booking System), tập trung giải quyết bài toán **Concurrency** và **Race Condition khi giữ ghế** trong các đợt mở bán vé cao điểm.

> **Lưu ý:** Đây là dự án cá nhân phục vụ mục đích học tập và xây dựng portfolio kỹ thuật. Các cổng thanh toán (VNPAY, MoMo) được triển khai ở chế độ mô phỏng / sandbox.

---

## Điểm nhấn kỹ thuật (Technical Highlights)

- **Xử lý Race Condition & Giữ ghế 2 lớp (Two-Phase Locking Strategy):**
  - **Lớp 1 (RAM Lock):** Khi người dùng click chọn ghế, backend thực hiện lệnh Redis `SET lock:showtime:{id}:seat:{id} {session_id} NX EX 600`. Thao tác phản hồi trong ~1-2ms, chặn ngay các request đồng thời từ user khác mà không tạo tải I/O lên MySQL.
  - **Lớp 2 (Pessimistic Lock at Checkout):** Tại bước xác nhận thanh toán, hệ thống mở Database Transaction và khóa bản ghi bằng `SELECT ... FOR UPDATE` trên bảng `showtime_seats`. Cách tiếp cận này đảm bảo tính toàn vẹn dữ liệu (ACID) tuyệt đối trước khi trừ tiền và xuất vé, loại bỏ hoàn toàn rủi ro double-booking nếu Redis key bị trễ hạn.
- **Đồng bộ sơ đồ ghế Real-time (Laravel Reverb):**
  - Trạng thái ghế (`holding`, `booked`, `available`) được phát qua WebSocket channel `showtime.{id}`. Các client đang mở cùng phòng chiếu sẽ cập nhật màu ghế tức thì mà không cần polling API.
- **Tự động giải phóng ghế hết hạn (TTL-based Expiration):**
  - Ghế giữ tạm có TTL 10 phút trên Redis. Sau thời gian này, lock tự hủy; cron job định kỳ quét và broadcast sự kiện giải phóng ghế về các client.
- **Idempotent Webhook Callback:**
  - Endpoint xử lý IPN/Webhook từ cổng thanh toán kiểm tra mã giao dịch và trạng thái đơn hàng để chống duplicate callback do network timeout hoặc retry từ phía payment gateway.
- **Kiến trúc phân lớp chuẩn mực (Layered Architecture):**
  - **Backend (Laravel 11, PHP 8.3):** Áp dụng Service-Repository Pattern. Controller mỏng, 100% validation chuyển về Form Request, output định dạng qua API Resources, business logic tập trung tại Service layer.
  - **Frontend (Vue 3, TypeScript):** Composition API (`<script setup lang="ts">`), quản lý state tập trung qua Pinia stores, Tailwind CSS v4, hằng số và kiểu dữ liệu quản lý tập trung.

---

## Kiến trúc hệ thống

```text
[ Vue 3 Client (SPA) ]               [ Staff PWA Scanner ]
         │                                    │
         │ HTTP / REST API                    │ QR Camera Scan
         ▼                                    ▼
[ Nginx Reverse Proxy ] ──────────────► [ Laravel Reverb (WebSocket) ]
         │                                       ▲
         ▼                                       │ Broadcast Event
[ Laravel 11 Backend (PHP 8.3) ] ────────────────┘
   ├── MySQL 8.0  ──► Pessimistic Lock (SELECT FOR UPDATE) & DB Transactions
   ├── Redis 7.0  ──► Distributed Lock (SET NX EX 600) & Async Queues
   └── Sandbox Payment ──► Idempotent Webhook Verification
```

---

## Tính năng chính theo vai trò

### 1. Khách hàng (Customer)
- **Duyệt phim & Lịch chiếu:** Xem phim đang chiếu/sắp chiếu, trailer modal, lọc theo 30 cụm rạp tại 13 tỉnh thành.
- **Sơ đồ ghế Real-time:** Phân loại ghế Standard, VIP, Couple; đếm ngược 10 phút giữ chỗ.
- **Combo bắp nước & Voucher:** Modal chọn F&B, dải chọn nhanh 1-click voucher từ ví cá nhân, áp dụng mã giảm giá tự động.
- **Vé điện tử & QR Code:** Nhận vé định danh qua email và lưu trong trang "Vé Của Tôi".
- **Loyalty & Membership:** Tích lũy CinePoints sau mỗi đơn đặt vé, nâng hạng thành viên (Member, VIP, Diamond) và đổi điểm lấy voucher ưu đãi.
- **Bảo mật tài khoản:** Đăng nhập thông thường, Google OAuth, khôi phục mật khẩu qua mã OTP 6 số gửi về email, đổi mật khẩu.

### 2. Nhân viên soát vé (Staff Portal — `/staff/scanner`)
- **Quét vé QR:** Sử dụng camera thiết bị hoặc nhập mã để kiểm tra vé vào cổng.
- **Chống gian lận (Anti-fraud):** Cảnh báo tức thì nếu vé chưa thanh toán, vé sai cụm rạp, vé quá hạn suất chiếu hoặc vé đã quét trước đó.
- **Kiểm tra độ tuổi:** Hiển thị nhãn tuổi theo quy định Cục Điện Ảnh (P, K, T13, T16, T18) để đối chiếu giấy tờ tùy thân.

### 3. Quản trị viên (Admin Portal — `/admin`)
- **Analytics Dashboard:** Thống kê doanh thu, tỷ lệ lấp đầy rạp, phân bổ thị phần theo thương hiệu rạp và top phim bán chạy.
- **Tạo suất chiếu hàng loạt (Batch Generator):** Sinh lịch chiếu tự động cho nhiều rạp và nhiều khung giờ trong tuần.
- **Quản lý dữ liệu:** CRUD Phim (đồng bộ TMDb API), Cụm rạp, Phòng chiếu & Sơ đồ ghế (ma trận ghế tùy biến), Đơn đặt vé, Voucher và Phân quyền người dùng (Admin / Staff / Customer).

---

## Tech Stack

| Thành phần | Công nghệ |
|---|---|
| **Backend** | PHP 8.3, Laravel 11, Laravel Sanctum, Laravel Reverb (WebSockets) |
| **Database & Cache** | MySQL 8.0, Redis 7.0 |
| **Frontend** | Vue 3 (Composition API), TypeScript, Pinia, Vite, Tailwind CSS v4 |
| **Hạ tầng & Devops** | Docker, Docker Compose, Nginx, Mailpit |

---

## Hướng dẫn cài đặt cục bộ

### Yêu cầu môi trường
- Docker & Docker Compose
- Node.js >= 18 (nếu chạy frontend ngoài container)

### Các bước khởi chạy

1. **Clone repository:**
   ```bash
   git clone https://github.com/hiimthien/cinereserve.git
   cd cinereserve
   ```

2. **Cấu hình môi trường Backend:**
   ```bash
   cp backend/.env.example backend/.env
   ```

3. **Khởi động Docker containers:**
   ```bash
   docker compose up -d --build
   ```

4. **Khởi tạo database & nạp dữ liệu mẫu (Seeder):**
   ```bash
   docker exec cinereserve-php php artisan key:generate
   docker exec cinereserve-php php artisan migrate --seed
   ```
   *Seeder nạp sẵn danh sách phim TMDb, 30 cụm rạp, phòng chiếu, tài khoản test và dữ liệu doanh thu mẫu.*

5. **Chạy Frontend:**
   ```bash
   cd frontend
   npm install
   npm run dev
   ```

Hệ thống sẵn sàng tại:
- **Frontend:** `http://localhost:5173`
- **Backend API:** `http://localhost:8000/api` (hoặc qua Nginx port 80)
- **Mailpit (Xem email test/OTP):** `http://localhost:8025`

---

## Tài khoản thử nghiệm (Demo Accounts)

| Vai trò | Email | Mật khẩu | Quyền hạn / Mục đích test |
|---|---|---|---|
| **Admin** | `admin@cinereserve.com` | `password` | Toàn quyền quản trị tại `/admin` |
| **Staff** | `staff@cinereserve.com` | `password` | Soát vé QR tại `/staff/scanner` |
| **Diamond Member** | `diamond@cinereserve.com` | `password` | Khách VIP (sẵn 2,500 điểm để test đổi voucher) |
| **Standard Member** | `member@cinereserve.com` | `password` | Khách hàng thông thường đặt vé |

---

## Giới hạn hiện tại & Hướng phát triển (Limitations & Roadmap)

- **Cổng thanh toán:** Hiện sử dụng luồng mô phỏng; có thể tích hợp SDK thực tế của VNPAY/MoMo khi có merchant credentials.
- **Seat Map Scale:** Đang hỗ trợ tốt cho phòng chiếu dưới 300 ghế; với quy mô sân khấu/stadium lớn hơn, cần chuyển sang Canvas/WebGL rendering thay vì DOM nodes.
- **Distributed Tracing:** Dự kiến bổ sung OpenTelemetry để theo dõi độ trễ giữa các layer (Client ➔ Reverb ➔ Redis ➔ MySQL).

---

## License

Dự án được phân phối dưới giấy phép [MIT License](LICENSE).
