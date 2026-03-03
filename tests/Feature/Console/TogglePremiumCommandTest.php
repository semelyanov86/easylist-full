<?php

declare(strict_types=1);

namespace Tests\Feature\Console;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\PendingCommand;
use Tests\TestCase;

class TogglePremiumCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_enables_premium_for_non_premium_user(): void
    {
        $user = User::factory()->create(['is_premium' => false]);

        $this->withoutMockingConsoleOutput();

        /** @var int $exitCode */
        $exitCode = $this->artisan('user:toggle-premium', ['email' => $user->email]);

        $this->assertSame(0, $exitCode);

        $user->refresh();
        $this->assertTrue($user->is_premium);
    }

    public function test_disables_premium_for_premium_user(): void
    {
        $user = User::factory()->premium()->create();

        $this->withoutMockingConsoleOutput();

        /** @var int $exitCode */
        $exitCode = $this->artisan('user:toggle-premium', ['email' => $user->email]);

        $this->assertSame(0, $exitCode);

        $user->refresh();
        $this->assertFalse($user->is_premium);
    }

    public function test_fails_for_nonexistent_user(): void
    {
        /** @var PendingCommand $command */
        $command = $this->artisan('user:toggle-premium', ['email' => 'nonexistent@example.com']);
        $command
            ->expectsOutputToContain('Пользователь не найден')
            ->assertExitCode(1);
    }
}
