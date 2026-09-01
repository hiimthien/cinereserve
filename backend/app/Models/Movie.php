<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Movie extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'genre' => 'array',
        'cast' => 'array',
        'release_date' => 'date',
        'rating' => 'float',
    ];

    public function showtimes(): HasMany
    {
        return $this->hasMany(Showtime::class);
    }

    /**
     * Scope đa năng xử lý tất cả bộ lọc (Chuẩn Clean Laravel)
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        // 1. Lọc theo trạng thái (Đang chiếu / Sắp chiếu)
        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        // 2. Tìm kiếm theo tên phim / đạo diễn
        if (!empty($filters['search'])) {
            $search = (string) $filters['search'];
            $query->where(function (Builder $q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('original_title', 'like', "%{$search}%")
                  ->orWhere('director', 'like', "%{$search}%");
            });
        }

        // 3. Lọc theo thể loại phim
        if (!empty($filters['genre']) && $filters['genre'] !== 'all') {
            $genre = (string) $filters['genre'];
            $query->whereJsonContains('genre', $genre);
        }

        // 4. Lọc theo Tiêu chí Suất chiếu (Thành phố, Rạp, Ngày, Loại phòng)
        if (!empty($filters['city']) || !empty($filters['cinema_id']) || !empty($filters['date']) || !empty($filters['room_type'])) {
            $query->whereHas('showtimes', function (Builder $sq) use ($filters) {
                if (!empty($filters['date'])) {
                    $sq->where('show_date', $filters['date']);
                }
                if (!empty($filters['cinema_id'])) {
                    $sq->where('cinema_id', (int) $filters['cinema_id']);
                }
                if (!empty($filters['city']) && $filters['city'] !== 'Tất cả') {
                    $sq->whereHas('cinema', function (Builder $cq) use ($filters) {
                        $cq->where('city', 'like', "%{$filters['city']}%");
                    });
                }
                if (!empty($filters['room_type']) && $filters['room_type'] !== 'all') {
                    $sq->whereHas('room', function (Builder $rq) use ($filters) {
                        $rq->where('room_type', 'like', "%{$filters['room_type']}%");
                    });
                }
            });
        }

        return $query;
    }
}
