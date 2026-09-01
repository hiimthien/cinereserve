<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MovieResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'original_title' => $this->original_title,
            'slug' => $this->slug,
            'description' => $this->description,
            'duration' => $this->duration ?? $this->duration_minutes ?? 120,
            'duration_minutes' => $this->duration ?? $this->duration_minutes ?? 120,
            'release_date' => $this->release_date?->format('Y-m-d') ?? $this->release_date,
            'rating' => (float) ($this->rating ?? 8.5),
            'age_rating' => $this->age_rating ?? 'T18',
            'poster_url' => $this->poster_url,
            'backdrop_url' => $this->backdrop_url,
            'trailer_url' => $this->trailer_url,
            'genre' => $this->genre ?? $this->genres ?? [],
            'genres' => $this->genre ?? $this->genres ?? [],
            'director' => $this->director,
            'cast' => $this->cast,
            'status' => $this->status ?? 'now_showing',
            'showtimes' => ShowtimeResource::collection($this->whenLoaded('showtimes')),
            'reviews' => ReviewResource::collection($this->whenLoaded('reviews')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
