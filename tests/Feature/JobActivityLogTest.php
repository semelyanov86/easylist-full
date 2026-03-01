<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Actions\Job\MoveJobToStatusAction;
use App\Actions\JobComment\CreateJobCommentAction;
use App\Models\Job;
use App\Models\JobCategory;
use App\Models\JobStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class JobActivityLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_activity_logged_on_job_creation(): void
    {
        $user = User::factory()->create();
        $status = JobStatus::factory()->for($user)->create();
        $category = JobCategory::factory()->for($user)->create();

        $this->actingAs($user);

        $job = Job::factory()->for($user)->create([
            'job_status_id' => $status->id,
            'job_category_id' => $category->id,
        ]);

        $activity = Activity::query()
            ->where('subject_type', Job::class)
            ->where('subject_id', $job->id)
            ->where('event', 'created')
            ->first();

        $this->assertNotNull($activity);
        $this->assertSame('Вакансия создана', $activity->description);
        $this->assertSame('job', $activity->log_name);
    }

    public function test_activity_logged_on_job_update(): void
    {
        $user = User::factory()->create();
        $status = JobStatus::factory()->for($user)->create();
        $category = JobCategory::factory()->for($user)->create();

        $this->actingAs($user);

        $job = Job::factory()->for($user)->create([
            'job_status_id' => $status->id,
            'job_category_id' => $category->id,
            'title' => 'Старое название',
        ]);

        $job->update(['title' => 'Новое название']);

        $activity = Activity::query()
            ->where('subject_type', Job::class)
            ->where('subject_id', $job->id)
            ->where('event', 'updated')
            ->first();

        $this->assertNotNull($activity);
        $this->assertSame('Вакансия обновлена', $activity->description);
    }

    public function test_no_activity_logged_when_nothing_changes(): void
    {
        $user = User::factory()->create();
        $status = JobStatus::factory()->for($user)->create();
        $category = JobCategory::factory()->for($user)->create();

        $this->actingAs($user);

        $job = Job::factory()->for($user)->create([
            'job_status_id' => $status->id,
            'job_category_id' => $category->id,
            'title' => 'Название',
        ]);

        $countBefore = Activity::query()
            ->where('subject_type', Job::class)
            ->where('subject_id', $job->id)
            ->count();

        $job->update(['title' => 'Название']);

        $countAfter = Activity::query()
            ->where('subject_type', Job::class)
            ->where('subject_id', $job->id)
            ->count();

        $this->assertSame($countBefore, $countAfter);
    }

    public function test_activity_logged_on_job_deletion(): void
    {
        $user = User::factory()->create();
        $status = JobStatus::factory()->for($user)->create();
        $category = JobCategory::factory()->for($user)->create();

        $this->actingAs($user);

        $job = Job::factory()->for($user)->create([
            'job_status_id' => $status->id,
            'job_category_id' => $category->id,
        ]);

        $jobId = $job->id;
        $job->delete();

        $activity = Activity::query()
            ->where('subject_type', Job::class)
            ->where('subject_id', $jobId)
            ->where('event', 'deleted')
            ->first();

        $this->assertNotNull($activity);
        $this->assertSame('Вакансия удалена', $activity->description);
    }

    public function test_custom_activity_logged_on_status_change(): void
    {
        $user = User::factory()->create();
        $oldStatus = JobStatus::factory()->for($user)->create(['title' => 'Новая']);
        $newStatus = JobStatus::factory()->for($user)->create(['title' => 'На рассмотрении']);
        $category = JobCategory::factory()->for($user)->create();

        $job = Job::factory()->for($user)->create([
            'job_status_id' => $oldStatus->id,
            'job_category_id' => $category->id,
        ]);

        $action = resolve(MoveJobToStatusAction::class);
        $action->execute($user, $job, $newStatus->id);

        $activity = Activity::query()
            ->where('subject_type', Job::class)
            ->where('subject_id', $job->id)
            ->where('event', 'status_changed')
            ->first();

        $this->assertNotNull($activity);
        $this->assertInstanceOf(Activity::class, $activity);
        $this->assertSame('Статус изменён', $activity->description);
        $this->assertSame('Новая', $activity->getExtraProperty('old_status'));
        $this->assertSame('На рассмотрении', $activity->getExtraProperty('new_status'));
    }

    public function test_activity_logged_when_comment_added(): void
    {
        $user = User::factory()->create();
        $status = JobStatus::factory()->for($user)->create();
        $category = JobCategory::factory()->for($user)->create();

        $job = Job::factory()->for($user)->create([
            'job_status_id' => $status->id,
            'job_category_id' => $category->id,
        ]);

        $action = resolve(CreateJobCommentAction::class);
        $action->execute($user, $job, ['body' => 'Тестовый комментарий']);

        $activity = Activity::query()
            ->where('subject_type', Job::class)
            ->where('subject_id', $job->id)
            ->where('event', 'comment_added')
            ->first();

        $this->assertNotNull($activity);
        $this->assertInstanceOf(Activity::class, $activity);
        $this->assertSame('Добавлен комментарий', $activity->description);
        $this->assertSame('Тестовый комментарий', $activity->getExtraProperty('comment_body'));
    }
}
