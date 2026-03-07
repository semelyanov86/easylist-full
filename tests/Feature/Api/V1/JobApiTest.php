<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\Contact;
use App\Models\Job;
use App\Models\JobCategory;
use App\Models\JobComment;
use App\Models\JobStatus;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class JobApiTest extends TestCase
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

    public function test_unauthenticated_request_returns_401(): void
    {
        $response = $this->getJson('/api/v1/jobs');

        $response->assertUnauthorized();
    }

    public function test_index_returns_json_api_format(): void
    {
        Sanctum::actingAs($this->user);

        Job::factory()->for($this->user)->for($this->status, 'status')->for($this->category, 'category')->create();

        $response = $this->getJson('/api/v1/jobs');

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/vnd.api+json')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'type',
                        'id',
                        'attributes' => ['title', 'company_name', 'is_favorite', 'created_at'],
                        'relationships' => ['status', 'category'],
                    ],
                ],
                'meta' => ['current_page', 'total', 'per_page', 'last_page'],
                'links' => ['first', 'last', 'prev', 'next'],
            ]);

        $response->assertJsonPath('data.0.type', 'jobs');
    }

    public function test_index_supports_pagination(): void
    {
        Sanctum::actingAs($this->user);

        Job::factory()->count(5)->for($this->user)->for($this->status, 'status')->for($this->category, 'category')->create();

        $response = $this->getJson('/api/v1/jobs?page[size]=2&page[number]=1');

        $response->assertOk()
            ->assertJsonPath('meta.per_page', 2)
            ->assertJsonPath('meta.total', 5)
            ->assertJsonCount(2, 'data');
    }

    public function test_index_supports_filter_by_status(): void
    {
        Sanctum::actingAs($this->user);

        $status2 = JobStatus::factory()->for($this->user)->create();

        Job::factory()->for($this->user)->for($this->status, 'status')->for($this->category, 'category')->create(['title' => 'Вакансия 1']);
        Job::factory()->for($this->user)->for($status2, 'status')->for($this->category, 'category')->create(['title' => 'Вакансия 2']);

        $response = $this->getJson("/api/v1/jobs?filter[status_id]={$this->status->id}");

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.attributes.title', 'Вакансия 1');
    }

    public function test_index_supports_search(): void
    {
        Sanctum::actingAs($this->user);

        Job::factory()->for($this->user)->for($this->status, 'status')->for($this->category, 'category')->create(['company_name' => 'Яндекс']);
        Job::factory()->for($this->user)->for($this->status, 'status')->for($this->category, 'category')->create(['company_name' => 'Google']);

        $response = $this->getJson('/api/v1/jobs?filter[search]=Яндекс');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.attributes.company_name', 'Яндекс');
    }

    public function test_index_supports_favorites_filter(): void
    {
        Sanctum::actingAs($this->user);

        Job::factory()->for($this->user)->for($this->status, 'status')->for($this->category, 'category')->create(['is_favorite' => true]);
        Job::factory()->for($this->user)->for($this->status, 'status')->for($this->category, 'category')->create(['is_favorite' => false]);

        $response = $this->getJson('/api/v1/jobs?filter[is_favorite]=1');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.attributes.is_favorite', true);
    }

    public function test_show_returns_json_api_format(): void
    {
        Sanctum::actingAs($this->user);

        $job = Job::factory()->for($this->user)->for($this->status, 'status')->for($this->category, 'category')->create();

        $response = $this->getJson("/api/v1/jobs/{$job->id}");

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/vnd.api+json')
            ->assertJsonStructure([
                'data' => [
                    'type',
                    'id',
                    'attributes' => ['title', 'company_name', 'uuid', 'is_favorite'],
                    'relationships' => ['status', 'category'],
                ],
                'included',
            ]);

        $response->assertJsonPath('data.type', 'jobs');
        $response->assertJsonPath('data.id', (string) $job->id);
    }

    public function test_show_with_includes(): void
    {
        Sanctum::actingAs($this->user);

        $job = Job::factory()->for($this->user)->for($this->status, 'status')->for($this->category, 'category')->create();
        $contact = Contact::factory()->for($job)->for($this->user)->create();
        $comment = JobComment::factory()->for($job)->for($this->user)->create();

        $response = $this->getJson("/api/v1/jobs/{$job->id}?include=contacts,comments");

        $response->assertOk()
            ->assertJsonPath('data.relationships.contacts.data.0.type', 'contacts')
            ->assertJsonPath('data.relationships.contacts.data.0.id', (string) $contact->id)
            ->assertJsonPath('data.relationships.comments.data.0.type', 'comments')
            ->assertJsonPath('data.relationships.comments.data.0.id', (string) $comment->id);

        /** @var list<array<string, mixed>> $included */
        $included = $response->json('included');
        $includedTypes = collect($included)->pluck('type')->unique()->sort()->values()->all();
        $this->assertContains('contacts', $includedTypes);
        $this->assertContains('comments', $includedTypes);
    }

    public function test_show_rejects_invalid_include(): void
    {
        Sanctum::actingAs($this->user);

        $job = Job::factory()->for($this->user)->for($this->status, 'status')->for($this->category, 'category')->create();

        $response = $this->getJson("/api/v1/jobs/{$job->id}?include=invalid");

        $response->assertStatus(400);
    }

    public function test_show_forbidden_for_other_user(): void
    {
        Sanctum::actingAs($this->user);

        $otherUser = User::factory()->create();
        $otherStatus = JobStatus::factory()->for($otherUser)->create();
        $otherCategory = JobCategory::factory()->for($otherUser)->create();
        $job = Job::factory()->for($otherUser)->for($otherStatus, 'status')->for($otherCategory, 'category')->create();

        $response = $this->getJson("/api/v1/jobs/{$job->id}");

        $response->assertForbidden();
    }

    public function test_store_creates_job(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/jobs', [
            'title' => 'PHP Developer',
            'company_name' => 'Яндекс',
            'job_status_id' => $this->status->id,
            'job_category_id' => $this->category->id,
        ]);

        $response->assertCreated()
            ->assertHeader('Content-Type', 'application/vnd.api+json')
            ->assertJsonPath('data.type', 'jobs')
            ->assertJsonPath('data.attributes.title', 'PHP Developer')
            ->assertJsonPath('data.attributes.company_name', 'Яндекс');

        $this->assertDatabaseHas('job_listings', [
            'user_id' => $this->user->id,
            'title' => 'PHP Developer',
        ]);
    }

    public function test_store_validation_returns_json_api_errors(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/jobs', []);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'errors' => [
                    '*' => ['status', 'title', 'detail', 'source'],
                ],
            ]);
    }

    public function test_update_modifies_job(): void
    {
        Sanctum::actingAs($this->user);

        $job = Job::factory()->for($this->user)->for($this->status, 'status')->for($this->category, 'category')->create();

        $response = $this->patchJson("/api/v1/jobs/{$job->id}", [
            'title' => 'Senior PHP Developer',
            'company_name' => 'Яндекс',
            'job_status_id' => $this->status->id,
            'job_category_id' => $this->category->id,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.attributes.title', 'Senior PHP Developer');
    }

    public function test_destroy_deletes_job(): void
    {
        Sanctum::actingAs($this->user);

        $job = Job::factory()->for($this->user)->for($this->status, 'status')->for($this->category, 'category')->create();

        $response = $this->deleteJson("/api/v1/jobs/{$job->id}");

        $response->assertNoContent();
        $this->assertSoftDeleted('job_listings', ['id' => $job->id]);
    }

    public function test_move_status_changes_job_status(): void
    {
        Sanctum::actingAs($this->user);

        $newStatus = JobStatus::factory()->for($this->user)->create();
        $job = Job::factory()->for($this->user)->for($this->status, 'status')->for($this->category, 'category')->create();

        $response = $this->patchJson("/api/v1/jobs/{$job->id}/status", [
            'status_id' => $newStatus->id,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.relationships.status.data.id', (string) $newStatus->id);
    }

    public function test_toggle_favorite(): void
    {
        Sanctum::actingAs($this->user);

        $job = Job::factory()->for($this->user)->for($this->status, 'status')->for($this->category, 'category')->create(['is_favorite' => false]);

        $response = $this->patchJson("/api/v1/jobs/{$job->id}/favorite");

        $response->assertOk()
            ->assertJsonPath('data.attributes.is_favorite', true);
    }

    public function test_share_generates_uuid(): void
    {
        Sanctum::actingAs($this->user);

        $job = Job::factory()->for($this->user)->for($this->status, 'status')->for($this->category, 'category')->create(['uuid' => null]);

        $response = $this->postJson("/api/v1/jobs/{$job->id}/share");

        $response->assertOk()
            ->assertJsonPath('data.type', 'jobs')
            ->assertJsonStructure(['data' => ['attributes' => ['uuid']]]);

        $this->assertNotNull($response->json('data.attributes.uuid'));
    }

    public function test_store_with_skills(): void
    {
        Sanctum::actingAs($this->user);

        $skill = Skill::factory()->for($this->user)->create(['title' => 'PHP']);

        $response = $this->postJson('/api/v1/jobs', [
            'title' => 'Developer',
            'company_name' => 'Test',
            'job_status_id' => $this->status->id,
            'job_category_id' => $this->category->id,
            'skill_ids' => [$skill->id],
        ]);

        $response->assertCreated();

        /** @var list<array<string, mixed>> $skillsData */
        $skillsData = $response->json('data.relationships.skills.data');
        $skillIds = collect($skillsData)->pluck('id')->all();
        $this->assertContains((string) $skill->id, $skillIds);
    }
}
