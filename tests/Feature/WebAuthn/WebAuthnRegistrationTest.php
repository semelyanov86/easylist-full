<?php

declare(strict_types=1);

namespace Tests\Feature\WebAuthn;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laragear\WebAuthn\Models\WebAuthnCredential;
use Tests\TestCase;

class WebAuthnRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_options_requires_authentication(): void
    {
        $response = $this->postJson(route('webauthn.register.challenge'));

        $response->assertUnauthorized();
    }

    public function test_register_options_returns_challenge_for_authenticated_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson(route('webauthn.register.challenge'));

        $response->assertOk();
    }

    public function test_register_store_requires_authentication(): void
    {
        $response = $this->postJson(route('webauthn.register'));

        $response->assertUnauthorized();
    }

    public function test_destroy_requires_authentication(): void
    {
        $response = $this->deleteJson(route('webauthn.destroy', ['credentialId' => 'fake-id']));

        $response->assertUnauthorized();
    }

    public function test_destroy_removes_credential_for_current_user(): void
    {
        $user = User::factory()->withWebAuthn()->create();

        /** @var WebAuthnCredential $credential */
        $credential = $user->webAuthnCredentials()->first();

        $response = $this->actingAs($user)
            ->deleteJson(route('webauthn.destroy', ['credentialId' => $credential->getKey()]));

        $response->assertOk()
            ->assertJson(['message' => 'Ключ удалён.']);

        $this->assertDatabaseMissing('webauthn_credentials', [
            'id' => $credential->getKey(),
        ]);
    }

    public function test_destroy_returns_not_found_for_other_users_credential(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->withWebAuthn()->create();

        /** @var WebAuthnCredential $credential */
        $credential = $otherUser->webAuthnCredentials()->first();

        $response = $this->actingAs($user)
            ->deleteJson(route('webauthn.destroy', ['credentialId' => $credential->getKey()]));

        $response->assertNotFound();

        $this->assertDatabaseHas('webauthn_credentials', [
            'id' => $credential->getKey(),
        ]);
    }

    public function test_destroy_returns_not_found_for_nonexistent_credential(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->deleteJson(route('webauthn.destroy', ['credentialId' => 'nonexistent']));

        $response->assertNotFound();
    }
}
