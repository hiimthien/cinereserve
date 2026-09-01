<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShowtimeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'movie_id' => $this->movie_id,
            'cinema_id' => $this->cinema_id,
            'room_id' => $this->room_id,
            'show_date' => $this->show_date?->format('Y-m-d') ?? $this->show_date,
            'date' => $this->show_date?->format('Y-m-d') ?? $this->show_date,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'base_price' => (float) $this->base_price,
            'format' => $this->format ?? '2D Standard',
            'status' => $this->status ?? 'scheduled',
            'movie' => new MovieResource($this->whenLoaded('movie')),
            'cinema' => $this->whenLoaded('cinema'),
            'room' => $this->whenLoaded('room'),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
