<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MovieFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'nullable|string|in:now_showing,coming_soon,all',
            'city' => 'nullable|string|max:100',
            'cinema_id' => 'nullable|integer|exists:cinemas,id',
            'date' => 'nullable|date_format:Y-m-d',
            'genre' => 'nullable|string|max:50',
            'room_type' => 'nullable|string|max:50',
            'search' => 'nullable|string|max:100',
        ];
    }
}
