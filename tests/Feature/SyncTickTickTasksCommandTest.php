<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Contracts\TickTickClientContract;
use App\Models\Job;
use App\Models\JobCategory;
use App\Models\JobStatus;
use App\Models\JobTask;
use App\Models\User;
use App\Services\FakeTickTickClientService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class SyncTickTickTasksCommandTest extends TestCase
{
    use RefreshDatabase;

    private FakeTickTickClientService $fakeClient;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->fakeClient = new FakeTickTickClientService();
        $this->app->instance(TickTickClientContract::class, $this->fakeClient);
    }

    public function test_command_completes_task_when_ticktick_status_is_2(): void
    {
        $this->fakeClient->withTaskStatus(2);

        $user = User::factory()->withTickTick()->create();
        $job = $this->createJobForUser($user);
        $task = JobTask::factory()->create([
            'job_id' => $job->id,
            'user_id' => $user->id,
            'external_id' => 'tt-sync-1',
            'completed_at' => null,
        ]);

        $this->withoutMockingConsoleOutput();

        /** @var int $exitCode */
        $exitCode = $this->artisan('ticktick:sync');
        $this->assertSame(0, $exitCode);

        $freshTask = $task->fresh();
        $this->assertNotNull($freshTask);
        $this->assertNotNull($freshTask->completed_at);

        $activity = Activity::query()
            ->where('log_name', 'job')
            ->where('event', 'task_completed')
            ->where('subject_id', $job->id)
            ->latest()
            ->first();

        $this->assertNotNull($activity);
        $this->assertInstanceOf(Activity::class, $activity);
        $this->assertSame('Задача выполнена (TickTick)', $activity->description);
    }

    public function test_command_does_not_change_task_when_ticktick_status_is_0(): void
    {
        $this->fakeClient->withTaskStatus(0);

        $user = User::factory()->withTickTick()->create();
        $job = $this->createJobForUser($user);
        $task = JobTask::factory()->create([
            'job_id' => $job->id,
            'user_id' => $user->id,
            'external_id' => 'tt-sync-2',
            'completed_at' => null,
        ]);

        $this->withoutMockingConsoleOutput();

        /** @var int $exitCode */
        $exitCode = $this->artisan('ticktick:sync');
        $this->assertSame(0, $exitCode);

        $freshTask = $task->fresh();
        $this->assertNotNull($freshTask);
        $this->assertNull($freshTask->completed_at);
    }

    public function test_command_deletes_task_when_ticktick_returns_404(): void
    {
        $this->fakeClient->shouldReturn404();

        $user = User::factory()->withTickTick()->create();
        $job = $this->createJobForUser($user);
        $task = JobTask::factory()->create([
            'job_id' => $job->id,
            'user_id' => $user->id,
            'external_id' => 'tt-deleted',
            'completed_at' => null,
        ]);

        $this->withoutMockingConsoleOutput();

        /** @var int $exitCode */
        $exitCode = $this->artisan('ticktick:sync');
        $this->assertSame(0, $exitCode);

        $this->assertDatabaseMissing('job_tasks', ['id' => $task->id]);

        $activity = Activity::query()
            ->where('log_name', 'job')
            ->where('event', 'task_removed')
            ->where('subject_id', $job->id)
            ->latest()
            ->first();

        $this->assertNotNull($activity);
        $this->assertInstanceOf(Activity::class, $activity);
        $this->assertSame('Задача удалена (TickTick)', $activity->description);
    }

    public function test_command_skips_users_without_ticktick(): void
    {
        User::factory()->create();

        $this->withoutMockingConsoleOutput();

        /** @var int $exitCode */
        $exitCode = $this->artisan('ticktick:sync');
        $this->assertSame(0, $exitCode);

        $this->assertEmpty($this->fakeClient->getCalls());
    }

    public function test_command_skips_already_completed_tasks(): void
    {
        $user = User::factory()->withTickTick()->create();
        $job = $this->createJobForUser($user);
        JobTask::factory()->completed()->create([
            'job_id' => $job->id,
            'user_id' => $user->id,
            'external_id' => 'tt-completed',
        ]);

        $this->withoutMockingConsoleOutput();

        /** @var int $exitCode */
        $exitCode = $this->artisan('ticktick:sync');
        $this->assertSame(0, $exitCode);

        $this->assertEmpty($this->fakeClient->getCalls());
    }

    public function test_command_skips_tasks_without_external_id(): void
    {
        $user = User::factory()->withTickTick()->create();
        $job = $this->createJobForUser($user);
        JobTask::factory()->create([
            'job_id' => $job->id,
            'user_id' => $user->id,
            'external_id' => null,
        ]);

        $this->withoutMockingConsoleOutput();

        /** @var int $exitCode */
        $exitCode = $this->artisan('ticktick:sync');
        $this->assertSame(0, $exitCode);

        $this->assertEmpty($this->fakeClient->getCalls());
    }

    public function test_command_continues_on_error(): void
    {
        $this->fakeClient->shouldFail();

        $user = User::factory()->withTickTick()->create();
        $job = $this->createJobForUser($user);
        JobTask::factory()->create([
            'job_id' => $job->id,
            'user_id' => $user->id,
            'external_id' => 'tt-error',
            'completed_at' => null,
        ]);

        $this->withoutMockingConsoleOutput();

        /** @var int $exitCode */
        $exitCode = $this->artisan('ticktick:sync');
        $this->assertSame(0, $exitCode);

        $freshTask = JobTask::where('external_id', 'tt-error')->first();
        $this->assertNotNull($freshTask);
        $this->assertNull($freshTask->completed_at);
    }

    private function createJobForUser(User $user): Job
    {
        $status = JobStatus::factory()->for($user)->create();
        $category = JobCategory::factory()->for($user)->create();

        return Job::factory()->for($user)->create([
            'job_status_id' => $status->id,
            'job_category_id' => $category->id,
        ]);
    }
}
