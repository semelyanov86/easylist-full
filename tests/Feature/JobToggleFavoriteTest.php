<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Job;
use App\Models\JobCategory;
use App\Models\JobStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobToggleFavoriteTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_toggle_favorite_from_false_to_true(): void
    {
        $user = User::factory()->create();
        $status = JobStatus::factory()->for($user)->create();
        $category = JobCategory::factory()->for($user)->create();

        $job = Job::factory()->for($user)->create([
            'job_status_id' => $status->id,
            'job_category_id' => $category->id,
            'is_favorite' => false,
        ]);

        $response = $this->actingAs($user)->patch(route('jobs.toggle-favorite', $job));

        $response->assertRedirect();
        $freshJob = $job->fresh();
        $this->assertNotNull($freshJob);
        $this->assertTrue($freshJob->is_favorite);
    }

    public function test_user_can_toggle_favorite_from_true_to_false(): void
    {
        $user = User::factory()->create();
        $status = JobStatus::factory()->for($user)->create();
        $category = JobCategory::factory()->for($user)->create();

        $job = Job::factory()->for($user)->favorite()->create([
            'job_status_id' => $status->id,
            'job_category_id' => $category->id,
        ]);

        $response = $this->actingAs($user)->patch(route('jobs.toggle-favorite', $job));

        $response->assertRedirect();
        $freshJob = $job->fresh();
        $this->assertNotNull($freshJob);
        $this->assertFalse($freshJob->is_favorite);
    }

    public function test_user_cannot_toggle_favorite_on_another_users_job(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $status = JobStatus::factory()->for($otherUser)->create();
        $category = JobCategory::factory()->for($otherUser)->create();

        $job = Job::factory()->for($otherUser)->create([
            'job_status_id' => $status->id,
            'job_category_id' => $category->id,
        ]);

        $response = $this->actingAs($user)->patch(route('jobs.toggle-favorite', $job));

        $response->assertForbidden();
    }

    public function test_guest_cannot_toggle_favorite(): void
    {
        $user = User::factory()->create();
        $status = JobStatus::factory()->for($user)->create();
        $category = JobCategory::factory()->for($user)->create();

        $job = Job::factory()->for($user)->create([
            'job_status_id' => $status->id,
            'job_category_id' => $category->id,
        ]);

        $response = $this->patch(route('jobs.toggle-favorite', $job));

        $response->assertRedirect(route('login'));
    }
}
