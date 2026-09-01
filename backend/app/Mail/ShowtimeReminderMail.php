<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ShowtimeReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Booking $booking
    ) {}

    public function envelope(): Envelope
    {
        $movieTitle = $this->booking->movie?->title ?? 'Bộ Phim Của Bạn';
        $startTime = $this->booking->showtime?->start_time ?? 'sắp tới';
        $cinemaName = $this->booking->cinema?->name ?? 'CineReserve';

        return new Envelope(
            subject: "🎬 [Nhắc Giờ Chiếu] Suất {$startTime} hôm nay tại {$cinemaName} - {$movieTitle}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.showtime-reminder',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
