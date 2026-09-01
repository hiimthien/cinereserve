<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VoucherResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'title' => $this->title,
            'discount_type' => $this->discount_type,
            'discount_value' => (float) $this->discount_value,
            'min_order_amount' => (float) ($this->min_order_amount ?? 0),
            'max_discount' => (float) ($this->max_discount ?? 0),
            'description' => $this->description,
            'required_tier' => $this->required_tier,
            'points_cost' => (int) ($this->points_cost ?? 0),
            'usage_limit' => $this->usage_limit ? (int) $this->usage_limit : null,
            'used_count' => (int) ($this->used_count ?? 0),
            'is_active' => (bool) $this->is_active,
            'expires_at' => $this->expires_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
