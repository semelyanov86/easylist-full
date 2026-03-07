<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\Job;
use App\Models\JobCategory;
use App\Models\JobDocument;
use App\Models\JobStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class JobDocumentApiTest extends TestCase
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

    public function test_index_returns_documents(): void
    {
        Sanctum::actingAs($this->user);

        JobDocument::factory()->count(2)->for($this->job)->for($this->user)->create();

        $response = $this->getJson("/api/v1/jobs/{$this->job->id}/documents");

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.type', 'documents');
    }

    public function test_store_creates_document_with_external_url(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson("/api/v1/jobs/{$this->job->id}/documents", [
            'title' => 'Резюме',
            'category' => 'resume',
            'external_url' => 'https://example.com/resume.pdf',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.type', 'documents')
            ->assertJsonPath('data.attributes.title', 'Резюме')
            ->assertJsonPath('data.attributes.external_url', 'https://example.com/resume.pdf');
    }

    public function test_forbidden_for_other_users_job(): void
    {
        $otherUser = User::factory()->create();
        Sanctum::actingAs($otherUser);

        $response = $this->getJson("/api/v1/jobs/{$this->job->id}/documents");

        $response->assertForbidden();
    }
}
