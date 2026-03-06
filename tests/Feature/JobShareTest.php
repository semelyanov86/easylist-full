<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Job;
use App\Models\JobCategory;
use App\Models\JobStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobShareTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_share_job_and_uuid_is_generated(): void
    {
        $user = User::factory()->create();
        $job = $this->createJobForUser($user);

        $this->assertNull($job->uuid);

        $response = $this->actingAs($user)->postJson(route('jobs.share', $job));

        $response->assertOk();
        $response->assertJsonStructure(['uuid']);

        $job->refresh();
        $this->assertNotNull($job->uuid);
        $this->assertSame($job->uuid, $response->json('uuid'));
    }

    public function test_share_returns_existing_uuid_if_already_shared(): void
    {
        $user = User::factory()->create();
        $job = $this->createJobForUser($user, ['uuid' => 'existing-uuid-value']);

        $response = $this->actingAs($user)->postJson(route('jobs.share', $job));

        $response->assertOk();
        $response->assertJson(['uuid' => 'existing-uuid-value']);

        $job->refresh();
        $this->assertSame('existing-uuid-value', $job->uuid);
    }

    public function test_share_generates_uuid_v7(): void
    {
        $user = User::factory()->create();
        $job = $this->createJobForUser($user);

        $response = $this->actingAs($user)->postJson(route('jobs.share', $job));

        /** @var string $uuid */
        $uuid = $response->json('uuid');
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $uuid,
        );
    }

    public function test_other_user_cannot_share_job(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $job = $this->createJobForUser($owner);

        $response = $this->actingAs($otherUser)->postJson(route('jobs.share', $job));

        $response->assertForbidden();
    }

    public function test_guest_cannot_share_job(): void
    {
        $user = User::factory()->create();
        $job = $this->createJobForUser($user);

        $response = $this->postJson(route('jobs.share', $job));

        $response->assertUnauthorized();
    }

    public function test_idempotent_share_does_not_change_uuid(): void
    {
        $user = User::factory()->create();
        $job = $this->createJobForUser($user);

        $first = $this->actingAs($user)->postJson(route('jobs.share', $job));
        $firstUuid = $first->json('uuid');

        $second = $this->actingAs($user)->postJson(route('jobs.share', $job));
        $secondUuid = $second->json('uuid');

        $this->assertSame($firstUuid, $secondUuid);
    }

    /** @param  array<string, mixed>  $attributes */
    private function createJobForUser(User $user, array $attributes = []): Job
    {
        $status = JobStatus::factory()->for($user)->create();
        $category = JobCategory::factory()->for($user)->create();

        return Job::factory()->for($user)->create(array_merge([
            'job_status_id' => $status->id,
            'job_category_id' => $category->id,
        ], $attributes));
    }
}
