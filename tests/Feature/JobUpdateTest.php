<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Job;
use App\Models\JobCategory;
use App\Models\JobStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_user_can_update_a_job(): void
    {
        $user = User::factory()->create();
        $status = JobStatus::factory()->for($user)->create();
        $category = JobCategory::factory()->for($user)->create();
        $job = Job::factory()->for($user)->for($status, 'status')->for($category, 'category')->create();

        $newStatus = JobStatus::factory()->for($user)->create();
        $newCategory = JobCategory::factory()->for($user)->create();

        $response = $this->actingAs($user)->patch(route('jobs.update', $job), [
            'title' => 'Updated Title',
            'company_name' => 'Updated Company',
            'job_status_id' => $newStatus->id,
            'job_category_id' => $newCategory->id,
            'description' => 'Новое описание',
            'job_url' => 'https://example.com/updated',
            'salary' => 200000,
            'location_city' => 'Санкт-Петербург',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('job_listings', [
            'id' => $job->id,
            'title' => 'Updated Title',
            'company_name' => 'Updated Company',
            'job_status_id' => $newStatus->id,
            'job_category_id' => $newCategory->id,
            'description' => 'Новое описание',
            'job_url' => 'https://example.com/updated',
            'salary' => 200000,
            'location_city' => 'Санкт-Петербург',
        ]);
    }

    public function test_required_fields_are_validated(): void
    {
        $user = User::factory()->create();
        $status = JobStatus::factory()->for($user)->create();
        $category = JobCategory::factory()->for($user)->create();
        $job = Job::factory()->for($user)->for($status, 'status')->for($category, 'category')->create();

        $response = $this->actingAs($user)->patch(route('jobs.update', $job), []);

        $response->assertSessionHasErrors(['title', 'company_name', 'job_status_id', 'job_category_id']);
    }

    public function test_cannot_update_another_users_job(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $status = JobStatus::factory()->for($otherUser)->create();
        $category = JobCategory::factory()->for($otherUser)->create();
        $job = Job::factory()->for($otherUser)->for($status, 'status')->for($category, 'category')->create();

        $response = $this->actingAs($user)->patch(route('jobs.update', $job), [
            'title' => 'Hacked',
            'company_name' => 'Hacked',
            'job_status_id' => $status->id,
            'job_category_id' => $category->id,
        ]);

        $response->assertForbidden();
    }

    public function test_cannot_update_job_with_another_users_status(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $status = JobStatus::factory()->for($user)->create();
        $category = JobCategory::factory()->for($user)->create();
        $job = Job::factory()->for($user)->for($status, 'status')->for($category, 'category')->create();

        $otherStatus = JobStatus::factory()->for($otherUser)->create();

        $response = $this->actingAs($user)->patch(route('jobs.update', $job), [
            'title' => 'Developer',
            'company_name' => 'Test',
            'job_status_id' => $otherStatus->id,
            'job_category_id' => $category->id,
        ]);

        $response->assertForbidden();
    }

    public function test_cannot_update_job_with_another_users_category(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $status = JobStatus::factory()->for($user)->create();
        $category = JobCategory::factory()->for($user)->create();
        $job = Job::factory()->for($user)->for($status, 'status')->for($category, 'category')->create();

        $otherCategory = JobCategory::factory()->for($otherUser)->create();

        $response = $this->actingAs($user)->patch(route('jobs.update', $job), [
            'title' => 'Developer',
            'company_name' => 'Test',
            'job_status_id' => $status->id,
            'job_category_id' => $otherCategory->id,
        ]);

        $response->assertForbidden();
    }

    public function test_invalid_url_fails_validation(): void
    {
        $user = User::factory()->create();
        $status = JobStatus::factory()->for($user)->create();
        $category = JobCategory::factory()->for($user)->create();
        $job = Job::factory()->for($user)->for($status, 'status')->for($category, 'category')->create();

        $response = $this->actingAs($user)->patch(route('jobs.update', $job), [
            'title' => 'Developer',
            'company_name' => 'Test',
            'job_status_id' => $status->id,
            'job_category_id' => $category->id,
            'job_url' => 'not-a-url',
        ]);

        $response->assertSessionHasErrors('job_url');
    }

    public function test_negative_salary_fails_validation(): void
    {
        $user = User::factory()->create();
        $status = JobStatus::factory()->for($user)->create();
        $category = JobCategory::factory()->for($user)->create();
        $job = Job::factory()->for($user)->for($status, 'status')->for($category, 'category')->create();

        $response = $this->actingAs($user)->patch(route('jobs.update', $job), [
            'title' => 'Developer',
            'company_name' => 'Test',
            'job_status_id' => $status->id,
            'job_category_id' => $category->id,
            'salary' => -1,
        ]);

        $response->assertSessionHasErrors('salary');
    }

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $user = User::factory()->create();
        $status = JobStatus::factory()->for($user)->create();
        $category = JobCategory::factory()->for($user)->create();
        $job = Job::factory()->for($user)->for($status, 'status')->for($category, 'category')->create();

        $response = $this->patch(route('jobs.update', $job), [
            'title' => 'Developer',
            'company_name' => 'Test',
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_optional_fields_can_be_cleared(): void
    {
        $user = User::factory()->create();
        $status = JobStatus::factory()->for($user)->create();
        $category = JobCategory::factory()->for($user)->create();
        $job = Job::factory()->for($user)->for($status, 'status')->for($category, 'category')->create([
            'description' => 'Описание',
            'job_url' => 'https://example.com',
            'salary' => 100000,
            'location_city' => 'Москва',
        ]);

        $response = $this->actingAs($user)->patch(route('jobs.update', $job), [
            'title' => $job->title,
            'company_name' => $job->company_name,
            'job_status_id' => $status->id,
            'job_category_id' => $category->id,
            'description' => null,
            'job_url' => null,
            'salary' => null,
            'location_city' => null,
        ]);

        $response->assertRedirect();
        $job->refresh();
        $this->assertNull($job->description);
        $this->assertNull($job->job_url);
        $this->assertNull($job->salary);
        $this->assertNull($job->location_city);
    }
}
