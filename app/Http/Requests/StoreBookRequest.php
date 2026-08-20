<?php

namespace App\Http\Requests;

use App\Models\Book;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Book::class) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'old_price' => $this->input('oldPrice', $this->input('old_price')),
            'in_stock' => $this->has('inStock')
                ? $this->boolean('inStock')
                : ($this->has('in_stock') ? $this->boolean('in_stock') : true),
        ]);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'genre' => ['required', Rule::in(Book::GENRES)],
            'year' => ['required', 'integer', 'min:1900', 'max:'.(now()->year + 1)],
            'pages' => ['nullable', 'integer', 'min:1', 'max:10000'],
            'price' => ['required', 'integer', 'min:0'],
            'old_price' => ['nullable', 'integer', 'min:0'],
            'description' => ['nullable', 'string', 'max:2000'],
            'language' => ['nullable', 'string', 'max:8'],
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
