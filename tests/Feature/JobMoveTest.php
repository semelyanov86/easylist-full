<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Job;
use App\Models\JobCategory;
use App\Models\JobStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobMoveTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_move_own_job_to_another_status(): void
    {
        $user = User::factory()->create();
        $statusA = JobStatus::factory()->for($user)->create();
        $statusB = JobStatus::factory()->for($user)->create();
        $category = JobCategory::factory()->for($user)->create();

        $job = Job::factory()->for($user)->create([
            'job_status_id' => $statusA->id,
            'job_category_id' => $category->id,
        ]);

        $response = $this->actingAs($user)->patch(route('jobs.move', $job), [
            'status_id' => $statusB->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('job_listings', [
            'id' => $job->id,
            'job_status_id' => $statusB->id,
        ]);
    }

    public function test_user_cannot_move_another_users_job(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $status = JobStatus::factory()->for($user)->create();
        $otherStatus = JobStatus::factory()->for($otherUser)->create();
        $otherCategory = JobCategory::factory()->for($otherUser)->create();

        $otherJob = Job::factory()->for($otherUser)->create([
            'job_status_id' => $otherStatus->id,
            'job_category_id' => $otherCategory->id,
        ]);

        $response = $this->actingAs($user)->patch(route('jobs.move', $otherJob), [
            'status_id' => $status->id,
        ]);

        $response->assertForbidden();
    }

    public function test_user_cannot_move_job_to_another_users_status(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $status = JobStatus::factory()->for($user)->create();
        $otherStatus = JobStatus::factory()->for($otherUser)->create();
        $category = JobCategory::factory()->for($user)->create();

        $job = Job::factory()->for($user)->create([
            'job_status_id' => $status->id,
            'job_category_id' => $category->id,
        ]);

        $response = $this->actingAs($user)->patch(route('jobs.move', $job), [
            'status_id' => $otherStatus->id,
        ]);

        $response->assertForbidden();
    }

    public function test_invalid_status_id_fails_validation(): void
    {
        $user = User::factory()->create();
        $status = JobStatus::factory()->for($user)->create();
        $category = JobCategory::factory()->for($user)->create();

        $job = Job::factory()->for($user)->create([
            'job_status_id' => $status->id,
            'job_category_id' => $category->id,
        ]);

        $response = $this->actingAs($user)->patch(route('jobs.move', $job), [
            'status_id' => 99999,
        ]);

        $response->assertSessionHasErrors('status_id');
    }

    public function test_unauthenticated_user_is_redirected(): void
    {
        $user = User::factory()->create();
        $status = JobStatus::factory()->for($user)->create();
        $category = JobCategory::factory()->for($user)->create();

        $job = Job::factory()->for($user)->create([
            'job_status_id' => $status->id,
            'job_category_id' => $category->id,
        ]);

        $response = $this->patch(route('jobs.move', $job), [
            'status_id' => $status->id,
        ]);

        $response->assertRedirect(route('login'));
    }
}
