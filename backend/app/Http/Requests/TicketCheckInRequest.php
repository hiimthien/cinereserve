<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TicketCheckInRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'qr_code' => ['required', 'string'],
            'staff_name' => ['nullable', 'string', 'max:255'],
            'cinema_id' => ['nullable', 'integer'],
        ];
    }
}
