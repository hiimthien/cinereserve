<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\User;
use App\Models\Voucher;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LoyaltyVoucherMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public Voucher $voucher,
        public string $badgeText = 'Đặc Quyền Thành Viên VIP',
        public ?string $customMessage = null,
        public ?string $subjectTitle = null
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->subjectTitle ?: "🎁 [CineReserve] Quà tặng Voucher {$this->voucher->code} dành riêng cho bạn!";
        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.loyalty-voucher',
            with: [
                'userName' => $this->user->name,
                'tierName' => $this->user->getTierName(),
                'badgeText' => $this->badgeText,
                'emailMessage' => $this->customMessage ?: 'Cảm ơn bạn đã luôn tin tưởng và đồng hành cùng CineReserve. Dưới đây là mã ưu đãi độc quyền dành cho bạn:',
                'voucherTitle' => $this->voucher->title,
                'voucherCode' => $this->voucher->code,
                'voucherDescription' => $this->voucher->description,
                'subjectTitle' => $this->subjectTitle,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
