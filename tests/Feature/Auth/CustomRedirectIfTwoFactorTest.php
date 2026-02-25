<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Features;
use Tests\TestCase;

class CustomRedirectIfTwoFactorTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_with_totp_only_gets_totp_method(): void
    {
        if (! Features::canManageTwoFactorAuthentication()) {
            $this->markTestSkipped('Two-factor authentication is not enabled.');
        }

        $user = User::factory()->withTwoFactor()->create();

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('two-factor.login'));

        $this->assertEquals(['totp'], session('login.2fa_methods'));
    }

    public function test_user_with_webauthn_only_gets_webauthn_method(): void
    {
        if (! Features::canManageTwoFactorAuthentication()) {
            $this->markTestSkipped('Two-factor authentication is not enabled.');
        }

        $user = User::factory()->withWebAuthn()->create();

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('two-factor.login'));

        $this->assertEquals(['webauthn'], session('login.2fa_methods'));
    }

    public function test_user_with_both_methods_gets_both(): void
    {
        if (! Features::canManageTwoFactorAuthentication()) {
            $this->markTestSkipped('Two-factor authentication is not enabled.');
        }

        $user = User::factory()->withTwoFactor()->withWebAuthn()->create();

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('two-factor.login'));

        $this->assertEquals(['totp', 'webauthn'], session('login.2fa_methods'));
    }

    public function test_user_without_2fa_logs_in_directly(): void
    {
        $user = User::factory()->create();

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_two_factor_challenge_receives_available_methods_prop(): void
    {
        if (! Features::canManageTwoFactorAuthentication()) {
            $this->markTestSkipped('Two-factor authentication is not enabled.');
        }

        $user = User::factory()->withTwoFactor()->withWebAuthn()->create();

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response = $this->get(route('two-factor.login'));

        $response->assertOk()
            ->assertInertia(
                fn (\Inertia\Testing\AssertableInertia $page) => $page
                    ->component('auth/TwoFactorChallenge')
                    ->has('availableMethods')
                    ->where('availableMethods', ['totp', 'webauthn'])
                    ->has('intendedUrl')
            );
    }
}
