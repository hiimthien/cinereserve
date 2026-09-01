<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'title',
        'description',
        'target',
        'discount_type',
        'discount_value',
        'min_order_amount',
        'max_discount_amount',
        'usage_limit',
        'used_count',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'discount_value' => 'float',
        'min_order_amount' => 'float',
        'max_discount_amount' => 'float',
        'usage_limit' => 'integer',
        'used_count' => 'integer',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Calculate discount based on tickets and snacks totals
     */
    public function calculateDiscount(float $seatsTotal, float $snackTotal): float
    {
        $totalOrder = $seatsTotal + $snackTotal;

        if ($this->min_order_amount > 0 && $totalOrder < $this->min_order_amount) {
            return 0;
        }

        $applicableBase = match ($this->target) {
            'ticket' => $seatsTotal,
            'combo' => $snackTotal,
            default => $totalOrder,
        };

        if ($applicableBase <= 0) {
            return 0;
        }

        if ($this->discount_type === 'percent') {
            $discount = ($applicableBase * $this->discount_value) / 100;
            if ($this->max_discount_amount && $discount > $this->max_discount_amount) {
                $discount = $this->max_discount_amount;
            }
            return round($discount);
        }

        // Fixed amount discount
        return min($this->discount_value, $applicableBase);
    }
}
