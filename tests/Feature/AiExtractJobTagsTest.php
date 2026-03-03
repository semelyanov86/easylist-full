<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Contracts\AiTagExtractorContract;
use App\Models\Job;
use App\Models\Skill;
use App\Models\User;
use App\Services\FakeAiTagExtractorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiExtractJobTagsTest extends TestCase
{
    use RefreshDatabase;

    private FakeAiTagExtractorService $fakeExtractor;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->fakeExtractor = new FakeAiTagExtractorService();
        $this->app->instance(AiTagExtractorContract::class, $this->fakeExtractor);
    }

    public function test_unauthenticated_user_gets_401(): void
    {
        $user = User::factory()->create();
        $job = Job::factory()->for($user)->create();

        $response = $this->postJson(route('ai.extract-job-tags', $job));

        $response->assertUnauthorized();
    }

    public function test_another_users_job_returns_403(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $job = Job::factory()->for($otherUser)->create();

        $response = $this->actingAs($user)->postJson(route('ai.extract-job-tags', $job));

        $response->assertForbidden();
    }

    public function test_successful_extraction_creates_and_attaches_skills(): void
    {
        $user = User::factory()->create();
        $job = Job::factory()->for($user)->create();

        $this->fakeExtractor->withResponse(['PHP', 'Laravel', 'Vue.js']);

        $response = $this->actingAs($user)->postJson(route('ai.extract-job-tags', $job));

        $response->assertOk();
        $response->assertJsonStructure(['skills']);

        $this->assertDatabaseHas('skills', ['user_id' => $user->id, 'title' => 'PHP']);
        $this->assertDatabaseHas('skills', ['user_id' => $user->id, 'title' => 'Laravel']);
        $this->assertDatabaseHas('skills', ['user_id' => $user->id, 'title' => 'Vue.js']);

        $this->assertSame(3, $job->skills()->count());
    }

    public function test_existing_skills_are_not_duplicated(): void
    {
        $user = User::factory()->create();
        $job = Job::factory()->for($user)->create();

        Skill::factory()->for($user)->create(['title' => 'PHP']);

        $this->fakeExtractor->withResponse(['PHP', 'Laravel']);

        $response = $this->actingAs($user)->postJson(route('ai.extract-job-tags', $job));

        $response->assertOk();

        $this->assertSame(1, $user->skills()->where('title', 'PHP')->count());
        $this->assertSame(2, $user->skills()->count());
        $this->assertSame(2, $job->skills()->count());
    }

    public function test_previously_attached_skills_are_preserved(): void
    {
        $user = User::factory()->create();
        $job = Job::factory()->for($user)->create();

        $existingSkill = Skill::factory()->for($user)->create(['title' => 'Docker']);
        $job->skills()->attach($existingSkill);

        $this->fakeExtractor->withResponse(['PHP']);

        $response = $this->actingAs($user)->postJson(route('ai.extract-job-tags', $job));

        $response->assertOk();

        $this->assertSame(2, $job->skills()->count());
        $this->assertTrue($job->skills()->where('title', 'Docker')->exists());
        $this->assertTrue($job->skills()->where('title', 'PHP')->exists());
    }

    public function test_service_failure_returns_502(): void
    {
        $user = User::factory()->create();
        $job = Job::factory()->for($user)->create();

        $this->fakeExtractor->shouldFail();

        $response = $this->actingAs($user)->postJson(route('ai.extract-job-tags', $job));

        $response->assertStatus(502);
        $response->assertJsonStructure(['message']);
    }

    public function test_empty_tags_returns_empty_skills(): void
    {
        $user = User::factory()->create();
        $job = Job::factory()->for($user)->create();

        $this->fakeExtractor->withResponse([]);

        $response = $this->actingAs($user)->postJson(route('ai.extract-job-tags', $job));

        $response->assertOk();
        $response->assertJson(['skills' => []]);
        $this->assertSame(0, $job->skills()->count());
    }
}
