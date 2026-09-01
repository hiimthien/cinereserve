<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role ?? 'user',
            'phone' => $this->phone,
            'avatar' => $this->avatar,
            'points' => $this->points ?? 0,
            'membership_tier' => $this->membership_tier ?? 'member',
            'tier_name' => $this->getTierName(),
            'total_spent' => (float) ($this->total_spent ?? 0),
            'total_tickets_bought' => (int) ($this->total_tickets_bought ?? 0),
            'bookings_count' => $this->bookings_count ?? ($this->bookings ? count($this->bookings) : 0),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
