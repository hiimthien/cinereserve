<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'movie_id' => $this->movie_id,
            'user_id' => $this->user_id,
            'user_name' => $this->user_name ?? 'Khán Giả CineReserve',
            'user_avatar' => $this->user_avatar,
            'membership_tier' => $this->membership_tier ?? 'member',
            'rating' => (int) $this->rating,
            'comment' => $this->comment,
            'likes_count' => (int) ($this->likes_count ?? 0),
            'created_at' => $this->created_at?->toIso8601String(),
            'created_at_human' => $this->created_at?->diffForHumans(),
        ];
    }
}
