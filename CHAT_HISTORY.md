# 📝 NHẬT KÝ & LỊCH SỬ TRAO ĐỔI DỰ ÁN CINERESERVE

> **Ngày bắt đầu:** 31/08/2026  
> **Ngày cập nhật gần nhất:** 01/09/2026  
> **Chủ sở hữu:** Cao Lương Thiện (@hiimthien)  
> **Kho lưu trữ:** [https://github.com/hiimthien/cinereserve](https://github.com/hiimthien/cinereserve)

---

## 📑 Phiên 1: Khởi Tạo Hệ Thống & Core Real-time (31/08/2026)

1. **Phân tích kỹ thuật Socket & Event-driven trong Laravel:** Giải quyết bài toán cạn kiệt Worker Pool trên PHP-FPM thông qua tiến trình Daemon **Laravel Reverb** độc lập.
2. **Tư vấn chiến lược CV ứng tuyển Junior/Mid Developer:** Tập trung vào các thế mạnh khó: *Xử lý đồng thời (Race Condition), Khóa ghế Redis Atomic Lock 10 phút, Caching, Event Broadcasting và Idempotency*.
3. **Setup toàn bộ hệ sinh thái Docker:** 7 Container (`cinereserve-nginx`, `cinereserve-php`, `cinereserve-reverb`, `cinereserve-queue`, `cinereserve-mysql`, `cinereserve-redis`, `cinereserve-mailpit`).
4. **Xây dựng Client Booking Flow (Vue 3 + Tailwind v4 + Pinia):** Trang chủ Carousel, Chi tiết phim & trailer, Sơ đồ chọn ghế cong phát sáng 4 màu thời gian thực, Trang thanh toán & chọn combo bắp nước, Trang xuất vé điện tử QR Code với vết cắt răng cưa (Ticket Notches).

---

## 📑 Phiên 2: Nâng Cấp Quản Trị CineAdmin & Chuẩn Hóa Skill (01/09/2026)

### 1. Sửa lỗi Giao diện Khách hàng & Vé Của Tôi (`/my-tickets`):
* Căn chỉnh icon và text trên `BaseButton.vue` chuẩn hàng ngang.
* Tích hợp tính năng **Lưu Ảnh Mã QR Vé** trực tiếp (`fetch -> Blob -> <a> download`) với filename `CineReserve_CR-xxxxxx_QRCode.png` kèm spinner loading.
* **Bảo mật dữ liệu vé:** Phân tách rõ ràng giữa khách đã đăng nhập và khách vãng lai (Guest). Khách vãng lai có thẻ hướng dẫn *"Đăng Nhập Ngay"* thân thiện.

### 2. Xây dựng Cổng Quản Trị Hệ Thống CineAdmin (`/admin`):
* **📊 Tổng Quan & Doanh Thu (`AdminDashboardView.vue`):** Phân tích doanh thu 4 chiều động (Hôm nay 24h, 7 ngày qua, 30 ngày, Năm nay 12 tháng), bộ lọc đa chiều theo Rạp & Phim, biểu đồ Top phim ăn khách và tỷ lệ lấp đầy ghế.
* **🎬 Quản Lý Phim (`AdminMoviesView.vue`):** Thêm, sửa, xóa phim chiếu rạp, hỗ trợ phân loại 3 trạng thái (*Đang chiếu, Sắp chiếu, Suất chiếu sớm*), tìm kiếm, phân trang và modal 2 cột rộng rãi `maxWidth="3xl"`.
* **🎟️ Quản Lý Lịch & Suất Chiếu Theo Phim (`AdminShowtimesView.vue`):**
  * Nhóm suất chiếu theo từng bộ Phim (Group by Movie) kèm thanh Status Tabs trực quan (*Tất Cả, Đang Chiếu, Suất Chiếu Sớm, Sắp Khởi Chiếu*).
  * Popup chi tiết xem toàn bộ lịch chiếu của bộ phim đó trên toàn quốc kèm nút Sửa/Xóa từng suất.
  * **⚡ Tạo Hàng Loạt Nhiều Rạp (Batch Multi-Cinema):** Cho phép chọn đồng loạt nhiều rạp (hoặc *Chọn tất cả*), chọn số ngày liên tiếp và nhiều khung giờ chiếu trong ngày chỉ với 1 click.
  * **Tự động điền ngày khởi chiếu (Auto-Prefill Release Date)** khi lên lịch cho phim sắp chiếu.
  * **Tùy chỉnh giá vé riêng theo 3 loại ghế:** *Ghế Thường (Standard), Ghế VIP (+15.000đ), Ghế Đôi/Couple (2x)*.
  * Sửa lỗi dropdown phòng chiếu liên kết trực tiếp với từng cụm rạp.
* **🍿 Quản Lý Bắp Nước & Combo (`AdminSnacksView.vue`):** CRUD combo bắp rang bơ, nước ngọt, snack nachos kèm phân loại và giá bán.
* **🏷️ Quản Lý Mã Giảm Giá & Voucher (`AdminVouchersView.vue`):** CRUD voucher khuyến mãi, thiết lập % giảm giá hoặc giảm tiền mặt cố định, số lượt dùng tối đa và hạn sử dụng.
* **🎫 Quản Lý Toàn Bộ Đơn Đặt Vé Toàn Quốc (`AdminBookingsView.vue`):** Tra cứu mọi giao dịch đặt vé của khách hàng trên toàn bộ các cụm rạp, hỗ trợ nút **Soát Vé Nhanh** và **Hủy Vé**.

### 3. Chuẩn Hóa Dữ Liệu Tỉ Giá VNĐ Trong MySQL & Seeders:
* Chuyển đổi toàn bộ 60 suất chiếu cũ dính giá USD (`$9.50`, `$11.00`, `$12.50`) sang tỉ giá Việt Nam chuẩn (`85.000 đ - 295.000 đ`).
* Viết [`FixUsdPricesSeeder.php`](file:///d:/PJ/cinereserve/backend/database/seeders/FixUsdPricesSeeder.php) và tích hợp vào [`DatabaseSeeder.php`](file:///d:/PJ/cinereserve/backend/database/seeders/DatabaseSeeder.php).

### 4. Tái Cấu Trúc Toàn Bộ Mã Nguồn Chuẩn Skill & Rules:
* **Backend (Laravel 11, PHP 8.3):**
  * Sử dụng Form Request Validation (`AdminMovieRequest`, `AdminShowtimeRequest`, `AdminSnackRequest`, `AdminVoucherRequest`).
  * Sử dụng API Resources (`MovieResource`, `ShowtimeResource`, `SnackResource`, `VoucherResource`, `BookingResource`).
  * Cập nhật `SeatLockingService.php` tính giá chính xác theo giá ghế tùy chỉnh của từng suất chiếu.
* **Frontend (Vue 3, TypeScript, Pinia):**
  * Xây dựng bộ thư viện **Base Components** chuẩn Atomic: `BaseModal`, `BasePagination`, `BaseBadge`, `BaseSelect`, `BaseInput`, `BaseButton`, `BaseSpinner`.
  * Nâng cấp `BaseModal.vue` hỗ trợ `zIndex` đa tầng (Modal cha 50, Modal con 60), bố cục 2 cột thông minh và **Sticky Footer Bar cố định** giúp các nút thao tác luôn hiển thị rõ ràng, không bị cuộn trang.

---

## ⏳ Những Phần Chưa Hoàn Thiện & Kế Hoạch Tiếp Theo

- [ ] **Máy Quét Soát Vé QR Dành Cho Nhân Viên Rạp (Staff QR Scanner):** Trang `/admin/scanner` mở Camera trên thiết bị di động quét trực tiếp mã QR trên vé của khách để Check-in đổi trạng thái thành `checked_in`.
- [ ] **Tích hợp Cổng Thanh Toán Thực Tế (VNPay Sandbox / MoMo Sandbox Webhook Idempotency):** Xử lý IPN Webhook tự động cập nhật đơn vé thành công khi khách thanh toán trên App Ngân hàng / MoMo.
- [ ] **Viết Bộ Test Tự Động (Pest / PHPUnit):** Viết test case Race Condition khi 10 request đồng thời giữ cùng 1 ghế qua Redis Lock.
- [ ] **Quay Video / GIF Demo & Cập nhật README:** Chuẩn bị tư liệu hoàn chỉnh để bổ sung vào CV xin việc và Showcase LinkedIn.
