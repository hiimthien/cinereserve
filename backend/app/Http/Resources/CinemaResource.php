<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CinemaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'address' => $this->address,
            'city' => $this->city,
            'rooms_count' => $this->rooms_count ?? ($this->rooms ? count($this->rooms) : 0),
            'showtimes_count' => $this->showtimes_count ?? 0,
            'rooms' => RoomResource::collection($this->whenLoaded('rooms')),
        ];
    }
}
