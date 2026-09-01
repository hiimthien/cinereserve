<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoomResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'cinema_id' => $this->cinema_id,
            'name' => $this->name,
            'room_type' => $this->room_type,
            'total_seats' => $this->total_seats,
            'cinema' => new CinemaResource($this->whenLoaded('cinema')),
            'seats' => SeatResource::collection($this->whenLoaded('seats')),
        ];
    }
}
