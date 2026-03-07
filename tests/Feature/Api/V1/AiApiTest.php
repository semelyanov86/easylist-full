<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Jobs\AnalyzeCompanyJob;
use App\Jobs\FindContactsJob;
use App\Models\Job;
use App\Models\JobCategory;
use App\Models\JobStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AiApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Job $job;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->premium()->create();
        $status = JobStatus::factory()->for($this->user)->create();
        $category = JobCategory::factory()->for($this->user)->create();
        $this->job = Job::factory()->for($this->user)->for($status, 'status')->for($category, 'category')->create();
    }

    public function test_analyze_company_dispatches_job_and_returns_204(): void
    {
        Queue::fake();
        Sanctum::actingAs($this->user);

        $response = $this->postJson("/api/v1/jobs/{$this->job->id}/analyze-company");

        $response->assertNoContent();

        Queue::assertPushed(AnalyzeCompanyJob::class, fn (AnalyzeCompanyJob $queuedJob): bool => true);
    }

    public function test_analyze_company_requires_premium(): void
    {
        $freeUser = User::factory()->create(['is_premium' => false]);
        $status = JobStatus::factory()->for($freeUser)->create();
        $category = JobCategory::factory()->for($freeUser)->create();
        $job = Job::factory()->for($freeUser)->for($status, 'status')->for($category, 'category')->create();

        Sanctum::actingAs($freeUser);

        $response = $this->postJson("/api/v1/jobs/{$job->id}/analyze-company");

        $response->assertForbidden();
    }

    public function test_find_contacts_dispatches_job_and_returns_204(): void
    {
        Queue::fake();
        Sanctum::actingAs($this->user);

        $response = $this->postJson("/api/v1/jobs/{$this->job->id}/find-contacts");

        $response->assertNoContent();

        Queue::assertPushed(FindContactsJob::class, fn (FindContactsJob $queuedJob): bool => true);
    }

    public function test_find_contacts_requires_premium(): void
    {
        $freeUser = User::factory()->create(['is_premium' => false]);
        $status = JobStatus::factory()->for($freeUser)->create();
        $category = JobCategory::factory()->for($freeUser)->create();
        $job = Job::factory()->for($freeUser)->for($status, 'status')->for($category, 'category')->create();

        Sanctum::actingAs($freeUser);

        $response = $this->postJson("/api/v1/jobs/{$job->id}/find-contacts");

        $response->assertForbidden();
    }

    public function test_analyze_company_forbidden_for_other_user(): void
    {
        $otherUser = User::factory()->premium()->create();
        Sanctum::actingAs($otherUser);

        $response = $this->postJson("/api/v1/jobs/{$this->job->id}/analyze-company");

        $response->assertForbidden();
    }
}
