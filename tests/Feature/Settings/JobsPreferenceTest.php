<?php

declare(strict_types=1);

namespace Tests\Feature\Settings;

use App\Enums\JobsViewMode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobsPreferenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_save_table_view_mode(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put(route('preferences.jobs-view-mode.update'), [
            'view_mode' => 'table',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'jobs_view_mode' => 'table',
        ]);
    }

    public function test_authenticated_user_can_save_kanban_view_mode(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put(route('preferences.jobs-view-mode.update'), [
            'view_mode' => 'kanban',
        ]);

        $response->assertRedirect();
        $user->refresh();
        $this->assertSame(JobsViewMode::Kanban, $user->jobs_view_mode);
    }

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->put(route('preferences.jobs-view-mode.update'), [
            'view_mode' => 'kanban',
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_invalid_view_mode_fails_validation(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put(route('preferences.jobs-view-mode.update'), [
            'view_mode' => 'invalid',
        ]);

        $response->assertSessionHasErrors('view_mode');
    }

    public function test_missing_view_mode_fails_validation(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put(route('preferences.jobs-view-mode.update'), []);

        $response->assertSessionHasErrors('view_mode');
    }
}
