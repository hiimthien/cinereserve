<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\TmdbMovieSyncService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncMoviesFromTmdbJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 180; // 3 minutes timeout for external TMDB calls
    public int $tries = 2;

    public function __construct(
        public int $pages = 2
    ) {}

    public function handle(TmdbMovieSyncService $syncService): void
    {
        Log::info("🎬 [Queue Worker] Bắt đầu đồng bộ danh sách phim từ TMDb API ({$this->pages} trang)...");

        try {
            $nowPlaying = $syncService->syncNowPlayingMovies($this->pages);
            $upcoming = $syncService->syncUpcomingMovies(1);

            $total = count($nowPlaying) + count($upcoming);
            Log::info("✅ [Queue Worker] Đồng bộ thành công {$total} phim từ TMDb (Đang chiếu: " . count($nowPlaying) . ", Sắp chiếu: " . count($upcoming) . ")");
        } catch (Exception $e) {
            Log::error("❌ [Queue Worker] Lỗi đồng bộ phim TMDb: {$e->getMessage()}");
            throw $e;
        }
    }
}
