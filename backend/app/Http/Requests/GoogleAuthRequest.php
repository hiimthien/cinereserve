<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GoogleAuthRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'name' => ['required', 'string'],
            'google_id' => ['nullable', 'string'],
            'avatar' => ['nullable', 'string'],
        ];
    }
}
