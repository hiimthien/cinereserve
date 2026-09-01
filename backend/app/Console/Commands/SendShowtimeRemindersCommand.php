<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\SendShowtimeReminderJob;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendShowtimeRemindersCommand extends Command
{
    protected $signature = 'showtimes:send-reminders {--force : Bỏ qua giới hạn thời gian để gửi test cho tất cả vé chưa nhắc}';

    protected $description = 'Tự động quét và gửi Email nhắc nhở cho khách hàng có suất chiếu bắt đầu trong vòng 2 giờ tới';

    public function handle(): int
    {
        $now = Carbon::now();
        $twoHoursLater = $now->copy()->addHours(2);
        $todayStr = $now->format('Y-m-d');
        $isForce = $this->option('force');

        $this->info("🔍 Đang quét các đơn đặt vé có suất chiếu sắp diễn ra...");

        $query = Booking::query()
            ->with(['movie', 'cinema', 'room', 'showtime', 'seats', 'user'])
            ->whereIn('status', ['paid', 'confirmed'])
            ->where('check_in_status', 'pending')
            ->whereNull('reminder_sent_at')
            ->whereHas('showtime', function ($q) use ($todayStr, $now, $twoHoursLater, $isForce) {
                if ($isForce) {
                    return;
                }
                $q->where(function ($sub) use ($todayStr) {
                    $sub->whereDate('show_date', $todayStr)
                        ->orWhereDate('date', $todayStr);
                })
                ->where('start_time', '>=', $now->format('H:i:s'))
                ->where('start_time', '<=', $twoHoursLater->format('H:i:s'));
            });

        $bookings = $query->get();

        if ($bookings->isEmpty()) {
            $this->info("✅ Không có đơn đặt vé nào cần gửi nhắc nhở vào lúc này.");
            return Command::SUCCESS;
        }

        $this->info("📬 Tìm thấy {$bookings->count()} đơn đặt vé. Đang gửi email nhắc nhở...");

        $bar = $this->output->createProgressBar($bookings->count());
        $bar->start();

        foreach ($bookings as $booking) {
            SendShowtimeReminderJob::dispatch($booking);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("✨ Đã lên lịch gửi toàn bộ email nhắc nhở thành công!");

        return Command::SUCCESS;
    }
}
