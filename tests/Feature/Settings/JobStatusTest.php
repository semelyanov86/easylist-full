<?php

declare(strict_types=1);

namespace Tests\Feature\Settings;

use App\Models\JobStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class JobStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_statuses_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('job-statuses.index'));

        $response->assertOk();
    }

    public function test_job_statuses_page_requires_authentication(): void
    {
        $response = $this->get(route('job-statuses.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_job_statuses_page_shows_only_own_statuses(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        JobStatus::factory()->for($user)->create(['title' => 'Мой статус']);
        JobStatus::factory()->for($otherUser)->create(['title' => 'Чужой статус']);

        $response = $this->actingAs($user)->get(route('job-statuses.index'));

        $response->assertOk();
        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('settings/JobStatuses')
                ->has('statuses', 1)
                ->where('statuses.0.title', 'Мой статус')
        );
    }

    public function test_statuses_are_ordered_by_order_column(): void
    {
        $user = User::factory()->create();

        // Создаём в произвольном порядке, затем вручную выставляем order_column
        $b = JobStatus::factory()->for($user)->create(['title' => 'Второй']);
        $a = JobStatus::factory()->for($user)->create(['title' => 'Первый']);
        $c = JobStatus::factory()->for($user)->create(['title' => 'Третий']);

        $a->update(['order_column' => 1]);
        $b->update(['order_column' => 2]);
        $c->update(['order_column' => 3]);

        $response = $this->actingAs($user)->get(route('job-statuses.index'));

        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->where('statuses.0.title', 'Первый')
                ->where('statuses.1.title', 'Второй')
                ->where('statuses.2.title', 'Третий')
        );
    }

    public function test_user_can_create_job_status(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('job-statuses.store'), [
            'title' => 'Новый статус',
            'description' => 'Описание',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('job-statuses.index'));
        $this->assertDatabaseHas('job_statuses', [
            'user_id' => $user->id,
            'title' => 'Новый статус',
            'description' => 'Описание',
        ]);
    }

    public function test_status_title_is_required(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('job-statuses.store'), [
            'title' => '',
        ]);

        $response->assertSessionHasErrors('title');
    }

    public function test_status_title_must_be_unique_per_user(): void
    {
        $user = User::factory()->create();
        JobStatus::factory()->for($user)->create(['title' => 'Дубликат']);

        $response = $this->actingAs($user)->post(route('job-statuses.store'), [
            'title' => 'Дубликат',
        ]);

        $response->assertSessionHasErrors('title');
    }

    public function test_different_users_can_have_same_title(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        JobStatus::factory()->for($otherUser)->create(['title' => 'Общий']);

        $response = $this->actingAs($user)->post(route('job-statuses.store'), [
            'title' => 'Общий',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('job-statuses.index'));
    }

    public function test_user_can_update_own_status(): void
    {
        $user = User::factory()->create();
        $status = JobStatus::factory()->for($user)->create(['title' => 'Старое']);

        $response = $this->actingAs($user)->patch(route('job-statuses.update', $status), [
            'title' => 'Новое',
            'description' => 'Обновлённое описание',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('job_statuses', [
            'id' => $status->id,
            'title' => 'Новое',
            'description' => 'Обновлённое описание',
        ]);
    }

    public function test_user_cannot_update_another_users_status(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $status = JobStatus::factory()->for($otherUser)->create();

        $response = $this->actingAs($user)->patch(route('job-statuses.update', $status), [
            'title' => 'Взлом',
        ]);

        $response->assertForbidden();
    }

    public function test_user_can_delete_own_status(): void
    {
        $user = User::factory()->create();
        $status = JobStatus::factory()->for($user)->create();

        $response = $this->actingAs($user)->delete(route('job-statuses.destroy', $status));

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('job_statuses', ['id' => $status->id]);
    }

    public function test_user_cannot_delete_another_users_status(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $status = JobStatus::factory()->for($otherUser)->create();

        $response = $this->actingAs($user)->delete(route('job-statuses.destroy', $status));

        $response->assertForbidden();
    }

    public function test_user_can_reorder_own_statuses(): void
    {
        $user = User::factory()->create();
        $a = JobStatus::factory()->for($user)->create(['title' => 'A', 'order_column' => 1]);
        $b = JobStatus::factory()->for($user)->create(['title' => 'B', 'order_column' => 2]);
        $c = JobStatus::factory()->for($user)->create(['title' => 'C', 'order_column' => 3]);

        $response = $this->actingAs($user)->post(route('job-statuses.reorder'), [
            'ids' => [$c->id, $a->id, $b->id],
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertSame(1, $c->fresh()?->order_column);
        $this->assertSame(2, $a->fresh()?->order_column);
        $this->assertSame(3, $b->fresh()?->order_column);
    }

    public function test_user_cannot_reorder_with_foreign_ids(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $own = JobStatus::factory()->for($user)->create(['order_column' => 1]);
        $foreign = JobStatus::factory()->for($otherUser)->create(['order_column' => 1]);

        $this->actingAs($user)->post(route('job-statuses.reorder'), [
            'ids' => [$foreign->id, $own->id],
        ]);

        // Чужой статус не изменился
        $this->assertSame(1, $foreign->fresh()?->order_column);
    }

    public function test_reorder_ids_must_be_array(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('job-statuses.reorder'), [
            'ids' => 'not-an-array',
        ]);

        $response->assertSessionHasErrors('ids');
    }
}
