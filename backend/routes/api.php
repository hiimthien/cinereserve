<?php

use App\Http\Controllers\Api\Admin\AdminAnalyticsController;
use App\Http\Controllers\Api\Admin\AdminBookingController;
use App\Http\Controllers\Api\Admin\AdminCinemaController;
use App\Http\Controllers\Api\Admin\AdminMovieController;
use App\Http\Controllers\Api\Admin\AdminRoomController;
use App\Http\Controllers\Api\Admin\AdminShowtimeController;
use App\Http\Controllers\Api\Admin\AdminSnackController;
use App\Http\Controllers\Api\Admin\AdminVoucherController;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\CinemaController;
use App\Http\Controllers\Api\LoyaltyController;
use App\Http\Controllers\Api\MovieController;
use App\Http\Controllers\Api\PaymentWebhookController;
use App\Http\Controllers\Api\SeatController;
use App\Http\Controllers\Api\ShowtimeController;
use App\Http\Controllers\Api\SnackController;
use App\Http\Controllers\Api\TicketCheckInController;
use App\Http\Controllers\Api\VoucherController;
use Illuminate\Support\Facades\Route;

// Auth API (Đăng nhập, Đăng ký, Google Auth, Me)
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/google', [AuthController::class, 'googleAuth']);
Route::get('/auth/me', [AuthController::class, 'me']);
Route::post('/auth/logout', [AuthController::class, 'logout']);

// Loyalty & Membership API (Đổi điểm thưởng lấy Voucher)
Route::get('/loyalty/rewards', [LoyaltyController::class, 'rewardsList']);
Route::post('/loyalty/redeem', [LoyaltyController::class, 'redeem']);

// Staff QR Check-in API (Soát vé nhân viên & chống quét 2 lần)
Route::post('/tickets/check-in', [TicketCheckInController::class, 'checkIn']);
Route::get('/tickets/verify/{code}', [TicketCheckInController::class, 'verify']);

// VNPay Sandbox & Webhook IPN API (HMAC-SHA512 + Idempotency)
Route::post('/payment/vnpay/create-url', [PaymentWebhookController::class, 'createVNPayUrl']);
Route::get('/payment/vnpay/ipn', [PaymentWebhookController::class, 'handleVNPayIpn']);
Route::get('/payment/vnpay/return', [PaymentWebhookController::class, 'handleVNPayReturn']);

// ==========================================
// 📊 ADMIN DASHBOARD API
// ==========================================
Route::prefix('admin')->group(function () {
    // Analytics & Metrics
    Route::get('/analytics', [AdminAnalyticsController::class, 'getAnalytics']);

    // Movies Management CRUD
    Route::get('/movies', [AdminMovieController::class, 'index']);
    Route::post('/movies', [AdminMovieController::class, 'store']);
    Route::put('/movies/{id}', [AdminMovieController::class, 'update']);
    Route::delete('/movies/{id}', [AdminMovieController::class, 'destroy']);

    // Showtimes Management CRUD
    Route::get('/showtimes', [AdminShowtimeController::class, 'index']);
    Route::post('/showtimes', [AdminShowtimeController::class, 'store']);
    Route::post('/showtimes/batch', [AdminShowtimeController::class, 'batchStore']);
    Route::put('/showtimes/{id}', [AdminShowtimeController::class, 'update']);
    Route::delete('/showtimes/{id}', [AdminShowtimeController::class, 'destroy']);


    // Cinemas & Seat Matrix Management
    Route::get('/rooms', [AdminRoomController::class, 'index']);
    Route::get('/rooms/{id}/seats', [AdminRoomController::class, 'getSeats']);
    Route::post('/rooms/{id}/seat-matrix', [AdminRoomController::class, 'updateSeatMatrix']);

    // Snacks & Combos Management CRUD
    Route::get('/snacks', [AdminSnackController::class, 'index']);
    Route::post('/snacks', [AdminSnackController::class, 'store']);
    Route::put('/snacks/{id}', [AdminSnackController::class, 'update']);
    Route::delete('/snacks/{id}', [AdminSnackController::class, 'destroy']);

    // Vouchers & Discounts Management CRUD
    Route::get('/vouchers', [AdminVoucherController::class, 'index']);
    Route::post('/vouchers', [AdminVoucherController::class, 'store']);
    Route::put('/vouchers/{id}', [AdminVoucherController::class, 'update']);
    Route::delete('/vouchers/{id}', [AdminVoucherController::class, 'destroy']);

    // Bookings & Orders Management
    Route::get('/bookings', [AdminBookingController::class, 'index']);
    Route::post('/bookings/{id}/check-in', [AdminBookingController::class, 'checkIn']);
    Route::delete('/bookings/{id}/cancel', [AdminBookingController::class, 'cancel']);
});


// Cinemas API (Lọc theo Rạp & xem phim chiếu tại rạp)
Route::get('/cinemas', [CinemaController::class, 'index']);
Route::get('/cinemas/{id}/showtimes', [CinemaController::class, 'showtimes']);

// Movies API
Route::get('/movies', [MovieController::class, 'index']);
Route::get('/movies/{id}', [MovieController::class, 'show']);

// Snacks / Combos API
Route::get('/snacks', [SnackController::class, 'index']);

// Vouchers & Discounts API
Route::get('/vouchers', [VoucherController::class, 'index']);
Route::post('/vouchers/apply', [VoucherController::class, 'apply']);

// Showtimes & Seats API
Route::get('/showtimes/{id}', [ShowtimeController::class, 'show']);
Route::get('/showtimes/{id}/seats', [ShowtimeController::class, 'getSeats']);

// Seat Holding & Releasing (Real-time Concurrency Locks)
Route::post('/showtimes/{showtimeId}/seats/{seatId}/hold', [SeatController::class, 'hold']);
Route::post('/showtimes/{showtimeId}/seats/{seatId}/release', [SeatController::class, 'release']);

// Bookings & Checkout API
Route::get('/bookings', [BookingController::class, 'index']);
Route::post('/bookings/checkout', [BookingController::class, 'checkout']);
Route::get('/bookings/{code}', [BookingController::class, 'show']);

