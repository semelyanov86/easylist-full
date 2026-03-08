<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Actions\Dashboard\GetDashboardActivityAction;
use App\Models\Job;
use App\Models\JobCategory;
use App\Models\JobStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetDashboardActivityActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_activities_for_all_user_jobs(): void
    {
        $user = User::factory()->create();
        $status = JobStatus::factory()->for($user)->create();
        $category = JobCategory::factory()->for($user)->create();

        $this->actingAs($user);

        $job1 = Job::factory()->for($user)->create([
            'job_status_id' => $status->id,
            'job_category_id' => $category->id,
        ]);

        $job2 = Job::factory()->for($user)->create([
            'job_status_id' => $status->id,
            'job_category_id' => $category->id,
        ]);

        $action = resolve(GetDashboardActivityAction::class);
        $result = $action->execute($user);

        $this->assertCount(2, $result);
    }

    public function test_does_not_return_activities_from_other_users(): void
    {
        $user1 = User::factory()->create();
        $status1 = JobStatus::factory()->for($user1)->create();
        $category1 = JobCategory::factory()->for($user1)->create();

        $user2 = User::factory()->create();
        $status2 = JobStatus::factory()->for($user2)->create();
        $category2 = JobCategory::factory()->for($user2)->create();

        $this->actingAs($user1);
        Job::factory()->for($user1)->create([
            'job_status_id' => $status1->id,
            'job_category_id' => $category1->id,
        ]);

        $this->actingAs($user2);
        Job::factory()->for($user2)->create([
            'job_status_id' => $status2->id,
            'job_category_id' => $category2->id,
        ]);

        $action = resolve(GetDashboardActivityAction::class);
        $result = $action->execute($user1);

        $this->assertCount(1, $result);
    }

    public function test_activities_sorted_latest_first(): void
    {
        $user = User::factory()->create();
        $status = JobStatus::factory()->for($user)->create();
        $category = JobCategory::factory()->for($user)->create();

        $this->actingAs($user);

        $job = Job::factory()->for($user)->create([
            'job_status_id' => $status->id,
            'job_category_id' => $category->id,
            'title' => 'Старое',
        ]);

        $job->update(['title' => 'Новое']);

        $action = resolve(GetDashboardActivityAction::class);
        $result = $action->execute($user);

        $this->assertCount(2, $result);
        $this->assertSame('updated', $result[0]->event);
        $this->assertSame('created', $result[1]->event);
    }

    public function test_contains_job_fields(): void
    {
        $user = User::factory()->create();
        $status = JobStatus::factory()->for($user)->create();
        $category = JobCategory::factory()->for($user)->create();

        $this->actingAs($user);

        $job = Job::factory()->for($user)->create([
            'job_status_id' => $status->id,
            'job_category_id' => $category->id,
            'title' => 'PHP Developer',
            'company_name' => 'Acme Corp',
        ]);

        $action = resolve(GetDashboardActivityAction::class);
        $result = $action->execute($user);

        $this->assertNotEmpty($result);
        $first = $result[0];
        $this->assertSame($job->id, $first->job_id);
        $this->assertSame('PHP Developer', $first->job_title);
        $this->assertSame('Acme Corp', $first->job_company_name);
    }

    public function test_limit_works(): void
    {
        $user = User::factory()->create();
        $status = JobStatus::factory()->for($user)->create();
        $category = JobCategory::factory()->for($user)->create();

        $this->actingAs($user);

        Job::factory()->count(5)->for($user)->create([
            'job_status_id' => $status->id,
            'job_category_id' => $category->id,
        ]);

        $action = resolve(GetDashboardActivityAction::class);
        $result = $action->execute($user, limit: 3);

        $this->assertCount(3, $result);
    }

    public function test_returns_empty_array_when_no_jobs(): void
    {
        $user = User::factory()->create();

        $action = resolve(GetDashboardActivityAction::class);
        $result = $action->execute($user);

        $this->assertSame([], $result);
    }
}
