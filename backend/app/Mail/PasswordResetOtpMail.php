<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordResetOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $otpCode
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "🔐 [CineReserve] Mã xác thực OTP đặt lại mật khẩu của bạn: {$this->otpCode}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.password-reset-otp',
            with: [
                'userName' => $this->user->name,
                'otpCode' => $this->otpCode,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
