<?php

use App\Jobs\SyncMoviesFromTmdbJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Lên lịch tự động đồng bộ phim mới từ TMDb API mỗi ngày vào lúc 03:00 sáng
Schedule::job(new SyncMoviesFromTmdbJob(pages: 2))
    ->dailyAt('03:00')
    ->name('daily-tmdb-movie-sync')
    ->withoutOverlapping();

// Lên lịch tự động quét và dọn dẹp các suất chiếu quá hạn mỗi 5 phút
Schedule::command('showtimes:deactivate-expired')
    ->everyFiveMinutes()
    ->name('deactivate-expired-showtimes')
    ->withoutOverlapping();

// Lên lịch tự động quét và gửi email nhắc giờ chiếu trước 2 tiếng mỗi 15 phút
Schedule::command('showtimes:send-reminders')
    ->everyFifteenMinutes()
    ->name('send-showtime-reminders')
    ->withoutOverlapping();

