<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_me_returns_json_api_user(): void
    {
        $user = User::factory()->create([
            'name' => 'Тест Пользователь',
            'email' => 'test@example.com',
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/me');

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/vnd.api+json')
            ->assertJsonPath('data.type', 'users')
            ->assertJsonPath('data.id', (string) $user->id)
            ->assertJsonPath('data.attributes.name', 'Тест Пользователь')
            ->assertJsonPath('data.attributes.email', 'test@example.com');
    }

    public function test_me_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/me');

        $response->assertUnauthorized();
    }
}
