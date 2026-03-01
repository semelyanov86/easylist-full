<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Actions\Job\GetJobActivityTimelineAction;
use App\Models\Job;
use App\Models\JobCategory;
use App\Models\JobStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetJobActivityTimelineActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_activities_for_job(): void
    {
        $user = User::factory()->create();
        $status = JobStatus::factory()->for($user)->create();
        $category = JobCategory::factory()->for($user)->create();

        $this->actingAs($user);

        $job = Job::factory()->for($user)->create([
            'job_status_id' => $status->id,
            'job_category_id' => $category->id,
        ]);

        $action = resolve(GetJobActivityTimelineAction::class);
        $result = $action->execute($job);

        $this->assertNotEmpty($result);

        $first = $result[0];
        $this->assertSame('Вакансия создана', $first->description);
        $this->assertSame('created', $first->event);
    }

    public function test_does_not_return_activities_from_other_jobs(): void
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

        $action = resolve(GetJobActivityTimelineAction::class);
        $result = $action->execute($job1);

        foreach ($result as $item) {
            $this->assertSame('Вакансия создана', $item->description);
        }

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

        $action = resolve(GetJobActivityTimelineAction::class);
        $result = $action->execute($job);

        $this->assertCount(2, $result);
        $this->assertSame('updated', $result[0]->event);
        $this->assertSame('created', $result[1]->event);
    }
}
