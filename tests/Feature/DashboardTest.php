<?php

declare(strict_types=1);

namespace Tests\Feature;

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
}
