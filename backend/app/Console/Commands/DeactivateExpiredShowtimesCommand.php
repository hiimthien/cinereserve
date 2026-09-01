<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Showtime;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DeactivateExpiredShowtimesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'showtimes:deactivate-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tự động kiểm tra và vô hiệu hóa / dọn dẹp các suất chiếu đã kết thúc hoặc quá hạn';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $now = Carbon::now();
        $currentDate = $now->toDateString();
        $currentTime = $now->subMinutes(15)->format('H:i'); // 15 phút grace period

        $this->info("[$now] Bắt đầu quét các suất chiếu quá hạn...");

        // 1. Tìm các suất chiếu của ngày cũ hoặc đã qua giờ chiếu trong ngày hôm nay
        $expiredShowtimes = Showtime::query()
            ->where('show_date', '<', $currentDate)
            ->orWhere(function ($q) use ($currentDate, $currentTime) {
                $q->where('show_date', '=', $currentDate)
                  ->where('start_time', '<=', $currentTime);
            })
            ->get();

        $count = $expiredShowtimes->count();

        if ($count === 0) {
            $this->info('Không có suất chiếu nào quá hạn cần xử lý.');
            return Command::SUCCESS;
        }

        $this->info("Phát hiện {$count} suất chiếu đã kết thúc hoặc quá giờ.");

        // 2. Dọn dẹp Redis locks & đánh dấu vé quá hạn chưa check-in
        $expiredShowtimeIds = $expiredShowtimes->pluck('id')->toArray();
        
        $expiredBookingsCount = \App\Models\Booking::query()
            ->whereIn('showtime_id', $expiredShowtimeIds)
            ->where('check_in_status', 'pending')
            ->where('status', 'confirmed')
            ->update([
                'check_in_status' => 'expired',
            ]);

        foreach ($expiredShowtimes as $showtime) {
            Cache::forget("showtime:{$showtime->id}:seats");
            Cache::forget("cinereserve:showtimes:date:{$showtime->show_date}");
        }

        Log::info("Cronjob: Đã xử lý {$count} suất chiếu và cập nhật {$expiredBookingsCount} vé quá hạn chưa check-in.");
        $this->info("Hoàn tất dọn dẹp {$count} suất chiếu và cập nhật {$expiredBookingsCount} vé quá hạn.");

        return Command::SUCCESS;
    }
}
