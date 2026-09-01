# 🎬 CINERESERVE - TÀI LIỆU TỔNG QUAN DỰ ÁN

> **Dự án:** Hệ thống Đặt vé xem phim Trực tuyến & Xử lý Đồng thời Thời gian thực  
> **Tác giả:** Cao Lương Thiện (@hiimthien)  
> **Mục tiêu:** Dự án cá nhân Full-stack phục vụ ứng tuyển vị trí **Junior / Mid PHP Full-Stack Developer**  
> **Kho lưu trữ GitHub:** [https://github.com/hiimthien/cinereserve](https://github.com/hiimthien/cinereserve)  
> **Cập nhật lần cuối:** 01/09/2026

---

## 📌 1. Bối Cảnh & Mục Tiêu Kỹ Thuật

Dự án được xây dựng nhằm giải quyết các bài toán hóc búa nhất trong các hệ thống thương mại và bán vé có lượng truy cập đồng thời cao (High-concurrency):
1. **Xử lý tranh chấp ghế (Race Condition & Over-selling):** Khi nhiều người dùng cùng chọn 1 ghế trong cùng 1 mili-giây.
2. **Khóa ghế tạm thời (Temporary Seat Hold):** Giữ ghế trong 10 phút bằng **Redis Atomic Lock & TTL**, tự động giải phóng nếu không thanh toán.
3. **Đồng bộ trạng thái thời gian thực (Real-time WebSockets):** Sử dụng **Laravel Reverb + Laravel Echo** phát sóng sự kiện đổi màu ghế tức thì mà không cần F5.
4. **Xử lý giao dịch & Thanh toán an toàn:** Giao dịch cơ sở dữ liệu ACID kết hợp **Pessimistic Locking (`SELECT ... FOR UPDATE`)** và chống duplicate Webhook (**Idempotency**).
5. **Kiến trúc Layered Chuẩn Quốc Tế:** Thin Controllers, Form Request Validation, API Resources Transformer, Service-Repository Pattern.
6. **Kiến trúc Container hóa (Docker):** Đóng gói trọn gói toàn bộ hệ sinh thái chạy độc lập chỉ bằng 1 lệnh.

---

## 🛠️ 2. Tech Stack & Kiến Trúc Hệ Thống

| Tầng | Công nghệ sử dụng | Vai trò & Mục đích |
| :--- | :--- | :--- |
| **Backend** | **Laravel 11 (PHP 8.3)** | RESTful API, Service-Repository Pattern, Form Request Validation, Eloquent ORM, DB Transactions |
| **WebSocket** | **Laravel Reverb** | Máy chủ WebSocket chính chủ chạy ngầm 24/7 trên cổng `8080` |
| **Caching & Queue** | **Redis 7.0** | Quản lý khóa giữ ghế 10 phút, hàng đợi xử lý ngầm (Queue Worker) |
| **Database** | **MySQL 8.0** | Quản lý quan hệ dữ liệu: Phim, Rạp, Phòng, Ghế, Suất chiếu, Vé, Bắp nước, Voucher, Giao dịch |
| **Frontend** | **Vue.js 3 + TypeScript** | Composition API (`<script setup lang="ts">`), Single Page Application |
| **Component System** | **Atomic Base Components** | `BaseModal`, `BasePagination`, `BaseBadge`, `BaseSelect`, `BaseInput`, `BaseButton`, `BaseSpinner` |
| **State Management** | **Pinia** | Quản lý State giỏ vé, đồng hồ đếm ngược `09:59`, thông tin phim & suất chiếu |
| **Styling** | **Tailwind CSS v4** | Dark Mode Cinema phong cách Netflix / Apple TV, hiệu ứng vòm màn hình cong phát sáng |
| **Web Server** | **Nginx (Alpine)** | Reverse Proxy điều hướng Request API sang PHP-FPM và WebSocket sang Reverb |
| **Mail Server** | **Mailpit** | Giả lập gửi email xác nhận vé điện tử kèm mã QR local trên cổng `8025` |

---

## 🖥️ 3. Danh Sách Màn Hình & Trải Nghiệm Người Dùng (UI Flow)

### A. Phân Hệ Dành Cho Khách Hàng (Client Portal):
1. **Trang chủ (`HomeView.vue`):**
   * **Hero Carousel:** Tự động chuyển đổi các phim bom tấn hot (*Dune 2, Oppenheimer, Deadpool & Wolverine, Spider-Man*).
   * **Nút xem Trailer:** Bật popup xem trực tiếp video trailer YouTube độ phân giải cao.
   * **Tabs lọc:** Phim đang chiếu (Now Showing), Phim sắp chiếu (Coming Soon), Suất chiếu sớm (Sneak Show).
   * **Tìm kiếm:** Tìm nhanh theo tên phim và thể loại.
2. **Chi tiết phim & Lịch chiếu (`MovieDetailView.vue`):**
   * Hiển thị điểm IMDb, thời lượng, tóm tắt nội dung, danh sách rạp (*Landmark 81, Moonlight Thủ Đức*).
   * Thanh trượt ngang chọn ngày chiếu (14 ngày liên tiếp).
   * Danh sách khung giờ chiếu với nhãn định dạng phòng (*IMAX Laser, Dolby Atmos*).
3. **Sơ đồ chọn ghế Real-time (`SeatSelectionView.vue`):**
   * Vòm màn hình chiếu phát sáng (Curved Luminous Screen).
   * Ma trận ghế phân cấp: Ghế thường, Ghế VIP viền vàng, Ghế đôi (Couple Sofa).
   * **4 trạng thái ghế trực quan:** 
     * ⚪ **Trắng (Available):** Ghế trống.
     * 🟢 **Xanh lá (Selected):** Ghế bạn đang chọn.
     * 🟡 **Vàng (Holding):** Người khác đang giữ chỗ (kèm icon ổ khóa & nhấp nháy).
     * 🔴 **Đỏ (Booked):** Ghế đã bán.
   * **Thanh Bottom Bar cố định:** Đồng hồ đếm ngược `⏳ 09:59`, tổng tiền vé, nút tiến hành thanh toán.
4. **Thanh toán & Thêm Combo (`CheckoutView.vue`):**
   * Tùy chọn thêm combo bắp rang bơ Caramel, nước ngọt Coca-Cola, snack nachos.
   * Nhập mã giảm giá Voucher Khuyến mãi tính toán giảm trừ trực tiếp.
   * Lựa chọn phương thức: Credit Card, Ví MoMo, VNPAY QR.
5. **Xác nhận đặt vé & Vé điện tử (`TicketConfirmationView.vue`):**
   * Hiệu ứng pháo hoa chúc mừng (Confetti).
   * Thẻ vé điện tử thiết kế vết cắt răng cưa (Ticket Notches), mã QR Code lớn, nút lưu ảnh QR vé về máy.
6. **Vé của tôi (`MyTicketsView.vue`):**
   * Danh sách vé đã mua, bộ lọc theo phim, theo rạp, tìm kiếm mã vé và xuất vé điện tử.
   * Bảo mật thông tin chỉ hiển thị vé của tài khoản đang đăng nhập.

### B. Phân Hệ Quản Trị Hệ Thống (CineAdmin Portal - `/admin`):
1. **Tổng Quan & Doanh Thu (`AdminDashboardView.vue`):**
   * Biểu đồ doanh thu 4 chiều động (Hôm nay 24h, 7 ngày qua, 30 ngày, Năm nay 12 tháng).
   * Bộ lọc đa chiều theo Cụm rạp và Phim.
   * Tỷ lệ lấp đầy ghế (Occupancy rate) và Top phim doanh thu cao nhất.
2. **Quản Lý Phim Chiếu Rạp (`AdminMoviesView.vue`):**
   * CRUD phim, phân loại trạng thái (*Đang chiếu, Sắp chiếu, Suất chiếu sớm*), tìm kiếm, phân trang.
3. **Quản Lý Lịch & Suất Chiếu Theo Phim (`AdminShowtimesView.vue`):**
   * Nhóm lịch chiếu theo từng bộ Phim (Group by Movie).
   * Modal xem chi tiết toàn bộ suất chiếu của phim trên các rạp cả nước.
   * **⚡ Tạo Hàng Loạt Nhiều Rạp (Batch Multi-Cinema):** Lên lịch cho nhiều rạp và nhiều ngày cùng lúc chỉ với 1 click.
   * Tự động điền ngày khởi chiếu cho phim sắp chiếu.
   * **Cấu hình bảng giá riêng từng loại ghế:** Ghế Thường (Standard), Ghế VIP (+15k), Ghế Đôi (2x).
   * Chỉnh sửa & xóa suất chiếu linh hoạt.
4. **Quản Lý Bắp Nước & Combo (`AdminSnacksView.vue`):** CRUD các gói combo bắp nước rạp phim.
5. **Quản Lý Mã Giảm Giá & Voucher (`AdminVouchersView.vue`):** CRUD mã khuyến mãi theo % hoặc số tiền cố định.
6. **Quản Lý Toàn Bộ Đơn Đặt Vé (`AdminBookingsView.vue`):** Tra cứu mọi đơn vé, soát vé check-in nhanh hoặc hủy vé.

---

## ⚡ 4. Hướng Dẫn Chạy Dự Án

### Cách 1: Chạy bằng Docker (Khuyên dùng)
```bash
cd D:\PJ\cinereserve
docker compose up -d
docker compose exec php php artisan migrate:fresh --seed
```
* **Client Frontend:** `http://localhost:5173`
* **Admin Portal:** `http://localhost:5173/admin`
* **Backend API:** `http://localhost:8000/api/movies`
* **WebSocket Reverb:** `ws://localhost:8080`
* **Mailpit (Email inbox test):** `http://localhost:8025`

### Cách 2: Chạy trực tiếp (Local Standalone)
```bash
# Terminal 1: Backend
cd D:\PJ\cinereserve\backend
php artisan serve --port=8000

# Terminal 2: Frontend
cd D:\PJ\cinereserve\frontend
npm run dev
```

---

## 📅 5. Trạng Thái Hoàn Thiện & Kế Hoạch Tiếp Theo

### ✅ Đã Hoàn Thành (Completed):
- [x] Kiến trúc Core Real-time Socket (Laravel Reverb + Echo).
- [x] Khóa ghế chống trùng lặp Redis Atomic Lock (10 phút) + Pessimistic Lock `lockForUpdate()`.
- [x] Toàn bộ Client Booking Flow (Trang chủ, Chi tiết phim, Chọn ghế, Thanh toán, Vé điện tử).
- [x] Trang Vé Của Tôi (`/my-tickets`) kèm tải ảnh QR vé trực tiếp và bảo mật tài khoản.
- [x] CineAdmin Portal hoàn chỉnh: Dashboard doanh thu đa chiều, Quản lý phim, Quản lý suất chiếu nhóm theo phim (Batch creator + Giá từng ghế), Quản lý bắp nước, Quản lý voucher, Quản lý toàn bộ vé.
- [x] Tái cấu trúc chuẩn Layered Architecture: Form Requests, API Resources, Atomic Base Components.
- [x] Chuẩn hóa 100% tỉ giá trong cơ sở dữ liệu sang VNĐ (`85.000 đ - 295.000 đ`).

### ⏳ Kế Hoạch Tiếp Tục (Roadmap & Refactoring):
- [ ] **Tái Cấu Trúc Toàn Diện Backend & Frontend:** Chi tiết kế hoạch tại [REFACTORING_ROADMAP.md](file:///d:/PJ/cinereserve/REFACTORING_ROADMAP.md) (Chuyển sang Thin Controllers, Service-Repository Pattern, Custom Composables và tách nhỏ sub-components).
- [ ] **Tính năng Quét mã QR Soát vé (Staff QR Scanner):** Trang `/admin/scanner` mở Camera trên thiết bị di động quét trực tiếp mã QR trên vé của khách để Check-in đổi trạng thái sang `checked_in`.
- [ ] **Tích hợp Cổng Thanh Toán Thực Tế (VNPay Sandbox / MoMo Sandbox Webhook Idempotency):** Xử lý IPN Webhook tự động cập nhật đơn vé thành công khi khách thanh toán qua QR Ngân hàng / MoMo.
- [ ] **Viết Bộ Test Tự Động (Pest / PHPUnit):** Viết test case Race Condition khi 10 request đồng thời giữ cùng 1 ghế qua Redis Lock.
- [ ] **Quay Video / GIF Demo & Cập nhật README:** Chuẩn bị tư liệu hoàn chỉnh để bổ sung vào CV xin việc và Showcase LinkedIn.

