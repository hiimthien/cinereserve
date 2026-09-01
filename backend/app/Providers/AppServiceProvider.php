<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            \App\Repositories\Contracts\ShowtimeRepositoryInterface::class,
            \App\Repositories\Eloquent\ShowtimeRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\AnalyticsRepositoryInterface::class,
            \App\Repositories\Eloquent\AnalyticsRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\BookingRepositoryInterface::class,
            \App\Repositories\Eloquent\BookingRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\CinemaRepositoryInterface::class,
            \App\Repositories\Eloquent\CinemaRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\UserRepositoryInterface::class,
            \App\Repositories\Eloquent\UserRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\ReviewRepositoryInterface::class,
            \App\Repositories\Eloquent\ReviewRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\MovieRepositoryInterface::class,
            \App\Repositories\Eloquent\MovieRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\RoomRepositoryInterface::class,
            \App\Repositories\Eloquent\RoomRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\SnackRepositoryInterface::class,
            \App\Repositories\Eloquent\SnackRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\VoucherRepositoryInterface::class,
            \App\Repositories\Eloquent\VoucherRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 1. Rate Limiter cho API công khai thông thường (60 req/phút)
        \Illuminate\Support\Facades\RateLimiter::for('api', function (\Illuminate\Http\Request $request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // 2. Rate Limiter chống brute-force đăng nhập / đăng ký (10 req/phút)
        \Illuminate\Support\Facades\RateLimiter::for('auth', function (\Illuminate\Http\Request $request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(10)->by($request->ip())->response(function () {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn đã gửi quá nhiều yêu cầu đăng nhập/đăng ký. Vui lòng thử lại sau 1 phút.',
                ], 429);
            });
        });

        // 3. Rate Limiter chống bot spam giữ ghế & thanh toán (30 req/phút)
        \Illuminate\Support\Facades\RateLimiter::for('booking', function (\Illuminate\Http\Request $request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(30)->by($request->user()?->id ?: $request->ip())->response(function () {
                return response()->json([
                    'success' => false,
                    'message' => 'Thao tác đặt vé/giữ ghế quá dồn dập. Vui lòng thử lại sau vài giây.',
                ], 429);
            });
        });

        // 4. Rate Limiter chống spam đánh giá / review phim (10 req/phút)
        \Illuminate\Support\Facades\RateLimiter::for('reviews', function (\Illuminate\Http\Request $request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(10)->by($request->user()?->id ?: $request->ip())->response(function () {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn gửi đánh giá quá thường xuyên. Vui lòng thử lại sau 1 phút.',
                ], 429);
            });
        });

        // 5. Rate Limiter tốc độ cao cho máy quét QR nhân viên (120 req/phút)
        \Illuminate\Support\Facades\RateLimiter::for('staff_scan', function (\Illuminate\Http\Request $request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(120)->by($request->user()?->id ?: $request->ip());
        });
    }
}
