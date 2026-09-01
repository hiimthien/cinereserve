<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SeatResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'room_id' => $this->room_id,
            'row' => $this->row,
            'number' => $this->number,
            'type' => $this->type,
            'status' => $this->status ?? 'available',
            'held_by' => $this->held_by ?? null,
            'held_until' => $this->held_until ?? null,
            'price' => $this->price ?? 0,
        ];
    }
}
