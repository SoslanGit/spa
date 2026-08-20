<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_create_book(): void
    {
        $this->postJson('/api/books', $this->payload())
            ->assertUnauthorized();
    }

    public function test_user_can_create_book(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/books', $this->payload(), $this->bearer($user))
            ->assertCreated()
            ->assertJsonPath('title', 'Vue Cookbook')
            ->assertJsonPath('userId', $user->id);

        $this->assertDatabaseHas('books', [
            'title' => 'Vue Cookbook',
            'user_id' => $user->id,
        ]);
    }

    public function test_user_cannot_delete_someone_elses_book(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $book = Book::factory()->for($owner)->create();

        $this->deleteJson("/api/books/{$book->id}", [], $this->bearer($stranger))
            ->assertForbidden();

        $this->assertDatabaseHas('books', ['id' => $book->id]);
    }

    public function test_owner_can_delete_book(): void
    {
        $owner = User::factory()->create();
        $book = Book::factory()->for($owner)->create();

        $this->deleteJson("/api/books/{$book->id}", [], $this->bearer($owner))
            ->assertNoContent();

        $this->assertDatabaseMissing('books', ['id' => $book->id]);
    }

    private function bearer(User $user): array
    {
        return ['Authorization' => 'Bearer '.auth('api')->login($user)];
    }

    private function payload(): array
    {
        return [
            'title' => 'Vue Cookbook',
            'author' => 'Evan You',
            'genre' => 'frontend',
            'year' => 2024,
            'pages' => 280,
            'price' => 1990,
            'description' => 'Demo',
        ];
    }
}
