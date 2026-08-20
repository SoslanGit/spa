<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_rejects_bad_credentials(): void
    {
        User::factory()->create(['email' => 'demo@bookstore.test']);

        $this->postJson('/api/login', [
            'email' => 'demo@bookstore.test',
            'password' => 'wrong',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    public function test_login_returns_jwt(): void
    {
        $user = User::factory()->create([
            'email' => 'demo@bookstore.test',
            'password' => 'password',
        ]);

        $this->postJson('/api/login', [
            'email' => 'demo@bookstore.test',
            'password' => 'password',
        ])
            ->assertOk()
            ->assertJsonPath('token_type', 'bearer')
            ->assertJsonPath('user.id', $user->id)
            ->assertJsonPath('user.email', $user->email)
            ->assertJsonStructure(['access_token', 'token_type', 'expires_in', 'user']);
    }

    public function test_me_requires_auth(): void
    {
        $this->getJson('/api/me')->assertUnauthorized();
    }

    public function test_me_returns_current_user(): void
    {
        $user = User::factory()->create();

        $this->getJson('/api/me', $this->bearer($user))
            ->assertOk()
            ->assertJsonPath('id', $user->id)
            ->assertJsonPath('email', $user->email);
    }

    public function test_logout_invalidates_token(): void
    {
        $user = User::factory()->create();
        $headers = $this->bearer($user);

        $this->postJson('/api/logout', [], $headers)
            ->assertOk()
            ->assertJsonPath('message', 'Вы вышли.');

        $this->getJson('/api/me', $headers)->assertUnauthorized();
    }

    private function bearer(User $user): array
    {
        return ['Authorization' => 'Bearer '.auth('api')->login($user)];
    }
}
