<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\Job;
use App\Models\JobCategory;
use App\Models\JobStatus;
use App\Models\JobTask;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TaskApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Job $job;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $status = JobStatus::factory()->for($this->user)->create();
        $category = JobCategory::factory()->for($this->user)->create();
        $this->job = Job::factory()->for($this->user)->for($status, 'status')->for($category, 'category')->create();
    }

    public function test_pending_returns_only_incomplete_tasks(): void
    {
        Sanctum::actingAs($this->user);

        JobTask::factory()->count(2)->for($this->job)->for($this->user)->create();
        JobTask::factory()->completed()->for($this->job)->for($this->user)->create();

        $response = $this->getJson('/api/v1/tasks/pending');

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/vnd.api+json')
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.type', 'tasks');
    }

    public function test_pending_includes_job_relationship(): void
    {
        Sanctum::actingAs($this->user);

        JobTask::factory()->for($this->job)->for($this->user)->create();

        $response = $this->getJson('/api/v1/tasks/pending');

        $response->assertOk()
            ->assertJsonPath('data.0.relationships.job.data.type', 'jobs')
            ->assertJsonPath('data.0.relationships.job.data.id', (string) $this->job->id);
    }

    public function test_pending_includes_job_in_included(): void
    {
        Sanctum::actingAs($this->user);

        JobTask::factory()->for($this->job)->for($this->user)->create();

        $response = $this->getJson('/api/v1/tasks/pending');

        $response->assertOk()
            ->assertJsonPath('included.0.type', 'jobs')
            ->assertJsonPath('included.0.id', (string) $this->job->id)
            ->assertJsonPath('included.0.attributes.title', $this->job->title)
            ->assertJsonPath('included.0.attributes.company_name', $this->job->company_name);
    }

    public function test_pending_does_not_return_other_users_tasks(): void
    {
        Sanctum::actingAs($this->user);

        $otherUser = User::factory()->create();
        $status = JobStatus::factory()->for($otherUser)->create();
        $category = JobCategory::factory()->for($otherUser)->create();
        $otherJob = Job::factory()->for($otherUser)->for($status, 'status')->for($category, 'category')->create();
        JobTask::factory()->for($otherJob)->for($otherUser)->create();

        $response = $this->getJson('/api/v1/tasks/pending');

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_pending_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/tasks/pending');

        $response->assertUnauthorized();
    }
}
