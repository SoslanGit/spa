<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Book>
 */
class BookFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->unique()->sentence(3),
            'author' => fake()->name(),
            'genre' => fake()->randomElement(Book::GENRES),
            'year' => fake()->numberBetween(1990, 2024),
            'pages' => fake()->numberBetween(120, 700),
            'price' => fake()->numberBetween(990, 4990),
            'old_price' => null,
            'rating' => 0,
            'reviews' => 0,
            'in_stock' => true,
            'badge' => 'new',
            'language' => 'EN',
            'description' => fake()->sentence(),
            'cover' => Book::COVERS[0],
        ];
    }
}
