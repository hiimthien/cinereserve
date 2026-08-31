# 📝 NHẬT KÝ & LỊCH SỬ TRAO ĐỔI DỰ ÁN CINERESERVE

> **Ngày bắt đầu:** 31/08/2026  
> **Chủ sở hữu:** Cao Lương Thiện (@hiimthien)

---

## 📑 Tóm Tắt Toàn Bộ Phiên Làm Việc

### 1. Phân Tích Kỹ Thuật: Socket trong PHP & Laravel
* **Câu hỏi:** Tại sao PHP phải setup 1 server socket riêng không thể config chung được?
* **Giải đáp cốt lõi:**
  * PHP truyền thống (PHP-FPM/Apache) chạy theo mô hình **Short-lived & Stateless** (Request đến $\rightarrow$ khởi tạo $\rightarrow$ trả kết quả $\rightarrow$ hủy tiến trình).
  * Socket là kết nối **Long-lived & Stateful** (duy trì liên tục kết nối TCP trong RAM, 2 chiều).
  * Nếu giữ kết nối socket trên PHP-FPM thông thường sẽ làm **cạn kiệt Worker Pool** (Starvation) gây sập web và không thể broadcast tin nhắn giữa các tiến trình độc lập.
  * **Với Laravel:** Laravel cung cấp cơ chế Event Broadcasting. Từ Laravel 11+, **Laravel Reverb** được dùng làm WebSocket Server chính chủ, chạy dưới dạng một tiến trình Daemon độc lập (`php artisan reverb:start`).

---

### 2. Tư Vấn CV & Chiến Lược Dự Án Nhảy Việc (Junior 1 Năm Kinh Nghiệm)
* **Phân tích CV của Cao Lương Thiện:**
  * Điểm mạnh: Đã có kinh nghiệm thực chiến E-commerce (CodeIgniter 4), xử lý Webhook TikTok Shop, Redis Queue, Design Patterns (Factory, Strategy), tối ưu MySQL N+1.
  * Điểm còn thiếu: Thị trường tuyển dụng yêu cầu **Laravel** chiếm 80%, cần 1 dự án chứng minh năng lực **Laravel 11+** kết hợp các bài toán khó: **Real-time (WebSocket), Xử lý đồng thời (Race condition/Locking), Caching và Background Jobs**.
* **Đề tài được chọn:** **CineReserve - Hệ thống Đặt vé xem phim Trực tuyến Thời gian thực & Chống Double-booking**.

---

### 3. Thiết Kế UI & Tạo Design System (Google Stitch / AI)
* Đã xây dựng Master Prompt để xuất 5 màn hình chuẩn:
  1. *Design System (Cinematic Immersive Dark)*
  2. *Movie Details & Showtime Selector*
  3. *Interactive Seat Selection Map (4 trạng thái ghế + Countdown Timer)*
  4. *Secure Checkout (VNPay, MoMo, Card)*
  5. *E-Ticket QR Pass (Thẻ vé điện tử có vết cắt)*

---

### 4. Setup Toàn Bộ Project Tại `D:\PJ\cinereserve`
* **Docker Ecosystem:** 7 container (`cinereserve-nginx`, `cinereserve-php`, `cinereserve-reverb`, `cinereserve-queue`, `cinereserve-mysql`, `cinereserve-redis`, `cinereserve-mailpit`).
* **Backend (Laravel 11):** 8 bảng database, seeder 6 phim bom tấn, 3 cụm rạp, 118 ghế, hệ thống `SeatLockingService.php` (Redis lock 10 phút), REST API routes.
* **Frontend (Vue 3 + Vite + Tailwind CSS v4 + Pinia):**
  * `HomeView.vue`: Hero banner carousel tự động chuyển slide, bộ lọc phim đang chiếu/sắp chiếu, tìm kiếm.
  * `MovieDetailView.vue`: Thông tin phim, lịch chiếu theo rạp, modal xem trailer YouTube.
  * `SeatSelectionView.vue`: Sơ đồ ghế 4 màu, vòm màn hình cong, đồng hồ đếm ngược `09:59`.
  * `CheckoutView.vue`: Chọn thêm combo bắp nước, chọn cổng thanh toán.
  * `TicketConfirmationView.vue`: Hiệu ứng pháo hoa, vé điện tử QR code.
  * `MyTicketsView.vue`: Lịch sử vé đã mua.
* **Đã đẩy toàn bộ source code lên GitHub:** `https://github.com/hiimthien/cinereserve`

---

## 🎯 Gợi Ý Nhiệm Vụ Tiếp Tục Cho Ngày Mai:
1. Mở thư mục `D:\PJ\cinereserve` trong VS Code.
2. Bật Docker: `docker compose up -d` hoặc chạy standalone local server.
3. Cài đặt **Laravel Filament** để dựng nhanh trang Quản trị Admin Panel (Thống kê doanh thu, quản lý phim/suất chiếu).
4. Làm thêm màn hình Quét mã QR Check-in vé cho nhân viên rạp.
