<?php

declare(strict_types=1);

namespace Tests\Feature\Console;

use App\Models\Job;
use App\Models\JobCategory;
use App\Models\JobComment;
use App\Models\JobDocument;
use App\Models\JobStatus;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\PendingCommand;
use Tests\TestCase;

class DeleteUserCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_deletes_user_with_force_flag(): void
    {
        $user = User::factory()->create();

        $this->withoutMockingConsoleOutput();

        /** @var int $exitCode */
        $exitCode = $this->artisan('user:delete', [
            'email' => $user->email,
            '--force' => true,
        ]);

        $this->assertSame(0, $exitCode);
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_deletes_user_jobs_and_documents(): void
    {
        $user = User::factory()->create();
        $status = JobStatus::factory()->for($user)->create();
        $category = JobCategory::factory()->for($user)->create();

        $job = Job::factory()->for($user)->create([
            'job_status_id' => $status->id,
            'job_category_id' => $category->id,
        ]);

        JobDocument::factory()->for($job)->for($user)->create();
        JobComment::factory()->for($job)->for($user)->create();

        $skill = Skill::factory()->for($user)->create();
        $job->skills()->attach($skill);

        $this->withoutMockingConsoleOutput();

        /** @var int $exitCode */
        $exitCode = $this->artisan('user:delete', [
            'email' => $user->email,
            '--force' => true,
        ]);

        $this->assertSame(0, $exitCode);
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseMissing('job_listings', ['user_id' => $user->id]);
        $this->assertDatabaseMissing('job_documents', ['user_id' => $user->id]);
        $this->assertDatabaseMissing('job_comments', ['user_id' => $user->id]);
        $this->assertDatabaseMissing('skills', ['user_id' => $user->id]);
        $this->assertDatabaseMissing('job_statuses', ['user_id' => $user->id]);
        $this->assertDatabaseMissing('job_categories', ['user_id' => $user->id]);
    }

    public function test_deletes_soft_deleted_jobs(): void
    {
        $user = User::factory()->create();
        $status = JobStatus::factory()->for($user)->create();
        $category = JobCategory::factory()->for($user)->create();

        $job = Job::factory()->for($user)->create([
            'job_status_id' => $status->id,
            'job_category_id' => $category->id,
        ]);
        $job->delete();

        $this->assertSoftDeleted('job_listings', ['id' => $job->id]);

        $this->withoutMockingConsoleOutput();

        /** @var int $exitCode */
        $exitCode = $this->artisan('user:delete', [
            'email' => $user->email,
            '--force' => true,
        ]);

        $this->assertSame(0, $exitCode);
        $this->assertDatabaseMissing('job_listings', ['id' => $job->id]);
    }

    public function test_fails_for_nonexistent_user(): void
    {
        /** @var PendingCommand $command */
        $command = $this->artisan('user:delete', [
            'email' => 'nonexistent@example.com',
            '--force' => true,
        ]);

        $command
            ->expectsOutputToContain('Пользователь не найден')
            ->assertExitCode(1);
    }

    public function test_cancel_without_force_flag(): void
    {
        $user = User::factory()->create();

        /** @var PendingCommand $command */
        $command = $this->artisan('user:delete', ['email' => $user->email]);

        $command
            ->expectsConfirmation("Удалить пользователя {$user->email} и все его данные?", 'no')
            ->expectsOutputToContain('Операция отменена')
            ->assertExitCode(0);

        // PendingCommand ещё не выполнен, проверяем после destruct
    }

    public function test_cancel_preserves_user_data(): void
    {
        $user = User::factory()->create();

        $this->withoutMockingConsoleOutput();

        // confirm() без --force вернёт false при отсутствии интерактивного ввода
        /** @var int $exitCode */
        $exitCode = $this->artisan('user:delete', [
            'email' => $user->email,
            '--no-interaction' => true,
        ]);

        $this->assertSame(0, $exitCode);
        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }
}
