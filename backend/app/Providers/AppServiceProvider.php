<?php

namespace App\Providers;

use App\Repositories\Contracts\AnalyticsRepositoryInterface;
use App\Repositories\Contracts\BookingRepositoryInterface;
use App\Repositories\Contracts\CinemaRepositoryInterface;
use App\Repositories\Contracts\LoyaltyRepositoryInterface;
use App\Repositories\Contracts\MovieRepositoryInterface;
use App\Repositories\Contracts\ReviewRepositoryInterface;
use App\Repositories\Contracts\RoomRepositoryInterface;
use App\Repositories\Contracts\ShowtimeRepositoryInterface;
use App\Repositories\Contracts\SnackRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Contracts\VoucherRepositoryInterface;
use App\Repositories\Eloquent\AnalyticsRepository;
use App\Repositories\Eloquent\BookingRepository;
use App\Repositories\Eloquent\CinemaRepository;
use App\Repositories\Eloquent\LoyaltyRepository;
use App\Repositories\Eloquent\MovieRepository;
use App\Repositories\Eloquent\ReviewRepository;
use App\Repositories\Eloquent\RoomRepository;
use App\Repositories\Eloquent\ShowtimeRepository;
use App\Repositories\Eloquent\SnackRepository;
use App\Repositories\Eloquent\UserRepository;
use App\Repositories\Eloquent\VoucherRepository;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            ShowtimeRepositoryInterface::class,
            ShowtimeRepository::class
        );

        $this->app->bind(
            AnalyticsRepositoryInterface::class,
            AnalyticsRepository::class
        );

        $this->app->bind(
            BookingRepositoryInterface::class,
            BookingRepository::class
        );

        $this->app->bind(
            CinemaRepositoryInterface::class,
            CinemaRepository::class
        );

        $this->app->bind(
            UserRepositoryInterface::class,
            UserRepository::class
        );

        $this->app->bind(
            ReviewRepositoryInterface::class,
            ReviewRepository::class
        );

        $this->app->bind(
            MovieRepositoryInterface::class,
            MovieRepository::class
        );

        $this->app->bind(
            RoomRepositoryInterface::class,
            RoomRepository::class
        );

        $this->app->bind(
            SnackRepositoryInterface::class,
            SnackRepository::class
        );

        $this->app->bind(
            VoucherRepositoryInterface::class,
            VoucherRepository::class
        );

        $this->app->bind(
            LoyaltyRepositoryInterface::class,
            LoyaltyRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 1. Rate Limiter cho API công khai thông thường (60 req/phút)
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // 2. Rate Limiter chống brute-force đăng nhập / đăng ký (10 req/phút)
        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip())->response(function () {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn đã gửi quá nhiều yêu cầu đăng nhập/đăng ký. Vui lòng thử lại sau 1 phút.',
                ], 429);
            });
        });

        // 3. Rate Limiter chống bot spam giữ ghế & thanh toán (30 req/phút)
        RateLimiter::for('booking', function (Request $request) {
            return Limit::perMinute(30)->by($request->user()?->id ?: $request->ip())->response(function () {
                return response()->json([
                    'success' => false,
                    'message' => 'Thao tác đặt vé/giữ ghế quá dồn dập. Vui lòng thử lại sau vài giây.',
                ], 429);
            });
        });

        // 4. Rate Limiter chống spam đánh giá / review phim (10 req/phút)
        RateLimiter::for('reviews', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip())->response(function () {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn gửi đánh giá quá thường xuyên. Vui lòng thử lại sau 1 phút.',
                ], 429);
            });
        });

        // 5. Rate Limiter tốc độ cao cho máy quét QR nhân viên (120 req/phút)
        RateLimiter::for('staff_scan', function (Request $request) {
            return Limit::perMinute(120)->by($request->user()?->id ?: $request->ip());
        });
    }
}
