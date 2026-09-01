<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'showtime_id' => 'required|integer|exists:showtimes,id',
            'seat_ids' => 'required|array|min:1|max:8',
            'seat_ids.*' => 'integer|exists:seats,id',
            'session_id' => 'required|string|max:100',
            'user_name' => 'required|string|max:100',
            'user_email' => 'required|email|max:100',
            'user_phone' => 'required|string|max:20',
            'payment_method' => 'required|string|in:vnpay,momo,card,cash',
            'total_amount' => 'nullable|numeric|min:0',
            'voucher_code' => 'nullable|string|max:50',
            'discount_amount' => 'nullable|numeric|min:0',
            'combos' => 'nullable|array',
            'combos.*.id' => 'required_with:combos|integer',
            'combos.*.name' => 'nullable|string',
            'combos.*.price' => 'nullable|numeric',
            'combos.*.quantity' => 'required_with:combos|integer|min:1',
        ];
    }
}
