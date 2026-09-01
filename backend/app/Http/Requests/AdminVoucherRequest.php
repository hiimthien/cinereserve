<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminVoucherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id');
        $isPost = $this->isMethod('post');

        return [
            'code' => [$isPost ? 'required' : 'sometimes', 'string', 'max:50', 'unique:vouchers,code' . ($id ? ",{$id}" : '')],
            'title' => [$isPost ? 'required' : 'sometimes', 'string', 'max:150'],
            'discount_type' => [$isPost ? 'required' : 'sometimes', 'string', 'in:fixed,percent'],
            'discount_value' => [$isPost ? 'required' : 'sometimes', 'numeric', 'min:1'],
            'min_order_amount' => ['nullable', 'numeric', 'min:0'],
            'max_discount' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
            'required_tier' => ['nullable', 'string'],
            'points_cost' => ['nullable', 'integer', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'expires_at' => ['nullable', 'date'],
            'is_active' => ['boolean'],
        ];
    }
}
