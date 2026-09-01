<?php

use App\Http\Controllers\Api\Admin\AdminAnalyticsController;
use App\Http\Controllers\Api\Admin\AdminBookingController;
use App\Http\Controllers\Api\Admin\AdminCinemaController;
use App\Http\Controllers\Api\Admin\AdminMovieController;
use App\Http\Controllers\Api\Admin\AdminRoomController;
use App\Http\Controllers\Api\Admin\AdminShowtimeController;
use App\Http\Controllers\Api\Admin\AdminSnackController;
use App\Http\Controllers\Api\Admin\AdminUserController;
use App\Http\Controllers\Api\Admin\AdminVoucherController;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\CinemaController;
use App\Http\Controllers\Api\LoyaltyController;
use App\Http\Controllers\Api\MovieController;
use App\Http\Controllers\Api\MovieReviewController;
use App\Http\Controllers\Api\PaymentWebhookController;
use App\Http\Controllers\Api\SeatController;
use App\Http\Controllers\Api\ShowtimeController;
use App\Http\Controllers\Api\SnackController;
use App\Http\Controllers\Api\TicketCheckInController;
use App\Http\Controllers\Api\VoucherController;
use Illuminate\Support\Facades\Route;

// ==========================================
// 🔐 AUTH & IDENTITY APIS (Rate Limited)
// ==========================================
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:auth');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:auth');
    Route::post('/google', [AuthController::class, 'googleAuth'])->middleware('throttle:auth');
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

// ==========================================
// 🎁 LOYALTY & MEMBERSHIP APIS
// ==========================================
Route::prefix('loyalty')->group(function () {
    Route::get('/rewards', [LoyaltyController::class, 'rewardsList']);
    Route::post('/redeem', [LoyaltyController::class, 'redeem']);
});

// ==========================================
// 🎫 STAFF QR CHECK-IN APIS (Role Staff/Admin Protected & High-Speed Throttle)
// ==========================================
Route::prefix('tickets')->middleware(['role:staff,admin', 'throttle:staff_scan'])->group(function () {
    Route::post('/check-in', [TicketCheckInController::class, 'checkIn']);
    Route::get('/verify/{code}', [TicketCheckInController::class, 'verify']);
});

// ==========================================
// 💳 VNPAY PAYMENT WEBHOOK & IPN APIS
// ==========================================
Route::prefix('payment/vnpay')->group(function () {
    Route::post('/create-url', [PaymentWebhookController::class, 'createVNPayUrl']);
    Route::get('/ipn', [PaymentWebhookController::class, 'handleVNPayIpn']);
    Route::get('/return', [PaymentWebhookController::class, 'handleVNPayReturn']);
});

// ==========================================
// 👑 ADMIN MANAGEMENT APIS (Role Admin Protected)
// ==========================================
Route::prefix('admin')->middleware(['role:admin'])->group(function () {
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

    // Cinemas Management CRUD
    Route::get('/cinemas', [AdminCinemaController::class, 'index']);
    Route::post('/cinemas', [AdminCinemaController::class, 'store']);
    Route::get('/cinemas/{id}', [AdminCinemaController::class, 'show']);
    Route::put('/cinemas/{id}', [AdminCinemaController::class, 'update']);
    Route::delete('/cinemas/{id}', [AdminCinemaController::class, 'destroy']);

    // Rooms & Seat Matrix Management
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

    // Users & RBAC Management
    Route::get('/users', [AdminUserController::class, 'index']);
    Route::post('/users', [AdminUserController::class, 'store']);
    Route::get('/users/{id}', [AdminUserController::class, 'show']);
    Route::put('/users/{id}', [AdminUserController::class, 'update']);
    Route::delete('/users/{id}', [AdminUserController::class, 'destroy']);
    Route::patch('/users/{id}/role', [AdminUserController::class, 'updateRole']);
    Route::patch('/users/{id}/points', [AdminUserController::class, 'updatePoints']);

    // Review Moderation
    Route::delete('/reviews/{id}', [MovieReviewController::class, 'destroy']);
});

// ==========================================
// 🌐 PUBLIC CLIENT APIS (With Rate Limiting)
// ==========================================

// Cinemas API
Route::get('/cinemas', [CinemaController::class, 'index']);
Route::get('/cinemas/{id}/showtimes', [CinemaController::class, 'showtimes']);

// Movies & Reviews API
Route::get('/movies', [MovieController::class, 'index']);
Route::get('/movies/{id}', [MovieController::class, 'show']);
Route::get('/movies/{movieId}/reviews', [MovieReviewController::class, 'index']);
Route::post('/movies/{movieId}/reviews', [MovieReviewController::class, 'store'])->middleware('throttle:reviews');

// Snacks & Combos API
Route::get('/snacks', [SnackController::class, 'index']);

// Vouchers API
Route::get('/vouchers', [VoucherController::class, 'index']);
Route::post('/vouchers/apply', [VoucherController::class, 'apply']);

// Showtimes & Seats API
Route::get('/showtimes/{id}', [ShowtimeController::class, 'show']);
Route::get('/showtimes/{id}/seats', [ShowtimeController::class, 'getSeats']);

// Seat Holding & Releasing (Real-time Concurrency Protection)
Route::post('/showtimes/{showtimeId}/seats/{seatId}/hold', [SeatController::class, 'hold'])->middleware('throttle:booking');
Route::post('/showtimes/{showtimeId}/seats/{seatId}/release', [SeatController::class, 'release'])->middleware('throttle:booking');

// Bookings & Checkout API
Route::get('/bookings', [BookingController::class, 'index']);
Route::post('/bookings/checkout', [BookingController::class, 'checkout'])->middleware('throttle:booking');
Route::get('/bookings/{code}', [BookingController::class, 'show']);
