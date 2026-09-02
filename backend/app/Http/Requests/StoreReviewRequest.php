<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rating' => ['required', 'integer', 'min:1', 'max:10'],
            'comment' => ['required', 'string', 'min:5', 'max:1000'],
            'user_name' => ['nullable', 'string', 'max:255'],
        ];
    }
}
