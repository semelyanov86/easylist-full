<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Job;
use App\Models\JobCategory;
use App\Models\JobComment;
use App\Models\JobStatus;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class JobShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_own_job(): void
    {
        $user = User::factory()->create();
        $job = $this->createJobForUser($user);

        $response = $this->actingAs($user)->get(route('jobs.show', $job));

        $response->assertOk();
        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('jobs/Show')
                ->where('job.id', $job->id)
                ->where('job.title', $job->title)
        );
    }

    public function test_user_cannot_view_another_users_job(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $job = $this->createJobForUser($owner);

        $response = $this->actingAs($otherUser)->get(route('jobs.show', $job));

        $response->assertForbidden();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $user = User::factory()->create();
        $job = $this->createJobForUser($user);

        $response = $this->get(route('jobs.show', $job));

        $response->assertRedirect(route('login'));
    }

    public function test_soft_deleted_job_returns_404(): void
    {
        $user = User::factory()->create();
        $job = $this->createJobForUser($user);
        $job->delete();

        $response = $this->actingAs($user)->get(route('jobs.show', $job));

        $response->assertNotFound();
    }

    public function test_job_comments_included_in_response(): void
    {
        $user = User::factory()->create();
        $job = $this->createJobForUser($user);

        JobComment::factory()->create([
            'job_id' => $job->id,
            'user_id' => $user->id,
            'body' => 'Тестовый комментарий',
        ]);

        $response = $this->actingAs($user)->get(route('jobs.show', $job));

        $response->assertOk();
        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('jobs/Show')
                ->has('job.comments', 1)
                ->where('job.comments.0.body', 'Тестовый комментарий')
        );
    }

    public function test_job_skills_included_in_response(): void
    {
        $user = User::factory()->create();
        $job = $this->createJobForUser($user);
        $skill = Skill::factory()->for($user)->create(['title' => 'PHP']);
        $job->skills()->attach($skill);

        $response = $this->actingAs($user)->get(route('jobs.show', $job));

        $response->assertOk();
        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('jobs/Show')
                ->has('job.skills', 1)
                ->where('job.skills.0.title', 'PHP')
        );
    }

    public function test_status_tabs_returned(): void
    {
        $user = User::factory()->create();
        $job = $this->createJobForUser($user);

        $response = $this->actingAs($user)->get(route('jobs.show', $job));

        $response->assertOk();
        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('jobs/Show')
                ->has('statusTabs')
        );
    }

    public function test_categories_and_skills_returned(): void
    {
        $user = User::factory()->create();
        $job = $this->createJobForUser($user);
        Skill::factory()->for($user)->create(['title' => 'Laravel']);

        $response = $this->actingAs($user)->get(route('jobs.show', $job));

        $response->assertOk();
        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('jobs/Show')
                ->has('categories')
                ->has('skills')
        );
    }

    public function test_job_activities_included_in_response(): void
    {
        $user = User::factory()->create();
        $job = $this->createJobForUser($user);

        $response = $this->actingAs($user)->get(route('jobs.show', $job));

        $response->assertOk();
        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('jobs/Show')
                ->has('job.activities')
        );
    }

    private function createJobForUser(User $user): Job
    {
        $status = JobStatus::factory()->for($user)->create();
        $category = JobCategory::factory()->for($user)->create();

        return Job::factory()->for($user)->create([
            'job_status_id' => $status->id,
            'job_category_id' => $category->id,
        ]);
    }
}
