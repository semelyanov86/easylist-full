<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Actions\Dashboard\GetDashboardFavoriteJobsAction;
use App\Models\Job;
use App\Models\JobStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetDashboardFavoriteJobsActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_only_favorite_jobs_for_user(): void
    {
        $user = User::factory()->create();
        $status = JobStatus::factory()->create(['user_id' => $user->id]);

        Job::factory()->favorite()->for($user)->create(['job_status_id' => $status->id]);
        Job::factory()->for($user)->create(['job_status_id' => $status->id]);

        $action = resolve(GetDashboardFavoriteJobsAction::class);
        $result = $action->execute($user);

        $this->assertCount(1, $result);
        $this->assertSame($status->title, $result[0]->status_title);
    }

    public function test_does_not_return_non_favorite_jobs(): void
    {
        $user = User::factory()->create();

        Job::factory()->count(3)->for($user)->create();

        $action = resolve(GetDashboardFavoriteJobsAction::class);
        $result = $action->execute($user);

        $this->assertSame([], $result);
    }

    public function test_does_not_return_favorite_jobs_of_another_user(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        Job::factory()->favorite()->for($user1)->create();
        Job::factory()->favorite()->for($user2)->create();

        $action = resolve(GetDashboardFavoriteJobsAction::class);
        $result = $action->execute($user1);

        $this->assertCount(1, $result);
    }

    public function test_excludes_soft_deleted_jobs(): void
    {
        $user = User::factory()->create();

        $job = Job::factory()->favorite()->for($user)->create();
        $job->delete();

        Job::factory()->favorite()->for($user)->create();

        $action = resolve(GetDashboardFavoriteJobsAction::class);
        $result = $action->execute($user);

        $this->assertCount(1, $result);
    }

    public function test_limit_and_sorted_by_created_at_desc(): void
    {
        $user = User::factory()->create();
        $status = JobStatus::factory()->create(['user_id' => $user->id]);

        $oldest = Job::factory()->favorite()->for($user)->create([
            'job_status_id' => $status->id,
            'title' => 'Старая',
            'created_at' => now()->subDays(3),
        ]);

        $middle = Job::factory()->favorite()->for($user)->create([
            'job_status_id' => $status->id,
            'title' => 'Средняя',
            'created_at' => now()->subDays(2),
        ]);

        $newest = Job::factory()->favorite()->for($user)->create([
            'job_status_id' => $status->id,
            'title' => 'Новая',
            'created_at' => now()->subDay(),
        ]);

        $action = resolve(GetDashboardFavoriteJobsAction::class);
        $result = $action->execute($user, limit: 2);

        $this->assertCount(2, $result);
        $this->assertSame($newest->id, $result[0]->id);
        $this->assertSame($middle->id, $result[1]->id);
    }
}
