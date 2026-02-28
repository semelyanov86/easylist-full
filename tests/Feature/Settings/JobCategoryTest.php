<?php

declare(strict_types=1);

namespace Tests\Feature\Settings;

use App\Models\JobCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class JobCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_job_category(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->from(route('dashboard'))
            ->post(route('job-categories.store'), [
                'title' => 'Новая категория',
                'description' => 'Описание',
                'currency' => 'rub',
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('job_categories', [
            'user_id' => $user->id,
            'title' => 'Новая категория',
            'description' => 'Описание',
        ]);
    }

    public function test_store_requires_authentication(): void
    {
        $response = $this->post(route('job-categories.store'), [
            'title' => 'Тест',
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_category_title_is_required(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('job-categories.store'), [
            'title' => '',
            'currency' => 'rub',
        ]);

        $response->assertSessionHasErrors('title');
    }

    public function test_category_title_must_be_unique_per_user(): void
    {
        $user = User::factory()->create();
        JobCategory::factory()->for($user)->create(['title' => 'Дубликат']);

        $response = $this->actingAs($user)->post(route('job-categories.store'), [
            'title' => 'Дубликат',
            'currency' => 'rub',
        ]);

        $response->assertSessionHasErrors('title');
    }

    public function test_different_users_can_have_same_title(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        JobCategory::factory()->for($otherUser)->create(['title' => 'Общая']);

        $response = $this->actingAs($user)
            ->from(route('dashboard'))
            ->post(route('job-categories.store'), [
                'title' => 'Общая',
                'currency' => 'rub',
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('dashboard'));
    }

    public function test_category_title_max_length(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('job-categories.store'), [
            'title' => str_repeat('а', 256),
            'currency' => 'rub',
        ]);

        $response->assertSessionHasErrors('title');
    }

    public function test_user_can_update_own_category(): void
    {
        $user = User::factory()->create();
        $category = JobCategory::factory()->for($user)->create(['title' => 'Старое']);

        $response = $this->actingAs($user)->patch(route('job-categories.update', $category), [
            'title' => 'Новое',
            'description' => 'Обновлённое описание',
            'currency' => 'rub',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('job_categories', [
            'id' => $category->id,
            'title' => 'Новое',
            'description' => 'Обновлённое описание',
        ]);
    }

    public function test_user_cannot_update_another_users_category(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $category = JobCategory::factory()->for($otherUser)->create();

        $response = $this->actingAs($user)->patch(route('job-categories.update', $category), [
            'title' => 'Взлом',
            'currency' => 'rub',
        ]);

        $response->assertForbidden();
    }

    public function test_user_can_delete_own_category(): void
    {
        $user = User::factory()->create();
        $category = JobCategory::factory()->for($user)->create();

        $response = $this->actingAs($user)->delete(route('job-categories.destroy', $category));

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('job_categories', ['id' => $category->id]);
    }

    public function test_user_cannot_delete_another_users_category(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $category = JobCategory::factory()->for($otherUser)->create();

        $response = $this->actingAs($user)->delete(route('job-categories.destroy', $category));

        $response->assertForbidden();
    }

    public function test_user_can_reorder_own_categories(): void
    {
        $user = User::factory()->create();
        $a = JobCategory::factory()->for($user)->create(['title' => 'A', 'order_column' => 1]);
        $b = JobCategory::factory()->for($user)->create(['title' => 'B', 'order_column' => 2]);
        $c = JobCategory::factory()->for($user)->create(['title' => 'C', 'order_column' => 3]);

        $response = $this->actingAs($user)->post(route('job-categories.reorder'), [
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

        $own = JobCategory::factory()->for($user)->create(['order_column' => 1]);
        $foreign = JobCategory::factory()->for($otherUser)->create(['order_column' => 1]);

        $this->actingAs($user)->post(route('job-categories.reorder'), [
            'ids' => [$foreign->id, $own->id],
        ]);

        // Чужая категория не изменилась
        $this->assertSame(1, $foreign->fresh()?->order_column);
    }

    public function test_reorder_ids_must_be_array(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('job-categories.reorder'), [
            'ids' => 'not-an-array',
        ]);

        $response->assertSessionHasErrors('ids');
    }

    public function test_job_categories_shared_via_inertia(): void
    {
        $user = User::factory()->create();
        JobCategory::factory()->for($user)->create(['title' => 'Shared категория']);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->has('jobCategories', 1)
                ->where('jobCategories.0.title', 'Shared категория')
        );
    }

    public function test_job_categories_shared_are_ordered(): void
    {
        $user = User::factory()->create();

        $b = JobCategory::factory()->for($user)->create(['title' => 'Второй']);
        $a = JobCategory::factory()->for($user)->create(['title' => 'Первый']);

        $a->update(['order_column' => 1]);
        $b->update(['order_column' => 2]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->where('jobCategories.0.title', 'Первый')
                ->where('jobCategories.1.title', 'Второй')
        );
    }

    public function test_job_categories_not_shared_for_guests(): void
    {
        $response = $this->get('/');

        $response->assertOk();
    }

    public function test_user_can_create_category_with_currency(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->from(route('dashboard'))
            ->post(route('job-categories.store'), [
                'title' => 'Зарубежные вакансии',
                'description' => null,
                'currency' => 'usd',
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('job_categories', [
            'user_id' => $user->id,
            'title' => 'Зарубежные вакансии',
            'currency' => 'usd',
        ]);
    }

    public function test_invalid_currency_is_rejected(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('job-categories.store'), [
            'title' => 'Тест',
            'currency' => 'btc',
        ]);

        $response->assertSessionHasErrors('currency');
    }

    public function test_user_can_update_category_currency(): void
    {
        $user = User::factory()->create();
        $category = JobCategory::factory()->for($user)->create([
            'title' => 'Категория',
            'currency' => 'rub',
        ]);

        $response = $this->actingAs($user)->patch(route('job-categories.update', $category), [
            'title' => 'Категория',
            'currency' => 'eur',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('job_categories', [
            'id' => $category->id,
            'currency' => 'eur',
        ]);
    }

    public function test_currency_shared_via_inertia(): void
    {
        $user = User::factory()->create();
        JobCategory::factory()->for($user)->create([
            'title' => 'USD категория',
            'currency' => 'usd',
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->has('jobCategories', 1)
                ->where('jobCategories.0.currency', 'usd')
                ->where('jobCategories.0.currency_symbol', '$')
        );
    }
}
