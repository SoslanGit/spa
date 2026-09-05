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

    public function test_book_creation_requires_valid_payload(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/books', [
            'author' => 'Unknown',
            'genre' => 'not-a-genre',
            'year' => 1800,
            'price' => -1,
        ], $this->bearer($user))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'genre', 'year', 'price']);
    }

    public function test_guest_cannot_update_book(): void
    {
        $owner = User::factory()->create();
        $book = Book::factory()->for($owner)->create();

        $this->patchJson("/api/books/{$book->id}", ['title' => 'Updated'])
            ->assertUnauthorized();
    }

    public function test_user_cannot_update_someone_elses_book(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $book = Book::factory()->for($owner)->create(['title' => 'Original']);

        $this->patchJson("/api/books/{$book->id}", ['title' => 'Changed'], $this->bearer($stranger))
            ->assertForbidden();

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => 'Original',
        ]);
    }

    public function test_owner_can_update_book(): void
    {
        $owner = User::factory()->create();
        $book = Book::factory()->for($owner)->create([
            'title' => 'Original',
            'price' => 1000,
        ]);

        $this->patchJson("/api/books/{$book->id}", [
            'title' => 'Updated title',
            'price' => 1490,
        ], $this->bearer($owner))
            ->assertOk()
            ->assertJsonPath('title', 'Updated title')
            ->assertJsonPath('price', 1490);

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => 'Updated title',
            'price' => 1490,
        ]);
    }

    public function test_book_update_validates_changed_fields(): void
    {
        $owner = User::factory()->create();
        $book = Book::factory()->for($owner)->create();

        $this->patchJson("/api/books/{$book->id}", [
            'genre' => 'unknown',
            'year' => 1800,
        ], $this->bearer($owner))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['genre', 'year']);
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
