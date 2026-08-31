<?php

use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\MovieController;
use App\Http\Controllers\Api\SeatController;
use App\Http\Controllers\Api\ShowtimeController;
use Illuminate\Support\Facades\Route;

// Movies API
Route::get('/movies', [MovieController::class, 'index']);
Route::get('/movies/{id}', [MovieController::class, 'show']);

// Showtimes & Seats API
Route::get('/showtimes/{id}', [ShowtimeController::class, 'show']);
Route::get('/showtimes/{id}/seats', [ShowtimeController::class, 'getSeats']);

// Seat Holding & Releasing (Real-time Concurrency Locks)
Route::post('/showtimes/{showtimeId}/seats/{seatId}/hold', [SeatController::class, 'hold']);
Route::post('/showtimes/{showtimeId}/seats/{seatId}/release', [SeatController::class, 'release']);

// Bookings & Checkout API
Route::post('/bookings/checkout', [BookingController::class, 'checkout']);
Route::get('/bookings/{code}', [BookingController::class, 'show']);
