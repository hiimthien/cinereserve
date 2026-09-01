<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminSnackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isPost = $this->isMethod('post');

        return [
            'name' => [$isPost ? 'required' : 'sometimes', 'string', 'max:100'],
            'category' => [$isPost ? 'required' : 'sometimes', 'string', 'in:popcorn,drink,combo,snack'],
            'price' => [$isPost ? 'required' : 'sometimes', 'numeric', 'min:0'],
            'image_url' => ['nullable', 'string', 'url'],
            'description' => ['nullable', 'string'],
            'is_available' => ['boolean'],
        ];
    }
}
