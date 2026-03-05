<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Contracts\TickTickClientContract;
use App\Jobs\TickTick\SyncTickTickTaskCreated;
use App\Jobs\TickTick\SyncTickTickTaskDeleted;
use App\Jobs\TickTick\SyncTickTickTaskToggled;
use App\Jobs\TickTick\SyncTickTickTaskUpdated;
use App\Models\Job;
use App\Models\JobCategory;
use App\Models\JobStatus;
use App\Models\JobTask;
use App\Models\User;
use App\Services\FakeTickTickClientService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TickTickJobExecutionTest extends TestCase
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

    public function test_created_job_saves_external_id(): void
    {
        $this->fakeClient->withCreatedId('tt-new-123');

        $user = User::factory()->withTickTick()->create();
        $job = $this->createJobForUser($user);
        $task = JobTask::factory()->create([
            'job_id' => $job->id,
            'user_id' => $user->id,
            'external_id' => null,
        ]);

        new SyncTickTickTaskCreated($task->id)->handle($this->fakeClient);

        $calls = $this->fakeClient->getCalls();
        $this->assertCount(1, $calls);
        $this->assertSame('createTask', $calls[0]['method']);
        $this->assertSame('tt-new-123', $task->fresh()?->external_id);
    }

    public function test_created_job_skips_when_task_not_found(): void
    {
        new SyncTickTickTaskCreated(999999)->handle($this->fakeClient);

        $this->assertEmpty($this->fakeClient->getCalls());
    }

    public function test_created_job_skips_when_user_has_no_ticktick(): void
    {
        $user = User::factory()->create();
        $job = $this->createJobForUser($user);
        $task = JobTask::factory()->create([
            'job_id' => $job->id,
            'user_id' => $user->id,
        ]);

        new SyncTickTickTaskCreated($task->id)->handle($this->fakeClient);

        $this->assertCount(0, $this->fakeClient->getCalls());
    }

    public function test_updated_job_sends_correct_data(): void
    {
        $user = User::factory()->withTickTick()->create();
        $job = $this->createJobForUser($user);
        $task = JobTask::factory()->create([
            'job_id' => $job->id,
            'user_id' => $user->id,
            'external_id' => 'tt-existing',
            'title' => 'Обновлённая задача',
        ]);

        new SyncTickTickTaskUpdated($task->id)->handle($this->fakeClient);

        $calls = $this->fakeClient->getCalls();
        $this->assertCount(1, $calls);
        $this->assertSame('updateTask', $calls[0]['method']);
        $this->assertSame('tt-existing', $calls[0]['args'][1]);

        /** @var array{title: string} $updateData */
        $updateData = $calls[0]['args'][2];
        $this->assertSame('Обновлённая задача', $updateData['title']);
    }

    public function test_updated_job_skips_when_no_external_id(): void
    {
        $user = User::factory()->withTickTick()->create();
        $job = $this->createJobForUser($user);
        $task = JobTask::factory()->create([
            'job_id' => $job->id,
            'user_id' => $user->id,
            'external_id' => null,
        ]);

        new SyncTickTickTaskUpdated($task->id)->handle($this->fakeClient);

        $this->assertCount(0, $this->fakeClient->getCalls());
    }

    public function test_toggled_job_calls_complete_when_task_completed(): void
    {
        $user = User::factory()->withTickTick()->create();
        $job = $this->createJobForUser($user);
        $task = JobTask::factory()->completed()->create([
            'job_id' => $job->id,
            'user_id' => $user->id,
            'external_id' => 'tt-toggle',
        ]);

        new SyncTickTickTaskToggled($task->id)->handle($this->fakeClient);

        $calls = $this->fakeClient->getCalls();
        $this->assertCount(1, $calls);
        $this->assertSame('completeTask', $calls[0]['method']);
    }

    public function test_toggled_job_calls_update_when_task_reopened(): void
    {
        $user = User::factory()->withTickTick()->create();
        $job = $this->createJobForUser($user);
        $task = JobTask::factory()->create([
            'job_id' => $job->id,
            'user_id' => $user->id,
            'external_id' => 'tt-toggle',
            'completed_at' => null,
        ]);

        new SyncTickTickTaskToggled($task->id)->handle($this->fakeClient);

        $calls = $this->fakeClient->getCalls();
        $this->assertCount(1, $calls);
        $this->assertSame('updateTask', $calls[0]['method']);

        /** @var array{status: int} $updateData */
        $updateData = $calls[0]['args'][2];
        $this->assertSame(0, $updateData['status']);
    }

    public function test_toggled_job_skips_when_no_external_id(): void
    {
        $user = User::factory()->withTickTick()->create();
        $job = $this->createJobForUser($user);
        $task = JobTask::factory()->create([
            'job_id' => $job->id,
            'user_id' => $user->id,
            'external_id' => null,
        ]);

        new SyncTickTickTaskToggled($task->id)->handle($this->fakeClient);

        $this->assertCount(0, $this->fakeClient->getCalls());
    }

    public function test_deleted_job_calls_delete(): void
    {
        new SyncTickTickTaskDeleted('tt-del', 'token-1', 'list-1')->handle($this->fakeClient);

        $calls = $this->fakeClient->getCalls();
        $this->assertCount(1, $calls);
        $this->assertSame('deleteTask', $calls[0]['method']);
    }

    public function test_deleted_job_ignores_404(): void
    {
        $this->fakeClient->shouldReturn404();

        new SyncTickTickTaskDeleted('tt-del', 'token-1', 'list-1')->handle($this->fakeClient);

        // Не выбросило исключение — тест пройден
        $this->assertNotEmpty($this->fakeClient->getCalls());
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
