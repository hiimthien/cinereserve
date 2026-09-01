<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminShowtimeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'movie_id' => ['required', 'exists:movies,id'],
            'room_id' => ['required', 'exists:rooms,id'],
            'show_date' => ['required', 'date'],
            'start_time' => ['required', 'string'],
            'end_time' => ['nullable', 'string'],
            'base_price' => ['required', 'numeric', 'min:45000'],
            'price_vip' => ['nullable', 'numeric', 'min:45000'],
            'price_couple' => ['nullable', 'numeric', 'min:45000'],
            'format' => ['required', 'string'],
            'status' => ['nullable', 'string', 'in:scheduled,early_premiere,cancelled'],
        ];

    }
}
