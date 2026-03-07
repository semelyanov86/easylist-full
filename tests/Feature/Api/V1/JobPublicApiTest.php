<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\Job;
use App\Models\JobCategory;
use App\Models\JobStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobPublicApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_show_returns_job_by_uuid(): void
    {
        $user = User::factory()->create();
        $status = JobStatus::factory()->for($user)->create();
        $category = JobCategory::factory()->for($user)->create();
        $job = Job::factory()->shared()->for($user)->for($status, 'status')->for($category, 'category')->create();

        $response = $this->getJson("/api/v1/public/jobs/{$job->uuid}");

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/vnd.api+json')
            ->assertJsonPath('data.type', 'jobs')
            ->assertJsonPath('data.attributes.title', $job->title)
            ->assertJsonPath('data.attributes.company_name', $job->company_name);
    }

    public function test_public_show_does_not_require_authentication(): void
    {
        $user = User::factory()->create();
        $status = JobStatus::factory()->for($user)->create();
        $category = JobCategory::factory()->for($user)->create();
        $job = Job::factory()->shared()->for($user)->for($status, 'status')->for($category, 'category')->create();

        $response = $this->getJson("/api/v1/public/jobs/{$job->uuid}");

        $response->assertOk();
    }

    public function test_public_show_returns_404_for_invalid_uuid(): void
    {
        $response = $this->getJson('/api/v1/public/jobs/invalid-uuid');

        $response->assertNotFound();
    }
}
