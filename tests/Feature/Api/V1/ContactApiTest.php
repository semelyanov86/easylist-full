<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\Contact;
use App\Models\Job;
use App\Models\JobCategory;
use App\Models\JobStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ContactApiTest extends TestCase
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

    public function test_index_returns_contacts(): void
    {
        Sanctum::actingAs($this->user);

        Contact::factory()->count(2)->for($this->job)->for($this->user)->create();

        $response = $this->getJson("/api/v1/jobs/{$this->job->id}/contacts");

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.type', 'contacts');
    }

    public function test_store_creates_contact(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson("/api/v1/jobs/{$this->job->id}/contacts", [
            'first_name' => 'Иван',
            'last_name' => 'Петров',
            'position' => 'HR Manager',
            'email' => 'ivan@example.com',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.type', 'contacts')
            ->assertJsonPath('data.attributes.first_name', 'Иван')
            ->assertJsonPath('data.attributes.last_name', 'Петров');

        $this->assertDatabaseHas('contacts', [
            'job_id' => $this->job->id,
            'first_name' => 'Иван',
        ]);
    }

    public function test_destroy_deletes_contact(): void
    {
        Sanctum::actingAs($this->user);

        $contact = Contact::factory()->for($this->job)->for($this->user)->create();

        $response = $this->deleteJson("/api/v1/jobs/{$this->job->id}/contacts/{$contact->id}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('contacts', ['id' => $contact->id]);
    }

    public function test_cannot_delete_contact_from_other_job(): void
    {
        Sanctum::actingAs($this->user);

        $otherJob = Job::factory()->for($this->user)->for(
            JobStatus::factory()->for($this->user),
            'status',
        )->for(JobCategory::factory()->for($this->user), 'category')->create();

        $contact = Contact::factory()->for($otherJob)->for($this->user)->create();

        $response = $this->deleteJson("/api/v1/jobs/{$this->job->id}/contacts/{$contact->id}");

        $response->assertNotFound();
    }
}
