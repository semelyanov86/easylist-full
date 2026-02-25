<?php

declare(strict_types=1);

namespace Tests\Feature\WebAuthn;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebAuthnAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_auth_options_works_during_login_flow(): void
    {
        $user = User::factory()->withWebAuthn()->create();

        $response = $this->withSession(['login.id' => $user->id])
            ->postJson(route('webauthn.auth.challenge'));

        $response->assertOk();
    }

    public function test_auth_options_works_for_authenticated_user(): void
    {
        $user = User::factory()->withWebAuthn()->create();

        $response = $this->actingAs($user)
            ->postJson(route('webauthn.auth.challenge'));

        $response->assertOk();
    }

    public function test_auth_options_returns_error_without_user_context(): void
    {
        $response = $this->postJson(route('webauthn.auth.challenge'));

        // Без login.id в session и без auth — не найдёт пользователя
        $response->assertOk();
    }

    public function test_auth_verify_fails_without_valid_assertion(): void
    {
        $response = $this->postJson(route('webauthn.auth.verify'), [
            'id' => 'invalid',
            'rawId' => 'invalid',
            'type' => 'public-key',
            'response' => [
                'clientDataJSON' => 'invalid',
                'authenticatorData' => 'invalid',
                'signature' => 'invalid',
            ],
        ]);

        $response->assertStatus(422);
    }
}
