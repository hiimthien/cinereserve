<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Mail\ShowtimeReminderMail;
use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendShowtimeReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Booking $booking
    ) {}

    public function handle(): void
    {
        $email = $this->booking->customer_email ?: $this->booking->user?->email;

        if (!$email) {
            Log::warning("Cannot send showtime reminder: Booking #{$this->booking->id} has no valid email.");
            return;
        }

        try {
            Mail::to($email)->send(new ShowtimeReminderMail($this->booking));
            
            // Mark reminder as sent
            $this->booking->update(['reminder_sent_at' => now()]);

            Log::info("Sent showtime reminder email for Booking #{$this->booking->booking_code} to {$email}");
        } catch (\Throwable $e) {
            Log::error("Failed to send showtime reminder for Booking #{$this->booking->id}: " . $e->getMessage());
        }
    }
}
