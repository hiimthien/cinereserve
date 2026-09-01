<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // 'user', 'staff', 'admin'
        'phone',
        'avatar',
        'points',
        'membership_tier',
        'total_spent',
        'total_tickets_bought',
        'google_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'points' => 'integer',
        'total_spent' => 'float',
        'total_tickets_bought' => 'integer',
    ];

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Kiểm tra quyền Quản trị viên (Admin)
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Kiểm tra quyền Nhân viên soát vé / Quản trị
     */
    public function isStaff(): bool
    {
        return in_array($this->role, ['admin', 'staff'], true);
    }

    /**
     * Tỷ lệ tích điểm theo hạng thành viên
     */
    public function getPointMultiplier(): float
    {
        return match ($this->membership_tier) {
            'diamond' => 0.15, // 15%
            'vip' => 0.10,     // 10%
            default => 0.05,   // 5%
        };
    }

    /**
     * Tên hiển thị của hạng thành viên
     */
    public function getTierName(): string
    {
        return match ($this->membership_tier) {
            'diamond' => 'CineDiamond (Kim Cương)',
            'vip' => 'CineVIP (Vàng)',
            default => 'CineMember (Bạc)',
        };
    }

    /**
     * Cộng điểm và cập nhật tiến trình thăng hạng sau khi mua vé
     */
    public function processBookingLoyalty(float $amount, int $ticketsCount): array
    {
        $earnedPoints = (int) round(($amount / 1000) * $this->getPointMultiplier());
        if ($earnedPoints < 1) $earnedPoints = 1;

        $this->points += $earnedPoints;
        $this->total_spent += $amount;
        $this->total_tickets_bought += $ticketsCount;

        $oldTier = $this->membership_tier;
        $upgraded = false;

        // Quy tắc thăng hạng
        if ($this->total_tickets_bought >= 20 || $this->total_spent >= 2000000) {
            $this->membership_tier = 'diamond';
        } elseif ($this->total_tickets_bought >= 5 || $this->total_spent >= 500000) {
            $this->membership_tier = 'vip';
        }

        if ($oldTier !== $this->membership_tier) {
            $upgraded = true;
        }

        $this->save();

        return [
            'earned_points' => $earnedPoints,
            'current_points' => $this->points,
            'membership_tier' => $this->membership_tier,
            'upgraded' => $upgraded,
            'old_tier' => $oldTier,
        ];
    }
}
