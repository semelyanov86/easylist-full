<?php

declare(strict_types=1);

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class ApiTokenTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_tokens_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get(route('api-tokens.index'));

        $response->assertOk();
    }

    public function test_api_tokens_page_requires_authentication(): void
    {
        $response = $this->get(route('api-tokens.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_user_can_create_api_token(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post(route('api-tokens.store'), [
                'name' => 'Test Token',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('api-tokens.index'));

        $response->assertSessionHas('newToken');

        $this->assertDatabaseHas('personal_access_tokens', [
            'name' => 'Test Token',
            'tokenable_id' => $user->id,
            'tokenable_type' => User::class,
        ]);
    }

    public function test_token_name_is_required(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post(route('api-tokens.store'), [
                'name' => '',
            ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_token_name_cannot_exceed_max_length(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post(route('api-tokens.store'), [
                'name' => str_repeat('a', 256),
            ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_user_can_delete_own_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('Test Token');

        $response = $this
            ->actingAs($user)
            ->delete(route('api-tokens.destroy', $token->accessToken->id));

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('api-tokens.index'));

        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $token->accessToken->id,
        ]);
    }

    public function test_user_cannot_delete_another_users_token(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $token = $otherUser->createToken('Other Token');

        $this
            ->actingAs($user)
            ->delete(route('api-tokens.destroy', $token->accessToken->id));

        $this->assertDatabaseHas('personal_access_tokens', [
            'id' => $token->accessToken->id,
        ]);
    }

    public function test_tokens_list_contains_only_own_tokens(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $user->createToken('My Token');
        $otherUser->createToken('Other Token');

        $response = $this
            ->actingAs($user)
            ->get(route('api-tokens.index'));

        $response->assertOk();

        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('settings/ApiTokens')
                ->has('tokens', 1)
                ->where('tokens.0.name', 'My Token')
        );
    }
}
