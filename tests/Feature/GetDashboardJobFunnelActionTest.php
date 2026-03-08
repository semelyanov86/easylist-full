<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Actions\Dashboard\GetDashboardJobFunnelAction;
use App\Enums\StatusColor;
use App\Models\Job;
use App\Models\JobCategory;
use App\Models\JobStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetDashboardJobFunnelActionTest extends TestCase
{
    use RefreshDatabase;

    private GetDashboardJobFunnelAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new GetDashboardJobFunnelAction();
    }

    public function test_returns_all_statuses_with_counts(): void
    {
        $user = User::factory()->create();
        $status1 = JobStatus::factory()->create(['user_id' => $user->id, 'color' => StatusColor::Green]);
        $status2 = JobStatus::factory()->create(['user_id' => $user->id, 'color' => StatusColor::Blue]);

        $category = JobCategory::factory()->create(['user_id' => $user->id]);

        Job::factory()->count(3)->create([
            'user_id' => $user->id,
            'job_status_id' => $status1->id,
            'job_category_id' => $category->id,
        ]);
        Job::factory()->count(2)->create([
            'user_id' => $user->id,
            'job_status_id' => $status2->id,
            'job_category_id' => $category->id,
        ]);

        $result = $this->action->execute($user);

        $this->assertCount(2, $result);

        $first = $result->firstWhere('id', $status1->id);
        $this->assertNotNull($first);
        $this->assertSame(3, $first->count);

        $second = $result->firstWhere('id', $status2->id);
        $this->assertNotNull($second);
        $this->assertSame(2, $second->count);
    }

    public function test_filters_by_category(): void
    {
        $user = User::factory()->create();
        $status = JobStatus::factory()->create(['user_id' => $user->id, 'color' => StatusColor::Green]);

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

        $resultA = $this->action->execute($user, $categoryA->id);
        $itemA = $resultA->firstWhere('id', $status->id);
        $this->assertNotNull($itemA);
        $this->assertSame(3, $itemA->count);

        $resultB = $this->action->execute($user, $categoryB->id);
        $itemB = $resultB->firstWhere('id', $status->id);
        $this->assertNotNull($itemB);
        $this->assertSame(5, $itemB->count);

        $resultAll = $this->action->execute($user);
        $itemAll = $resultAll->firstWhere('id', $status->id);
        $this->assertNotNull($itemAll);
        $this->assertSame(8, $itemAll->count);
    }

    public function test_shows_zero_count_for_statuses_without_jobs(): void
    {
        $user = User::factory()->create();
        JobStatus::factory()->create(['user_id' => $user->id, 'color' => StatusColor::Amber]);

        $result = $this->action->execute($user);

        $this->assertCount(1, $result);

        $item = $result->first();
        $this->assertNotNull($item);
        $this->assertSame(0, $item->count);
    }

    public function test_does_not_count_other_users_jobs(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $status = JobStatus::factory()->create(['user_id' => $user->id, 'color' => StatusColor::Red]);
        $otherStatus = JobStatus::factory()->create(['user_id' => $otherUser->id, 'color' => StatusColor::Blue]);

        $category = JobCategory::factory()->create(['user_id' => $user->id]);
        $otherCategory = JobCategory::factory()->create(['user_id' => $otherUser->id]);

        Job::factory()->count(2)->create([
            'user_id' => $user->id,
            'job_status_id' => $status->id,
            'job_category_id' => $category->id,
        ]);
        Job::factory()->count(10)->create([
            'user_id' => $otherUser->id,
            'job_status_id' => $otherStatus->id,
            'job_category_id' => $otherCategory->id,
        ]);

        $result = $this->action->execute($user);

        $this->assertCount(1, $result);

        $item = $result->first();
        $this->assertNotNull($item);
        $this->assertSame(2, $item->count);
    }

    public function test_respects_order_column(): void
    {
        $user = User::factory()->create();

        JobStatus::factory()->create([
            'user_id' => $user->id,
            'title' => 'Первый',
            'color' => StatusColor::Green,
        ]);
        JobStatus::factory()->create([
            'user_id' => $user->id,
            'title' => 'Второй',
            'color' => StatusColor::Blue,
        ]);

        $result = $this->action->execute($user);

        $first = $result->first();
        $last = $result->last();
        $this->assertNotNull($first);
        $this->assertNotNull($last);
        $this->assertSame('Первый', $first->title);
        $this->assertSame('Второй', $last->title);
    }

    public function test_returns_empty_collection_without_statuses(): void
    {
        $user = User::factory()->create();

        $result = $this->action->execute($user);

        $this->assertCount(0, $result);
    }

    public function test_excludes_soft_deleted_jobs(): void
    {
        $user = User::factory()->create();
        $status = JobStatus::factory()->create(['user_id' => $user->id, 'color' => StatusColor::Purple]);
        $category = JobCategory::factory()->create(['user_id' => $user->id]);

        Job::factory()->count(3)->create([
            'user_id' => $user->id,
            'job_status_id' => $status->id,
            'job_category_id' => $category->id,
        ]);

        $deletedJob = Job::factory()->create([
            'user_id' => $user->id,
            'job_status_id' => $status->id,
            'job_category_id' => $category->id,
        ]);
        $deletedJob->delete();

        $result = $this->action->execute($user);

        $item = $result->first();
        $this->assertNotNull($item);
        $this->assertSame(3, $item->count);
    }
}
