<?php

declare(strict_types=1);

namespace Tests\Feature\Console;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\PendingCommand;
use Tests\TestCase;

class CreateUserCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_user_with_given_name_and_email(): void
    {
        $this->withoutMockingConsoleOutput();

        /** @var int $exitCode */
        $exitCode = $this->artisan('user:create', [
            'name' => 'Тест Юзер',
            'email' => 'test@example.com',
        ]);

        $this->assertSame(0, $exitCode);

        $this->assertDatabaseHas('users', [
            'name' => 'Тест Юзер',
            'email' => 'test@example.com',
            'is_premium' => false,
        ]);
    }

    public function test_creates_premium_user(): void
    {
        $this->withoutMockingConsoleOutput();

        /** @var int $exitCode */
        $exitCode = $this->artisan('user:create', [
            'name' => 'Premium Юзер',
            'email' => 'premium@example.com',
            '--premium' => true,
        ]);

        $this->assertSame(0, $exitCode);

        $this->assertDatabaseHas('users', [
            'email' => 'premium@example.com',
            'is_premium' => true,
        ]);
    }

    public function test_creates_default_job_statuses_and_categories(): void
    {
        $this->withoutMockingConsoleOutput();

        /** @var int $exitCode */
        $exitCode = $this->artisan('user:create', [
            'name' => 'Тест',
            'email' => 'defaults@example.com',
        ]);

        $this->assertSame(0, $exitCode);

        $user = User::where('email', 'defaults@example.com')->firstOrFail();

        $this->assertCount(8, $user->jobStatuses);
        $this->assertCount(1, $user->jobCategories);
        $this->assertGreaterThan(0, $user->skills()->count());
    }

    public function test_fails_with_duplicate_email(): void
    {
        User::factory()->create(['email' => 'exists@example.com']);

        $this->withoutMockingConsoleOutput();

        /** @var int $exitCode */
        $exitCode = $this->artisan('user:create', [
            'name' => 'Дубликат',
            'email' => 'exists@example.com',
        ]);

        $this->assertSame(1, $exitCode);
    }

    public function test_output_shows_password(): void
    {
        /** @var PendingCommand $command */
        $command = $this->artisan('user:create', [
            'name' => 'Output Test',
            'email' => 'output@example.com',
        ]);

        $command
            ->expectsOutputToContain('Пользователь успешно создан.')
            ->expectsOutputToContain('Пароль:')
            ->assertExitCode(0);
    }
}
