<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminMovieRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isPost = $this->isMethod('post');

        return [
            'title' => [$isPost ? 'required' : 'sometimes', 'string', 'max:255'],
            'original_title' => ['nullable', 'string', 'max:255'],
            'description' => [$isPost ? 'required' : 'sometimes', 'string'],
            'duration' => ['nullable', 'integer', 'min:1'],
            'duration_minutes' => ['nullable', 'integer', 'min:1'],
            'release_date' => [$isPost ? 'required' : 'sometimes', 'date'],
            'rating' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'poster_url' => [$isPost ? 'required' : 'sometimes', 'string'],
            'backdrop_url' => ['nullable', 'string'],
            'trailer_url' => ['nullable', 'string'],
            'genres' => ['nullable', 'array'],
            'status' => [$isPost ? 'required' : 'sometimes', 'in:now_showing,coming_soon,early_premiere'],
        ];
    }
}
