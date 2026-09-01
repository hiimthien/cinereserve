<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoyaltyReward extends Model
{
    use HasFactory;

    protected $fillable = [
        'reward_key',
        'title',
        'description',
        'points_required',
        'discount_value',
        'target',
        'badge',
        'icon',
        'prefix',
        'is_active',
    ];

    protected $casts = [
        'points_required' => 'integer',
        'discount_value' => 'float',
        'is_active' => 'boolean',
    ];
}
