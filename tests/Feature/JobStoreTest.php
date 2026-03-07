<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Job;
use App\Models\JobCategory;
use App\Models\JobStatus;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_user_can_create_a_job(): void
    {
        $user = User::factory()->create();
        $status = JobStatus::factory()->for($user)->create();
        $category = JobCategory::factory()->for($user)->create();

        $response = $this->actingAs($user)->post(route('jobs.store'), [
            'title' => 'Frontend Developer',
            'company_name' => 'Яндекс',
            'job_status_id' => $status->id,
            'job_category_id' => $category->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('job_listings', [
            'user_id' => $user->id,
            'title' => 'Frontend Developer',
            'company_name' => 'Яндекс',
            'job_status_id' => $status->id,
            'job_category_id' => $category->id,
        ]);
    }

    public function test_required_fields_are_validated(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('jobs.store'), []);

        $response->assertSessionHasErrors(['title', 'company_name', 'job_status_id', 'job_category_id']);
    }

    public function test_optional_fields_are_stored(): void
    {
        $user = User::factory()->create();
        $status = JobStatus::factory()->for($user)->create();
        $category = JobCategory::factory()->for($user)->create();

        $response = $this->actingAs($user)->post(route('jobs.store'), [
            'title' => 'Backend Developer',
            'company_name' => 'VK',
            'job_status_id' => $status->id,
            'job_category_id' => $category->id,
            'description' => 'Описание вакансии',
            'job_url' => 'https://example.com/vacancy',
            'salary' => 150000,
            'location_city' => 'Москва',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('job_listings', [
            'user_id' => $user->id,
            'title' => 'Backend Developer',
            'description' => 'Описание вакансии',
            'job_url' => 'https://example.com/vacancy',
            'salary' => 150000,
            'location_city' => 'Москва',
        ]);
    }

    public function test_invalid_url_fails_validation(): void
    {
        $user = User::factory()->create();
        $status = JobStatus::factory()->for($user)->create();
        $category = JobCategory::factory()->for($user)->create();

        $response = $this->actingAs($user)->post(route('jobs.store'), [
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

        $response = $this->actingAs($user)->post(route('jobs.store'), [
            'title' => 'Developer',
            'company_name' => 'Test',
            'job_status_id' => $status->id,
            'job_category_id' => $category->id,
            'salary' => -1,
        ]);

        $response->assertSessionHasErrors('salary');
    }

    public function test_cannot_create_job_with_another_users_status(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $otherStatus = JobStatus::factory()->for($otherUser)->create();
        $category = JobCategory::factory()->for($user)->create();

        $response = $this->actingAs($user)->post(route('jobs.store'), [
            'title' => 'Developer',
            'company_name' => 'Test',
            'job_status_id' => $otherStatus->id,
            'job_category_id' => $category->id,
        ]);

        $response->assertForbidden();
    }

    public function test_cannot_create_job_with_another_users_category(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $status = JobStatus::factory()->for($user)->create();
        $otherCategory = JobCategory::factory()->for($otherUser)->create();

        $response = $this->actingAs($user)->post(route('jobs.store'), [
            'title' => 'Developer',
            'company_name' => 'Test',
            'job_status_id' => $status->id,
            'job_category_id' => $otherCategory->id,
        ]);

        $response->assertForbidden();
    }

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->post(route('jobs.store'), [
            'title' => 'Developer',
            'company_name' => 'Test',
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_job_created_with_skills(): void
    {
        $user = User::factory()->create();
        $status = JobStatus::factory()->for($user)->create();
        $category = JobCategory::factory()->for($user)->create();
        $skill1 = Skill::factory()->for($user)->create(['title' => 'PHP']);
        $skill2 = Skill::factory()->for($user)->create(['title' => 'Laravel']);

        $response = $this->actingAs($user)->post(route('jobs.store'), [
            'title' => 'Developer',
            'company_name' => 'Test',
            'job_status_id' => $status->id,
            'job_category_id' => $category->id,
            'skill_ids' => [$skill1->id, $skill2->id],
        ]);

        $response->assertRedirect();
        $job = Job::query()->where('user_id', $user->id)->first();
        $this->assertNotNull($job);
        $this->assertCount(2, $job->skills);
        $this->assertTrue($job->skills->contains($skill1));
        $this->assertTrue($job->skills->contains($skill2));
    }

    public function test_job_created_without_skills(): void
    {
        $user = User::factory()->create();
        $status = JobStatus::factory()->for($user)->create();
        $category = JobCategory::factory()->for($user)->create();

        $response = $this->actingAs($user)->post(route('jobs.store'), [
            'title' => 'Developer',
            'company_name' => 'Test',
            'job_status_id' => $status->id,
            'job_category_id' => $category->id,
        ]);

        $response->assertRedirect();
        $job = Job::query()->where('user_id', $user->id)->first();
        $this->assertNotNull($job);
        $this->assertCount(0, $job->skills);
    }

    public function test_other_users_skills_are_filtered_on_create(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $status = JobStatus::factory()->for($user)->create();
        $category = JobCategory::factory()->for($user)->create();
        $ownSkill = Skill::factory()->for($user)->create(['title' => 'PHP']);
        $otherSkill = Skill::factory()->for($otherUser)->create(['title' => 'Python']);

        $response = $this->actingAs($user)->post(route('jobs.store'), [
            'title' => 'Developer',
            'company_name' => 'Test',
            'job_status_id' => $status->id,
            'job_category_id' => $category->id,
            'skill_ids' => [$ownSkill->id, $otherSkill->id],
        ]);

        $response->assertRedirect();
        $job = Job::query()->where('user_id', $user->id)->first();
        $this->assertNotNull($job);
        $this->assertCount(1, $job->skills);
        $this->assertTrue($job->skills->contains($ownSkill));
        $this->assertFalse($job->skills->contains($otherSkill));
    }

    public function test_resume_version_url_is_stored(): void
    {
        $user = User::factory()->create();
        $status = JobStatus::factory()->for($user)->create();
        $category = JobCategory::factory()->for($user)->create();

        $response = $this->actingAs($user)->post(route('jobs.store'), [
            'title' => 'Developer',
            'company_name' => 'Test',
            'job_status_id' => $status->id,
            'job_category_id' => $category->id,
            'resume_version_url' => 'https://example.com/resume/v1',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('job_listings', [
            'user_id' => $user->id,
            'title' => 'Developer',
            'resume_version_url' => 'https://example.com/resume/v1',
        ]);
    }

    public function test_uuid_is_generated_automatically(): void
    {
        $user = User::factory()->create();
        $status = JobStatus::factory()->for($user)->create();
        $category = JobCategory::factory()->for($user)->create();

        $this->actingAs($user)->post(route('jobs.store'), [
            'title' => 'Developer',
            'company_name' => 'Test',
            'job_status_id' => $status->id,
            'job_category_id' => $category->id,
        ]);

        $job = Job::query()->where('user_id', $user->id)->first();

        $this->assertNotNull($job);
        $this->assertNotNull($job->uuid);
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $job->uuid,
        );
    }
}
