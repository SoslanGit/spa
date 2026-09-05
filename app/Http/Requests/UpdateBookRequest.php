<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBookRequest extends FormRequest
{
    public function authorize(): bool
    {
        $book = $this->route('book');

        return $book !== null && ($this->user()?->can('update', $book) ?? false);
    }

    protected function prepareForValidation(): void
    {
        $mapped = [];

        if ($this->has('oldPrice')) {
            $mapped['old_price'] = $this->input('oldPrice');
        }

        if ($this->has('inStock')) {
            $mapped['in_stock'] = $this->boolean('inStock');
        }

        if ($mapped !== []) {
            $this->merge($mapped);
        }
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'author' => ['sometimes', 'required', 'string', 'max:255'],
            'genre' => ['sometimes', 'required', Rule::in(\App\Models\Book::GENRES)],
            'year' => ['sometimes', 'required', 'integer', 'min:1900', 'max:'.(now()->year + 1)],
            'pages' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:10000'],
            'price' => ['sometimes', 'required', 'integer', 'min:0'],
            'old_price' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'description' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'language' => ['sometimes', 'nullable', 'string', 'max:8'],
            'in_stock' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Укажите название.',
            'author.required' => 'Укажите автора.',
            'genre.required' => 'Укажите жанр.',
            'genre.in' => 'Неизвестный жанр.',
            'year.required' => 'Укажите год.',
            'price.required' => 'Укажите цену.',
        ];
    }
}
