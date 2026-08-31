# 🎬 CINERESERVE - TÀI LIỆU TỔNG QUAN DỰ ÁN

> **Dự án:** Hệ thống Đặt vé xem phim Trực tuyến & Xử lý Đồng thời Thời gian thực  
> **Tác giả:** Cao Lương Thiện  
> **Mục tiêu:** Dự án cá nhân Full-stack phục vụ ứng tuyển vị trí **Junior / Mid PHP Full-Stack Developer**  
> **GitHub Repository:** [https://github.com/hiimthien/cinereserve](https://github.com/hiimthien/cinereserve)

---

## 📌 1. Bối Cảnh & Mục Tiêu Kỹ Thuật

Dự án được xây dựng nhằm giải quyết các bài toán hóc búa nhất trong các hệ thống thương mại và bán vé có lượng truy cập đồng thời cao (High-concurrency):
1. **Xử lý tranh chấp ghế (Race Condition & Over-selling):** Khi nhiều người dùng cùng chọn 1 ghế trong cùng 1 mili-giây.
2. **Khóa ghế tạm thời (Temporary Seat Hold):** Giữ ghế trong 10 phút bằng **Redis Atomic Lock & TTL**, tự động giải phóng nếu không thanh toán.
3. **Đồng bộ trạng thái thời gian thực (Real-time WebSockets):** Sử dụng **Laravel Reverb + Laravel Echo** phát sóng sự kiện đổi màu ghế tức thì mà không cần F5.
4. **Xử lý giao dịch & Thanh toán an toàn:** Giao dịch cơ sở dữ liệu ACID kết hợp **Pessimistic Locking (`SELECT ... FOR UPDATE`)** và chống duplicate Webhook (**Idempotency**).
5. **Kiến trúc Container hóa (Docker):** Đóng gói trọn gói toàn bộ hệ sinh thái chạy độc lập chỉ bằng 1 lệnh.

---

## 🛠️ 2. Tech Stack & Kiến Trúc Hệ Thống

| Tầng | Công nghệ sử dụng | Vai trò & Mục đích |
| :--- | :--- | :--- |
| **Backend** | **Laravel 11 (PHP 8.3)** | RESTful API, Service-Repository Pattern, Eloquent ORM, DB Transactions |
| **WebSocket** | **Laravel Reverb** | Máy chủ WebSocket chính chủ chạy ngầm 24/7 trên cổng `8080` |
| **Caching & Queue** | **Redis 7.0** | Quản lý khóa giữ ghế 10 phút, hàng đợi xử lý ngầm (Queue Worker) |
| **Database** | **MySQL 8.0** | Quản lý quan hệ dữ liệu: Phim, Rạp, Phòng, Ghế, Suất chiếu, Vé, Giao dịch |
| **Frontend** | **Vue.js 3 + TypeScript** | Composition API (`<script setup>`), Single Page Application |
| **State Management** | **Pinia** | Quản lý State giỏ vé, đồng hồ đếm ngược `09:59`, thông tin phim & suất chiếu |
| **Styling** | **Tailwind CSS v4** | Dark Mode Cinema phong cách Netflix / Apple TV, hiệu ứng vòm màn hình cong phát sáng |
| **Web Server** | **Nginx (Alpine)** | Reverse Proxy điều hướng Request API sang PHP-FPM và WebSocket sang Reverb |
| **Mail Server** | **Mailpit** | Giả lập gửi email xác nhận vé điện tử kèm mã QR local trên cổng `8025` |

---

## 🖥️ 3. Danh Sách Màn Hình & Trải Nghiệm Người Dùng (UI Flow)

1. **Trang chủ (`HomeView.vue`):**
   * **Hero Carousel:** Tự động chuyển đổi các phim bom tấn hot (*Dune 2, Oppenheimer, Deadpool & Wolverine, Spider-Man*).
   * **Nút xem Trailer:** Bật popup xem trực tiếp video trailer YouTube độ phân giải cao.
   * **Tabs lọc:** Phim đang chiếu (Now Showing) và Phim sắp chiếu (Coming Soon).
   * **Tìm kiếm:** Tìm nhanh theo tên phim và thể loại.
2. **Chi tiết phim & Lịch chiếu (`MovieDetailView.vue`):**
   * Hiển thị điểm IMDb, thời lượng, tóm tắt nội dung, danh sách rạp (*Landmark 81, Moonlight Thủ Đức*).
   * Thanh trượt ngang chọn ngày chiếu (5 ngày liên tiếp).
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
   * Tùy chọn thêm combo bắp rang bơ Caramel, nước ngọt Coca-Cola.
   * Lựa chọn phương thức: Credit Card, Ví MoMo, VNPAY QR.
5. **Xác nhận đặt vé & Vé điện tử (`TicketConfirmationView.vue`):**
   * Hiệu ứng pháo hoa chúc mừng (Confetti).
   * Thẻ vé điện tử thiết kế vết cắt răng cưa (Ticket Notches), mã QR Code lớn, nút tải vé PDF.
6. **Vé của tôi (`MyTicketsView.vue`):**
   * Danh sách vé đã đặt và lịch sử giao dịch.

---

## ⚡ 4. Hướng Dẫn Chạy Dự Án

### Cách 1: Chạy bằng Docker (Khuyên dùng)
```bash
cd D:\PJ\cinereserve
docker compose up -d
docker compose exec php php artisan migrate:fresh --seed
```
* **Frontend:** `http://localhost:5173` (chạy `cd frontend && npm run dev`)
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

## 📅 5. Kế Hoạch Tiếp Tục (Roadmap Cho Ngày Mai)

- [ ] **Tích hợp Laravel Filament:** Tạo trang Quản trị Admin Panel (Thêm phim mới, quản lý suất chiếu, xem biểu đồ doanh thu bán vé).
- [ ] **Tính năng Quét mã QR Soát vé (Staff Scanner):** Trang dành cho nhân viên rạp mở camera quét QR code từ vé của khách để đổi trạng thái sang `CHECKED_IN`.
- [ ] **Viết Unit & Feature Tests (Pest / PHPUnit):** Test case Race Condition khi 2 user cùng chọn 1 ghế, test case thanh toán thành công.
- [ ] **Quay Video/GIF Demo:** Gắn vào `README.md` để khoe trên CV & LinkedIn.
