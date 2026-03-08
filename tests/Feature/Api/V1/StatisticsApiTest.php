<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\Job;
use App\Models\JobCategory;
use App\Models\JobStatus;
use App\Models\JobTask;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class StatisticsApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private JobStatus $status;

    private JobCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->status = JobStatus::factory()->for($this->user)->create();
        $this->category = JobCategory::factory()->for($this->user)->create();
    }

    public function test_statistics_returns_all_dashboard_sections(): void
    {
        Sanctum::actingAs($this->user);

        $job = Job::factory()
            ->for($this->user)
            ->for($this->status, 'status')
            ->for($this->category, 'category')
            ->favorite()
            ->create();

        JobTask::factory()->for($job)->for($this->user)->create();

        $skill = Skill::factory()->for($this->user)->create();
        $job->skills()->attach($skill);

        $response = $this->getJson('/api/v1/statistics');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'type',
                    'id',
                    'attributes' => [
                        'recent_activities',
                        'pending_tasks',
                        'favorite_jobs',
                        'recent_jobs',
                        'skills_demand',
                        'response_dynamics',
                        'job_funnel',
                    ],
                ],
            ]);
    }

    public function test_statistics_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/statistics');

        $response->assertUnauthorized();
    }

    public function test_statistics_does_not_return_other_users_data(): void
    {
        Sanctum::actingAs($this->user);

        $otherUser = User::factory()->create();
        $otherStatus = JobStatus::factory()->for($otherUser)->create();
        $otherCategory = JobCategory::factory()->for($otherUser)->create();
        $otherJob = Job::factory()
            ->for($otherUser)
            ->for($otherStatus, 'status')
            ->for($otherCategory, 'category')
            ->favorite()
            ->create();
        JobTask::factory()->for($otherJob)->for($otherUser)->create();

        $response = $this->getJson('/api/v1/statistics');

        $response->assertOk()
            ->assertJsonPath('data.attributes.pending_tasks', [])
            ->assertJsonPath('data.attributes.favorite_jobs', [])
            ->assertJsonPath('data.attributes.recent_jobs', [])
            ->assertJsonPath('data.attributes.skills_demand', [])
            ->assertJsonPath('data.attributes.recent_activities', []);
    }

    public function test_statistics_returns_correct_json_api_format(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/v1/statistics');

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/vnd.api+json')
            ->assertJsonPath('data.type', 'statistics')
            ->assertJsonPath('data.id', (string) $this->user->id);
    }

    public function test_statistics_funnel_supports_category_filter(): void
    {
        Sanctum::actingAs($this->user);

        $anotherCategory = JobCategory::factory()->for($this->user)->create();

        Job::factory()
            ->count(3)
            ->for($this->user)
            ->for($this->status, 'status')
            ->for($this->category, 'category')
            ->create();

        Job::factory()
            ->count(2)
            ->for($this->user)
            ->for($this->status, 'status')
            ->for($anotherCategory, 'category')
            ->create();

        // Без фильтра — воронка учитывает все вакансии
        $responseAll = $this->getJson('/api/v1/statistics');
        $responseAll->assertOk();

        /** @var array<int, array{count: int}> $funnelAll */
        $funnelAll = $responseAll->json('data.attributes.job_funnel');
        $totalAll = array_sum(array_column($funnelAll, 'count'));

        // С фильтром — только вакансии указанной категории
        $responseFiltered = $this->getJson('/api/v1/statistics?funnel_category_id=' . $this->category->id);
        $responseFiltered->assertOk();

        /** @var array<int, array{count: int}> $funnelFiltered */
        $funnelFiltered = $responseFiltered->json('data.attributes.job_funnel');
        $totalFiltered = array_sum(array_column($funnelFiltered, 'count'));

        $this->assertSame(5, $totalAll);
        $this->assertSame(3, $totalFiltered);
    }

    public function test_statistics_returns_empty_sections_for_new_user(): void
    {
        $newUser = User::factory()->create();
        Sanctum::actingAs($newUser);

        $response = $this->getJson('/api/v1/statistics');

        $response->assertOk()
            ->assertJsonPath('data.attributes.recent_activities', [])
            ->assertJsonPath('data.attributes.pending_tasks', [])
            ->assertJsonPath('data.attributes.favorite_jobs', [])
            ->assertJsonPath('data.attributes.recent_jobs', [])
            ->assertJsonPath('data.attributes.skills_demand', [])
            ->assertJsonPath('data.attributes.job_funnel', []);

        // response_dynamics всегда возвращает 12 недель (с count=0 для нового пользователя)
        /** @var list<array{label: string, count: int}> $dynamics */
        $dynamics = $response->json('data.attributes.response_dynamics');
        $this->assertCount(12, $dynamics);
        $this->assertSame(0, array_sum(array_column($dynamics, 'count')));
    }
}
