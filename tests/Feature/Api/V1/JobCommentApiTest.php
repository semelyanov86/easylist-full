<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\Job;
use App\Models\JobCategory;
use App\Models\JobComment;
use App\Models\JobStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class JobCommentApiTest extends TestCase
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

    public function test_index_returns_comments(): void
    {
        Sanctum::actingAs($this->user);

        JobComment::factory()->count(3)->for($this->job)->for($this->user)->create();

        $response = $this->getJson("/api/v1/jobs/{$this->job->id}/comments");

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/vnd.api+json')
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('data.0.type', 'comments');
    }

    public function test_store_creates_comment(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson("/api/v1/jobs/{$this->job->id}/comments", [
            'body' => 'Тестовый комментарий',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.type', 'comments')
            ->assertJsonPath('data.attributes.body', 'Тестовый комментарий')
            ->assertJsonPath('data.attributes.author_name', $this->user->name);

        $this->assertDatabaseHas('job_comments', [
            'job_id' => $this->job->id,
            'body' => 'Тестовый комментарий',
        ]);
    }

    public function test_store_validation_error(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson("/api/v1/jobs/{$this->job->id}/comments", []);

        $response->assertStatus(422)
            ->assertJsonStructure(['errors']);
    }

    public function test_forbidden_for_other_users_job(): void
    {
        $otherUser = User::factory()->create();
        Sanctum::actingAs($otherUser);

        $response = $this->getJson("/api/v1/jobs/{$this->job->id}/comments");

        $response->assertForbidden();
    }
}
