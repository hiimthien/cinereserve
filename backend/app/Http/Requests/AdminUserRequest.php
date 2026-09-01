<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('id') ?? $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                $userId ? Rule::unique('users', 'email')->ignore($userId) : 'unique:users,email',
            ],
            'password' => [$userId ? 'nullable' : 'required', 'string', 'min:6'],
            'role' => ['required', 'string', Rule::in(['admin', 'staff', 'user'])],
            'phone' => ['nullable', 'string', 'max:20'],
            'membership_tier' => ['nullable', 'string', Rule::in(['member', 'vip', 'diamond'])],
            'points' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
