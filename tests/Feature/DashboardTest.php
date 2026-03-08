<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Job;
use App\Models\JobCategory;
use App\Models\JobStatus;
use App\Models\User;
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
