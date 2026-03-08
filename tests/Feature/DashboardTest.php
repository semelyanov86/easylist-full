<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Job;
use App\Models\JobCategory;
use App\Models\JobStatus;
use App\Models\Skill;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_visit_the_dashboard(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));
        $response->assertOk();
    }

    public function test_dashboard_has_deferred_recent_activities(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));

        $response->assertInertia(
            fn (Assert $page) => $page
                ->component('Dashboard')
                ->missing('recentActivities')
                ->loadDeferredProps(
                    fn (Assert $reload) => $reload
                        ->has('recentActivities')
                )
        );
    }

    public function test_dashboard_has_deferred_pending_tasks(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));

        $response->assertInertia(
            fn (Assert $page) => $page
                ->component('Dashboard')
                ->missing('pendingTasks')
                ->loadDeferredProps(
                    fn (Assert $reload) => $reload
                        ->has('pendingTasks')
                )
        );
    }

    public function test_dashboard_has_deferred_favorite_jobs(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));

        $response->assertInertia(
            fn (Assert $page) => $page
                ->component('Dashboard')
                ->missing('favoriteJobs')
                ->loadDeferredProps(
                    fn (Assert $reload) => $reload
                        ->has('favoriteJobs')
                )
        );
    }

    public function test_dashboard_has_deferred_recent_jobs(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));

        $response->assertInertia(
            fn (Assert $page) => $page
                ->component('Dashboard')
                ->missing('recentJobs')
                ->loadDeferredProps(
                    fn (Assert $reload) => $reload
                        ->has('recentJobs')
                )
        );
    }

    public function test_dashboard_has_job_funnel_data(): void
    {
        $user = User::factory()->create();
        $status = JobStatus::factory()->create(['user_id' => $user->id]);
        $category = JobCategory::factory()->create(['user_id' => $user->id]);

        Job::factory()->count(2)->create([
            'user_id' => $user->id,
            'job_status_id' => $status->id,
            'job_category_id' => $category->id,
        ]);

        $this->actingAs($user);

        $response = $this->get(route('dashboard'));

        $response->assertInertia(
            fn (Assert $page) => $page
                ->component('Dashboard')
                ->has('jobFunnel', 1)
                ->has(
                    'jobFunnel.0',
                    fn (Assert $item) => $item
                        ->where('id', $status->id)
                        ->where('count', 2)
                        ->etc()
                )
                ->where('funnelCategoryId', null)
        );
    }

    public function test_dashboard_has_deferred_skills_demand(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));

        $response->assertInertia(
            fn (Assert $page) => $page
                ->component('Dashboard')
                ->missing('skillsDemand')
                ->loadDeferredProps(
                    fn (Assert $reload) => $reload
                        ->has('skillsDemand')
                )
        );
    }

    public function test_dashboard_has_deferred_response_dynamics(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));

        $response->assertInertia(
            fn (Assert $page) => $page
                ->component('Dashboard')
                ->missing('responseDynamics')
                ->loadDeferredProps(
                    fn (Assert $reload) => $reload
                        ->has('responseDynamics')
                )
        );
    }

    public function test_dashboard_skills_demand_returns_top_skills(): void
    {
        $user = User::factory()->create();
        $status = JobStatus::factory()->create(['user_id' => $user->id]);
        $category = JobCategory::factory()->create(['user_id' => $user->id]);

        $skillA = Skill::factory()->create(['user_id' => $user->id, 'title' => 'PHP']);
        $skillB = Skill::factory()->create(['user_id' => $user->id, 'title' => 'Vue']);
        $skillC = Skill::factory()->create(['user_id' => $user->id, 'title' => 'Laravel']);

        // PHP — 3 вакансии, Vue — 1, Laravel — 2
        $jobsA = Job::factory()->count(3)->create([
            'user_id' => $user->id,
            'job_status_id' => $status->id,
            'job_category_id' => $category->id,
        ]);
        $jobsA->each(fn (Job $job) => $job->skills()->attach($skillA));

        $jobB = Job::factory()->create([
            'user_id' => $user->id,
            'job_status_id' => $status->id,
            'job_category_id' => $category->id,
        ]);
        $jobB->skills()->attach($skillB);

        $jobsC = Job::factory()->count(2)->create([
            'user_id' => $user->id,
            'job_status_id' => $status->id,
            'job_category_id' => $category->id,
        ]);
        $jobsC->each(fn (Job $job) => $job->skills()->attach($skillC));

        $this->actingAs($user);

        $response = $this->get(route('dashboard'));

        $response->assertInertia(
            fn (Assert $page) => $page
                ->component('Dashboard')
                ->loadDeferredProps(
                    fn (Assert $reload) => $reload
                        ->has('skillsDemand', 3)
                        ->where('skillsDemand.0.title', 'PHP')
                        ->where('skillsDemand.0.jobs_count', 3)
                        ->where('skillsDemand.1.title', 'Laravel')
                        ->where('skillsDemand.1.jobs_count', 2)
                        ->where('skillsDemand.2.title', 'Vue')
                        ->where('skillsDemand.2.jobs_count', 1)
                )
        );
    }

    public function test_dashboard_skills_demand_scoped_to_user(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $status = JobStatus::factory()->create(['user_id' => $user->id]);
        $category = JobCategory::factory()->create(['user_id' => $user->id]);
        $otherStatus = JobStatus::factory()->create(['user_id' => $other->id]);
        $otherCategory = JobCategory::factory()->create(['user_id' => $other->id]);

        // Скилл другого пользователя с вакансиями
        $otherSkill = Skill::factory()->create(['user_id' => $other->id, 'title' => 'Go']);
        $otherJob = Job::factory()->create([
            'user_id' => $other->id,
            'job_status_id' => $otherStatus->id,
            'job_category_id' => $otherCategory->id,
        ]);
        $otherJob->skills()->attach($otherSkill);

        $this->actingAs($user);

        $response = $this->get(route('dashboard'));

        $response->assertInertia(
            fn (Assert $page) => $page
                ->component('Dashboard')
                ->loadDeferredProps(
                    fn (Assert $reload) => $reload
                        ->has('skillsDemand', 0)
                )
        );
    }

    public function test_dashboard_response_dynamics_returns_weekly_points(): void
    {
        $user = User::factory()->create();
        $status = JobStatus::factory()->create(['user_id' => $user->id]);
        $category = JobCategory::factory()->create(['user_id' => $user->id]);

        // Вакансия на текущей неделе
        Job::factory()->create([
            'user_id' => $user->id,
            'job_status_id' => $status->id,
            'job_category_id' => $category->id,
            'created_at' => CarbonImmutable::now(),
        ]);

        // Вакансия 2 недели назад
        Job::factory()->create([
            'user_id' => $user->id,
            'job_status_id' => $status->id,
            'job_category_id' => $category->id,
            'created_at' => CarbonImmutable::now()->subWeeks(2),
        ]);

        $this->actingAs($user);

        $response = $this->get(route('dashboard'));

        $response->assertInertia(
            fn (Assert $page) => $page
                ->component('Dashboard')
                ->loadDeferredProps(
                    fn (Assert $reload) => $reload
                        ->has('responseDynamics', 12)
                        ->where('responseDynamics.11.count', 1)
                        ->where('responseDynamics.9.count', 1)
                )
        );
    }

    public function test_dashboard_response_dynamics_scoped_to_user(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $otherStatus = JobStatus::factory()->create(['user_id' => $other->id]);
        $otherCategory = JobCategory::factory()->create(['user_id' => $other->id]);

        // Вакансия другого пользователя
        Job::factory()->create([
            'user_id' => $other->id,
            'job_status_id' => $otherStatus->id,
            'job_category_id' => $otherCategory->id,
            'created_at' => CarbonImmutable::now(),
        ]);

        $this->actingAs($user);

        $response = $this->get(route('dashboard'));

        $response->assertInertia(
            fn (Assert $page) => $page
                ->component('Dashboard')
                ->loadDeferredProps(
                    fn (Assert $reload) => $reload
                        ->has('responseDynamics', 12)
                        // Все точки должны быть нулевыми
                        ->where('responseDynamics.0.count', 0)
                        ->where('responseDynamics.11.count', 0)
                )
        );
    }

    public function test_dashboard_funnel_filters_by_category(): void
    {
        $user = User::factory()->create();
        $status = JobStatus::factory()->create(['user_id' => $user->id]);
        $categoryA = JobCategory::factory()->create(['user_id' => $user->id]);
        $categoryB = JobCategory::factory()->create(['user_id' => $user->id]);

        Job::factory()->count(3)->create([
            'user_id' => $user->id,
            'job_status_id' => $status->id,
            'job_category_id' => $categoryA->id,
        ]);
        Job::factory()->count(5)->create([
            'user_id' => $user->id,
            'job_status_id' => $status->id,
            'job_category_id' => $categoryB->id,
        ]);

        $this->actingAs($user);

        $response = $this->get(route('dashboard', ['funnel_category_id' => $categoryA->id]));

        $response->assertInertia(
            fn (Assert $page) => $page
                ->component('Dashboard')
                ->has('jobFunnel', 1)
                ->has(
                    'jobFunnel.0',
                    fn (Assert $item) => $item
                        ->where('id', $status->id)
                        ->where('count', 3)
                        ->etc()
                )
                ->where('funnelCategoryId', $categoryA->id)
        );
    }
}
