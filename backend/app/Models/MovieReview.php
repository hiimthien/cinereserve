<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovieReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'movie_id',
        'user_id',
        'user_name',
        'user_avatar',
        'membership_tier',
        'rating',
        'comment',
        'likes_count',
    ];

    protected $casts = [
        'rating' => 'integer',
        'likes_count' => 'integer',
    ];

    public function movie(): BelongsTo
    {
        return $this->belongsTo(Movie::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
