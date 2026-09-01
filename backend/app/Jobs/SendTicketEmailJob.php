<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Mail\TicketConfirmationMail;
use App\Models\Booking;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendTicketEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 10;

    public function __construct(
        public Booking $booking
    ) {}

    public function handle(): void
    {
        try {
            if (!empty($this->booking->user_email)) {
                Mail::to($this->booking->user_email)->send(new TicketConfirmationMail($this->booking));
                Log::info("✅ [Queue Job] Đã gửi email xác nhận vé #{$this->booking->booking_code} tới {$this->booking->user_email}");
            }
        } catch (Exception $e) {
            Log::error("❌ [Queue Job] Lỗi gửi mail vé #{$this->booking->booking_code}: {$e->getMessage()}");
            throw $e;
        }
    }
}
