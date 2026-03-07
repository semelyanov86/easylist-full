<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\Job;
use App\Models\JobCategory;
use App\Models\JobStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class JobCategoryApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private JobStatus $status;

    private JobCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->status = JobStatus::factory()->for($this->user)->create();
        $this->category = JobCategory::factory()->for($this->user)->create();
    }

    public function test_index_returns_all_categories(): void
    {
        Sanctum::actingAs($this->user);

        JobCategory::factory()->for($this->user)->create();

        $response = $this->getJson('/api/v1/job-categories');

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/vnd.api+json')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'type',
                        'id',
                        'attributes' => ['title', 'currency', 'currency_symbol'],
                    ],
                ],
            ]);

        $response->assertJsonPath('data.0.type', 'job-categories');
    }

    public function test_show_returns_single_category(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson("/api/v1/job-categories/{$this->category->id}");

        $response->assertOk()
            ->assertJsonPath('data.type', 'job-categories')
            ->assertJsonPath('data.id', (string) $this->category->id)
            ->assertJsonPath('data.attributes.title', $this->category->title);
    }

    public function test_show_forbidden_for_other_user(): void
    {
        Sanctum::actingAs($this->user);

        $otherUser = User::factory()->create();
        $otherCategory = JobCategory::factory()->for($otherUser)->create();

        $response = $this->getJson("/api/v1/job-categories/{$otherCategory->id}");

        $response->assertForbidden();
    }

    public function test_jobs_returns_paginated_jobs_from_category(): void
    {
        Sanctum::actingAs($this->user);

        Job::factory()->count(3)->for($this->user)->for($this->status, 'status')->for($this->category, 'category')->create();

        $otherCategory = JobCategory::factory()->for($this->user)->create();
        Job::factory()->for($this->user)->for($this->status, 'status')->for($otherCategory, 'category')->create();

        $response = $this->getJson("/api/v1/job-categories/{$this->category->id}/jobs");

        $response->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('meta.total', 3);
    }

    public function test_jobs_supports_status_filter(): void
    {
        Sanctum::actingAs($this->user);

        $status2 = JobStatus::factory()->for($this->user)->create();

        Job::factory()->for($this->user)->for($this->status, 'status')->for($this->category, 'category')->create();
        Job::factory()->for($this->user)->for($status2, 'status')->for($this->category, 'category')->create();

        $response = $this->getJson("/api/v1/job-categories/{$this->category->id}/jobs?filter[status_id]={$this->status->id}");

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_jobs_supports_search(): void
    {
        Sanctum::actingAs($this->user);

        Job::factory()->for($this->user)->for($this->status, 'status')->for($this->category, 'category')->create(['company_name' => 'Яндекс']);
        Job::factory()->for($this->user)->for($this->status, 'status')->for($this->category, 'category')->create(['company_name' => 'Google']);

        $response = $this->getJson("/api/v1/job-categories/{$this->category->id}/jobs?filter[search]=Яндекс");

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }
}
