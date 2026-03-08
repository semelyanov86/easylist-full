<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Actions\Dashboard\GetDashboardPendingTasksAction;
use App\Models\Job;
use App\Models\JobTask;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetDashboardPendingTasksActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_only_pending_tasks_for_user(): void
    {
        $user = User::factory()->create();
        $job = Job::factory()->for($user)->create();

        JobTask::factory()->for($job)->create([
            'user_id' => $user->id,
            'completed_at' => null,
        ]);

        $action = resolve(GetDashboardPendingTasksAction::class);
        $result = $action->execute($user);

        $this->assertCount(1, $result);
    }

    public function test_does_not_return_completed_tasks(): void
    {
        $user = User::factory()->create();
        $job = Job::factory()->for($user)->create();

        JobTask::factory()->for($job)->create([
            'user_id' => $user->id,
            'completed_at' => null,
        ]);

        JobTask::factory()->for($job)->completed()->create([
            'user_id' => $user->id,
        ]);

        $action = resolve(GetDashboardPendingTasksAction::class);
        $result = $action->execute($user);

        $this->assertCount(1, $result);
    }

    public function test_does_not_return_tasks_of_another_user(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $job1 = Job::factory()->for($user1)->create();
        $job2 = Job::factory()->for($user2)->create();

        JobTask::factory()->for($job1)->create([
            'user_id' => $user1->id,
            'completed_at' => null,
        ]);

        JobTask::factory()->for($job2)->create([
            'user_id' => $user2->id,
            'completed_at' => null,
        ]);

        $action = resolve(GetDashboardPendingTasksAction::class);
        $result = $action->execute($user1);

        $this->assertCount(1, $result);
    }

    public function test_sorted_by_deadline_asc_nulls_last(): void
    {
        $user = User::factory()->create();
        $job = Job::factory()->for($user)->create();

        $taskNoDeadline = JobTask::factory()->for($job)->create([
            'user_id' => $user->id,
            'title' => 'Без дедлайна',
            'deadline' => null,
            'completed_at' => null,
        ]);

        $taskLate = JobTask::factory()->for($job)->create([
            'user_id' => $user->id,
            'title' => 'Поздний дедлайн',
            'deadline' => now()->addDays(10),
            'completed_at' => null,
        ]);

        $taskSoon = JobTask::factory()->for($job)->create([
            'user_id' => $user->id,
            'title' => 'Ближайший дедлайн',
            'deadline' => now()->addDay(),
            'completed_at' => null,
        ]);

        $action = resolve(GetDashboardPendingTasksAction::class);
        $result = $action->execute($user);

        $this->assertCount(3, $result);
        $this->assertSame($taskSoon->id, $result[0]->id);
        $this->assertSame($taskLate->id, $result[1]->id);
        $this->assertSame($taskNoDeadline->id, $result[2]->id);
    }

    public function test_limit_works(): void
    {
        $user = User::factory()->create();
        $job = Job::factory()->for($user)->create();

        JobTask::factory()->count(5)->for($job)->create([
            'user_id' => $user->id,
            'completed_at' => null,
        ]);

        $action = resolve(GetDashboardPendingTasksAction::class);
        $result = $action->execute($user, limit: 3);

        $this->assertCount(3, $result);
    }

    public function test_returns_empty_array_when_no_tasks(): void
    {
        $user = User::factory()->create();

        $action = resolve(GetDashboardPendingTasksAction::class);
        $result = $action->execute($user);

        $this->assertSame([], $result);
    }

    public function test_contains_job_fields(): void
    {
        $user = User::factory()->create();
        $job = Job::factory()->for($user)->create([
            'title' => 'PHP Developer',
            'company_name' => 'Acme Corp',
        ]);

        JobTask::factory()->for($job)->create([
            'user_id' => $user->id,
            'title' => 'Подготовить резюме',
            'completed_at' => null,
        ]);

        $action = resolve(GetDashboardPendingTasksAction::class);
        $result = $action->execute($user);

        $this->assertCount(1, $result);
        $first = $result[0];
        $this->assertSame($job->id, $first->job_id);
        $this->assertSame('PHP Developer', $first->job_title);
        $this->assertSame('Acme Corp', $first->job_company_name);
        $this->assertSame('Подготовить резюме', $first->title);
    }
}
