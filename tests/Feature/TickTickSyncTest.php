<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\TickTick\SyncTickTickTaskCreated;
use App\Jobs\TickTick\SyncTickTickTaskDeleted;
use App\Jobs\TickTick\SyncTickTickTaskToggled;
use App\Jobs\TickTick\SyncTickTickTaskUpdated;
use App\Models\Job;
use App\Models\JobCategory;
use App\Models\JobStatus;
use App\Models\JobTask;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class TickTickSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_task_dispatches_sync_job_when_user_has_ticktick(): void
    {
        Queue::fake();

        $user = User::factory()->withTickTick()->create();
        $job = $this->createJobForUser($user);

        $this->actingAs($user)->post(route('job-tasks.store', $job), [
            'title' => 'Задача для TickTick',
        ]);

        Queue::assertPushed(SyncTickTickTaskCreated::class);
    }

    public function test_create_task_does_not_dispatch_sync_job_when_user_has_no_ticktick(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $job = $this->createJobForUser($user);

        $this->actingAs($user)->post(route('job-tasks.store', $job), [
            'title' => 'Задача без TickTick',
        ]);

        Queue::assertNotPushed(SyncTickTickTaskCreated::class);
    }

    public function test_update_task_dispatches_sync_job_when_task_has_external_id(): void
    {
        Queue::fake();

        $user = User::factory()->withTickTick()->create();
        $job = $this->createJobForUser($user);
        $task = JobTask::factory()->create([
            'job_id' => $job->id,
            'user_id' => $user->id,
            'external_id' => 'tt-123',
        ]);

        $this->actingAs($user)->patch(route('job-tasks.update', $task), [
            'title' => 'Обновлённая задача',
        ]);

        Queue::assertPushed(SyncTickTickTaskUpdated::class);
    }

    public function test_update_task_does_not_dispatch_sync_job_when_no_external_id(): void
    {
        Queue::fake();

        $user = User::factory()->withTickTick()->create();
        $job = $this->createJobForUser($user);
        $task = JobTask::factory()->create([
            'job_id' => $job->id,
            'user_id' => $user->id,
            'external_id' => null,
        ]);

        $this->actingAs($user)->patch(route('job-tasks.update', $task), [
            'title' => 'Обновлённая задача',
        ]);

        Queue::assertNotPushed(SyncTickTickTaskUpdated::class);
    }

    public function test_update_task_does_not_dispatch_sync_job_when_user_has_no_ticktick(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $job = $this->createJobForUser($user);
        $task = JobTask::factory()->create([
            'job_id' => $job->id,
            'user_id' => $user->id,
            'external_id' => 'tt-123',
        ]);

        $this->actingAs($user)->patch(route('job-tasks.update', $task), [
            'title' => 'Обновлённая задача',
        ]);

        Queue::assertNotPushed(SyncTickTickTaskUpdated::class);
    }

    public function test_toggle_task_dispatches_sync_job_when_task_has_external_id(): void
    {
        Queue::fake();

        $user = User::factory()->withTickTick()->create();
        $job = $this->createJobForUser($user);
        $task = JobTask::factory()->create([
            'job_id' => $job->id,
            'user_id' => $user->id,
            'external_id' => 'tt-123',
        ]);

        $this->actingAs($user)->patch(route('job-tasks.toggle', $task));

        Queue::assertPushed(SyncTickTickTaskToggled::class);
    }

    public function test_toggle_task_does_not_dispatch_sync_job_when_no_external_id(): void
    {
        Queue::fake();

        $user = User::factory()->withTickTick()->create();
        $job = $this->createJobForUser($user);
        $task = JobTask::factory()->create([
            'job_id' => $job->id,
            'user_id' => $user->id,
            'external_id' => null,
        ]);

        $this->actingAs($user)->patch(route('job-tasks.toggle', $task));

        Queue::assertNotPushed(SyncTickTickTaskToggled::class);
    }

    public function test_delete_task_dispatches_sync_job_when_task_has_external_id(): void
    {
        Queue::fake();

        $user = User::factory()->withTickTick()->create();
        $job = $this->createJobForUser($user);
        $task = JobTask::factory()->create([
            'job_id' => $job->id,
            'user_id' => $user->id,
            'external_id' => 'tt-123',
        ]);

        $this->actingAs($user)->delete(route('job-tasks.destroy', $task));

        Queue::assertPushed(SyncTickTickTaskDeleted::class);
    }

    public function test_delete_task_does_not_dispatch_sync_job_when_no_external_id(): void
    {
        Queue::fake();

        $user = User::factory()->withTickTick()->create();
        $job = $this->createJobForUser($user);
        $task = JobTask::factory()->create([
            'job_id' => $job->id,
            'user_id' => $user->id,
            'external_id' => null,
        ]);

        $this->actingAs($user)->delete(route('job-tasks.destroy', $task));

        Queue::assertNotPushed(SyncTickTickTaskDeleted::class);
    }

    public function test_delete_task_does_not_dispatch_sync_job_when_user_has_no_ticktick(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $job = $this->createJobForUser($user);
        $task = JobTask::factory()->create([
            'job_id' => $job->id,
            'user_id' => $user->id,
            'external_id' => 'tt-123',
        ]);

        $this->actingAs($user)->delete(route('job-tasks.destroy', $task));

        Queue::assertNotPushed(SyncTickTickTaskDeleted::class);
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
