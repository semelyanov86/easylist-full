<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\CompanyInfo;
use App\Models\Contact;
use App\Models\Job;
use App\Models\JobCategory;
use App\Models\JobStatus;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class JobPublicViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_view_accessible_without_auth(): void
    {
        $job = $this->createSharedJob();

        $response = $this->get(route('jobs.public-view', ['uuid' => $job->uuid]));

        $response->assertOk();
        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('jobs/PublicView')
                ->where('job.title', $job->title)
                ->where('job.company_name', $job->company_name)
        );
    }

    public function test_public_view_accessible_when_authenticated(): void
    {
        $user = User::factory()->create();
        $job = $this->createSharedJob();

        $response = $this->actingAs($user)->get(route('jobs.public-view', ['uuid' => $job->uuid]));

        $response->assertOk();
    }

    public function test_public_view_returns_404_for_invalid_uuid(): void
    {
        $response = $this->get(route('jobs.public-view', ['uuid' => 'non-existent-uuid']));

        $response->assertNotFound();
    }

    public function test_public_view_returns_404_for_soft_deleted_job(): void
    {
        $job = $this->createSharedJob();
        $job->delete();

        $response = $this->get(route('jobs.public-view', ['uuid' => $job->uuid]));

        $response->assertNotFound();
    }

    public function test_public_view_does_not_expose_private_fields(): void
    {
        $job = $this->createSharedJob();

        $response = $this->get(route('jobs.public-view', ['uuid' => $job->uuid]));

        $response->assertOk();
        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('jobs/PublicView')
                ->missing('job.id')
                ->missing('job.is_favorite')
                ->missing('job.job_status_id')
                ->missing('job.job_category_id')
                ->missing('job.comments')
                ->missing('job.documents')
                ->missing('job.activities')
                ->missing('job.tasks')
        );
    }

    public function test_public_view_includes_skills(): void
    {
        $user = User::factory()->create();
        $job = $this->createSharedJobForUser($user);
        $skill = Skill::factory()->for($user)->create(['title' => 'Laravel']);
        $job->skills()->attach($skill);

        $response = $this->get(route('jobs.public-view', ['uuid' => $job->uuid]));

        $response->assertOk();
        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('jobs/PublicView')
                ->has('job.skills', 1)
                ->where('job.skills.0.title', 'Laravel')
        );
    }

    public function test_public_view_includes_contacts(): void
    {
        $job = $this->createSharedJob();
        Contact::factory()->create([
            'job_id' => $job->id,
            'user_id' => $job->user_id,
            'first_name' => 'Иван',
            'last_name' => 'Петров',
            'email' => 'ivan@example.com',
        ]);

        $response = $this->get(route('jobs.public-view', ['uuid' => $job->uuid]));

        $response->assertOk();
        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('jobs/PublicView')
                ->has('job.contacts', 1)
                ->where('job.contacts.0.first_name', 'Иван')
                ->where('job.contacts.0.last_name', 'Петров')
                ->where('job.contacts.0.email', 'ivan@example.com')
                ->missing('job.contacts.0.id')
                ->missing('job.contacts.0.user_id')
                ->missing('job.contacts.0.description')
        );
    }

    public function test_public_view_includes_company_info(): void
    {
        $job = $this->createSharedJob([
            'company_name' => 'ООО Тест',
            'location_city' => 'Москва',
        ]);

        CompanyInfo::factory()->create([
            'name' => 'ООО Тест',
            'city' => 'Москва',
            'info' => [
                'overview' => 'Описание компании',
                'industry' => 'IT',
                'employees' => '100-500',
            ],
        ]);

        $response = $this->get(route('jobs.public-view', ['uuid' => $job->uuid]));

        $response->assertOk();
        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('jobs/PublicView')
                ->where('job.company_info.overview', 'Описание компании')
                ->where('job.company_info.industry', 'IT')
                ->where('job.company_info.employees', '100-500')
        );
    }

    public function test_public_view_company_info_null_when_not_found(): void
    {
        $job = $this->createSharedJob([
            'company_name' => 'Неизвестная',
            'location_city' => 'Казань',
        ]);

        $response = $this->get(route('jobs.public-view', ['uuid' => $job->uuid]));

        $response->assertOk();
        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('jobs/PublicView')
                ->where('job.company_info', null)
        );
    }

    public function test_public_view_includes_salary_and_currency(): void
    {
        $job = $this->createSharedJob([
            'salary' => 150000,
        ]);

        $response = $this->get(route('jobs.public-view', ['uuid' => $job->uuid]));

        $response->assertOk();
        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('jobs/PublicView')
                ->where('job.salary', 150000)
                ->has('job.currency_symbol')
        );
    }

    public function test_public_view_returns_404_for_job_without_uuid(): void
    {
        $job = $this->createJob();

        $response = $this->get('/job/view/' . $job->id);

        $response->assertNotFound();
    }

    /** @param  array<string, mixed>  $attributes */
    private function createSharedJob(array $attributes = []): Job
    {
        $user = User::factory()->create();

        return $this->createSharedJobForUser($user, $attributes);
    }

    /** @param  array<string, mixed>  $attributes */
    private function createSharedJobForUser(User $user, array $attributes = []): Job
    {
        $status = JobStatus::factory()->for($user)->create();
        $category = JobCategory::factory()->for($user)->create();

        return Job::factory()->for($user)->shared()->create(array_merge([
            'job_status_id' => $status->id,
            'job_category_id' => $category->id,
        ], $attributes));
    }

    /** @param  array<string, mixed>  $attributes */
    private function createJob(array $attributes = []): Job
    {
        $user = User::factory()->create();
        $status = JobStatus::factory()->for($user)->create();
        $category = JobCategory::factory()->for($user)->create();

        return Job::factory()->for($user)->create(array_merge([
            'job_status_id' => $status->id,
            'job_category_id' => $category->id,
        ], $attributes));
    }
}
