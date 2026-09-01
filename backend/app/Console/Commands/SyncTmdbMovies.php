<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\TmdbMovieSyncService;
use Illuminate\Console\Command;

class SyncTmdbMovies extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cinereserve:sync-movies {--pages=2 : Số trang phim đang chiếu muốn đồng bộ}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Đồng bộ danh sách phim đang chiếu và sắp chiếu tại Việt Nam từ TMDb API';

    /**
     * Execute the console command.
     */
    public function handle(TmdbMovieSyncService $syncService): int
    {
        $this->info('🎬 Đang kết nối tới The Movie Database (TMDb) API...');
        $pages = (int) $this->option('pages');

        $this->output->progressStart($pages + 1);

        $this->line("\n📥 Đang tải danh sách phim Đang Chiếu (Now Playing in Vietnam)...");
        $nowPlaying = $syncService->syncNowPlayingMovies($pages);
        $this->output->progressAdvance();

        $this->line("\n📥 Đang tải danh sách phim Sắp Chiếu (Upcoming in Vietnam)...");
        $upcoming = $syncService->syncUpcomingMovies(1);
        $this->output->progressAdvance();

        $this->output->progressFinish();

        $this->newLine();
        $this->info("✅ Đồng bộ thành công!");
        $this->table(
            ['Danh mục', 'Số lượng phim cập nhật'],
            [
                ['Đang chiếu (Now Showing)', count($nowPlaying)],
                ['Sắp chiếu (Coming Soon)', count($upcoming)],
                ['Tổng cộng', count($nowPlaying) + count($upcoming)],
            ]
        );

        return Command::SUCCESS;
    }
}
