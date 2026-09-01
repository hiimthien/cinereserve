<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Mail\LoyaltyVoucherMail;
use App\Models\User;
use App\Models\Voucher;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendWelcomeVoucherEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 10;

    public function __construct(
        public User $user,
        public Voucher $voucher,
        public string $badgeText = 'Chào Mừng Thành Viên Mới',
        public string $customMessage = '',
        public string $subjectTitle = ''
    ) {}

    public function handle(): void
    {
        try {
            if (!empty($this->user->email)) {
                Mail::to($this->user->email)->send(new LoyaltyVoucherMail(
                    user: $this->user,
                    voucher: $this->voucher,
                    badgeText: $this->badgeText,
                    customMessage: $this->customMessage,
                    subjectTitle: $this->subjectTitle
                ));
                Log::info("✅ [Queue Job] Đã gửi email tặng voucher tới {$this->user->email}");
            }
        } catch (Exception $e) {
            Log::error("❌ [Queue Job] Lỗi gửi mail voucher tới {$this->user->email}: {$e->getMessage()}");
            throw $e;
        }
    }
}
