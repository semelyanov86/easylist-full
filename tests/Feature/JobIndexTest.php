<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\JobsViewMode;
use App\Models\Job;
use App\Models\JobCategory;
use App\Models\JobStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class JobIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_sees_only_own_jobs(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $status = JobStatus::factory()->for($user)->create();
        $category = JobCategory::factory()->for($user)->create();

        $otherStatus = JobStatus::factory()->for($otherUser)->create();
        $otherCategory = JobCategory::factory()->for($otherUser)->create();

        Job::factory()->for($user)->create([
            'title' => 'Моя вакансия',
            'job_status_id' => $status->id,
            'job_category_id' => $category->id,
        ]);

        Job::factory()->for($otherUser)->create([
            'title' => 'Чужая вакансия',
            'job_status_id' => $otherStatus->id,
            'job_category_id' => $otherCategory->id,
        ]);

        $response = $this->actingAs($user)->get(route('jobs.index'));

        $response->assertOk();
        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('jobs/Index')
                ->has('jobs.data', 1)
                ->where('jobs.data.0.title', 'Моя вакансия')
        );
    }

    public function test_pagination_works_with_query_string(): void
    {
        $user = User::factory()->create();
        $status = JobStatus::factory()->for($user)->create();
        $category = JobCategory::factory()->for($user)->create();

        Job::factory()
            ->count(20)
            ->for($user)
            ->create([
                'job_status_id' => $status->id,
                'job_category_id' => $category->id,
            ]);

        $response = $this->actingAs($user)->get(route('jobs.index', ['page' => 2]));

        $response->assertOk();
        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('jobs/Index')
                ->where('jobs.current_page', 2)
                ->has('jobs.data', 5)
        );
    }

    public function test_search_works_on_title_location_company(): void
    {
        $user = User::factory()->create();
        $status = JobStatus::factory()->for($user)->create();
        $category = JobCategory::factory()->for($user)->create();

        Job::factory()->for($user)->create([
            'title' => 'PHP Developer',
            'company_name' => 'Acme Corp',
            'location_city' => 'Москва',
            'job_status_id' => $status->id,
            'job_category_id' => $category->id,
        ]);

        Job::factory()->for($user)->create([
            'title' => 'Java Developer',
            'company_name' => 'Other Corp',
            'location_city' => 'Питер',
            'job_status_id' => $status->id,
            'job_category_id' => $category->id,
        ]);

        // Поиск по title
        $response = $this->actingAs($user)->get(route('jobs.index', ['search' => 'PHP']));
        $response->assertInertia(fn (AssertableInertia $page) => $page->has('jobs.data', 1));

        // Поиск по company_name
        $response = $this->actingAs($user)->get(route('jobs.index', ['search' => 'Acme']));
        $response->assertInertia(fn (AssertableInertia $page) => $page->has('jobs.data', 1));

        // Поиск по location_city
        $response = $this->actingAs($user)->get(route('jobs.index', ['search' => 'Москва']));
        $response->assertInertia(fn (AssertableInertia $page) => $page->has('jobs.data', 1));
    }

    public function test_status_filter_works(): void
    {
        $user = User::factory()->create();
        $statusA = JobStatus::factory()->for($user)->create(['title' => 'Отклик']);
        $statusB = JobStatus::factory()->for($user)->create(['title' => 'Интервью']);
        $category = JobCategory::factory()->for($user)->create();

        Job::factory()->for($user)->create([
            'job_status_id' => $statusA->id,
            'job_category_id' => $category->id,
        ]);

        Job::factory()->for($user)->create([
            'job_status_id' => $statusB->id,
            'job_category_id' => $category->id,
        ]);

        $response = $this->actingAs($user)->get(route('jobs.index', ['status_id' => $statusA->id]));

        $response->assertOk();
        $response->assertInertia(
            fn (AssertableInertia $page) => $page->has('jobs.data', 1)
        );
    }

    public function test_date_filter_works(): void
    {
        $user = User::factory()->create();
        $status = JobStatus::factory()->for($user)->create();
        $category = JobCategory::factory()->for($user)->create();

        Job::factory()->for($user)->create([
            'title' => 'Старая',
            'job_status_id' => $status->id,
            'job_category_id' => $category->id,
            'created_at' => '2025-01-15 10:00:00',
        ]);

        Job::factory()->for($user)->create([
            'title' => 'Новая',
            'job_status_id' => $status->id,
            'job_category_id' => $category->id,
            'created_at' => '2026-02-20 10:00:00',
        ]);

        $response = $this->actingAs($user)->get(route('jobs.index', [
            'date_from' => '2026-01-01',
            'date_to' => '2026-12-31',
        ]));

        $response->assertOk();
        $response->assertInertia(
            fn (AssertableInertia $page) => $page->has('jobs.data', 1)
        );
    }

    public function test_soft_deleted_jobs_not_shown(): void
    {
        $user = User::factory()->create();
        $status = JobStatus::factory()->for($user)->create();
        $category = JobCategory::factory()->for($user)->create();

        $job = Job::factory()->for($user)->create([
            'job_status_id' => $status->id,
            'job_category_id' => $category->id,
        ]);
        $job->delete();

        Job::factory()->for($user)->create([
            'job_status_id' => $status->id,
            'job_category_id' => $category->id,
        ]);

        $response = $this->actingAs($user)->get(route('jobs.index'));

        $response->assertOk();
        $response->assertInertia(
            fn (AssertableInertia $page) => $page->has('jobs.data', 1)
        );
    }

    public function test_category_filter_works(): void
    {
        $user = User::factory()->create();
        $status = JobStatus::factory()->for($user)->create();
        $categoryA = JobCategory::factory()->for($user)->create(['title' => 'IT']);
        $categoryB = JobCategory::factory()->for($user)->create(['title' => 'Дизайн']);

        Job::factory()->for($user)->create([
            'job_status_id' => $status->id,
            'job_category_id' => $categoryA->id,
        ]);

        Job::factory()->for($user)->create([
            'job_status_id' => $status->id,
            'job_category_id' => $categoryB->id,
        ]);

        $response = $this->actingAs($user)->get(route('jobs.index', ['job_category_id' => $categoryA->id]));

        $response->assertOk();
        $response->assertInertia(
            fn (AssertableInertia $page) => $page->has('jobs.data', 1)
        );
    }

    public function test_view_mode_returned_from_user_profile(): void
    {
        $user = User::factory()->create();
        $user->update(['jobs_view_mode' => JobsViewMode::Kanban]);

        $response = $this->actingAs($user)->get(route('jobs.index'));

        $response->assertOk();
        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('jobs/Index')
                ->where('viewMode', 'kanban')
        );
    }

    public function test_default_view_mode_is_table(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('jobs.index'));

        $response->assertOk();
        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('jobs/Index')
                ->where('viewMode', 'table')
        );
    }

    public function test_kanban_columns_contain_correct_data(): void
    {
        $user = User::factory()->create();
        $statusA = JobStatus::factory()->for($user)->create(['title' => 'Отклик']);
        $statusB = JobStatus::factory()->for($user)->create(['title' => 'Интервью']);
        $category = JobCategory::factory()->for($user)->create();

        Job::factory()->for($user)->create([
            'title' => 'PHP Developer',
            'job_status_id' => $statusA->id,
            'job_category_id' => $category->id,
        ]);

        Job::factory()->for($user)->create([
            'title' => 'Go Developer',
            'job_status_id' => $statusB->id,
            'job_category_id' => $category->id,
        ]);

        $response = $this->actingAs($user)->get(route('jobs.index'));

        $response->assertOk();
        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('jobs/Index')
                ->has('kanbanColumns', 2)
                ->where('kanbanColumns.0.title', 'Отклик')
                ->has('kanbanColumns.0.jobs', 1)
                ->where('kanbanColumns.1.title', 'Интервью')
                ->has('kanbanColumns.1.jobs', 1)
        );
    }

    public function test_favorite_filter_shows_only_favorites(): void
    {
        $user = User::factory()->create();
        $status = JobStatus::factory()->for($user)->create();
        $category = JobCategory::factory()->for($user)->create();

        Job::factory()->for($user)->favorite()->create([
            'title' => 'Избранная',
            'job_status_id' => $status->id,
            'job_category_id' => $category->id,
        ]);

        Job::factory()->for($user)->create([
            'title' => 'Обычная',
            'job_status_id' => $status->id,
            'job_category_id' => $category->id,
        ]);

        $response = $this->actingAs($user)->get(route('jobs.index', ['is_favorite' => 1]));

        $response->assertOk();
        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->has('jobs.data', 1)
                ->where('jobs.data.0.title', 'Избранная')
        );
    }

    public function test_favorites_count_shared_correctly(): void
    {
        $user = User::factory()->create();
        $status = JobStatus::factory()->for($user)->create();
        $category = JobCategory::factory()->for($user)->create();

        Job::factory()->for($user)->favorite()->count(3)->create([
            'job_status_id' => $status->id,
            'job_category_id' => $category->id,
        ]);

        Job::factory()->for($user)->count(2)->create([
            'job_status_id' => $status->id,
            'job_category_id' => $category->id,
        ]);

        $response = $this->actingAs($user)->get(route('jobs.index'));

        $response->assertOk();
        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->where('favoritesCount', 3)
        );
    }

    public function test_kanban_columns_respect_search_filter(): void
    {
        $user = User::factory()->create();
        $status = JobStatus::factory()->for($user)->create(['title' => 'Отклик']);
        $category = JobCategory::factory()->for($user)->create();

        Job::factory()->for($user)->create([
            'title' => 'PHP Developer',
            'job_status_id' => $status->id,
            'job_category_id' => $category->id,
        ]);

        Job::factory()->for($user)->create([
            'title' => 'Java Developer',
            'job_status_id' => $status->id,
            'job_category_id' => $category->id,
        ]);

        $response = $this->actingAs($user)->get(route('jobs.index', ['search' => 'PHP']));

        $response->assertOk();
        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->has('kanbanColumns', 1)
                ->has('kanbanColumns.0.jobs', 1)
                ->where('kanbanColumns.0.jobs.0.title', 'PHP Developer')
        );
    }
}
