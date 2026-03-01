<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Job;
use App\Models\JobCategory;
use App\Models\JobStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class JobDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_delete_own_job(): void
    {
        $user = User::factory()->create();
        $status = JobStatus::factory()->for($user)->create();
        $category = JobCategory::factory()->for($user)->create();

        $job = Job::factory()->for($user)->create([
            'job_status_id' => $status->id,
            'job_category_id' => $category->id,
        ]);

        $response = $this->actingAs($user)->delete(route('jobs.destroy', $job));

        $response->assertRedirect();
        $this->assertSoftDeleted('job_listings', ['id' => $job->id]);
    }

    public function test_user_cannot_delete_another_users_job(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $status = JobStatus::factory()->for($otherUser)->create();
        $category = JobCategory::factory()->for($otherUser)->create();

        $job = Job::factory()->for($otherUser)->create([
            'job_status_id' => $status->id,
            'job_category_id' => $category->id,
        ]);

        $response = $this->actingAs($user)->delete(route('jobs.destroy', $job));

        $response->assertForbidden();
    }

    public function test_deleted_job_not_shown_in_list(): void
    {
        $user = User::factory()->create();
        $status = JobStatus::factory()->for($user)->create();
        $category = JobCategory::factory()->for($user)->create();

        $job = Job::factory()->for($user)->create([
            'title' => 'Удалённая вакансия',
            'job_status_id' => $status->id,
            'job_category_id' => $category->id,
        ]);

        Job::factory()->for($user)->create([
            'title' => 'Активная вакансия',
            'job_status_id' => $status->id,
            'job_category_id' => $category->id,
        ]);

        $this->actingAs($user)->delete(route('jobs.destroy', $job));

        $response = $this->actingAs($user)->get(route('jobs.index'));

        $response->assertOk();
        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('jobs/Index')
                ->has('jobs.data', 1)
                ->where('jobs.data.0.title', 'Активная вакансия')
        );
    }

    public function test_guest_cannot_delete_job(): void
    {
        $user = User::factory()->create();
        $status = JobStatus::factory()->for($user)->create();
        $category = JobCategory::factory()->for($user)->create();

        $job = Job::factory()->for($user)->create([
            'job_status_id' => $status->id,
            'job_category_id' => $category->id,
        ]);

        $response = $this->delete(route('jobs.destroy', $job));

        $response->assertRedirect(route('login'));
    }
}
