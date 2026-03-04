<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Job;
use App\Models\JobCategory;
use App\Models\JobStatus;
use App\Models\JobTask;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class JobTaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_task_on_own_job(): void
    {
        $user = User::factory()->create();
        $job = $this->createJobForUser($user);

        $response = $this->actingAs($user)->post(route('job-tasks.store', $job), [
            'title' => 'Подготовить резюме',
            'deadline' => '2026-03-10',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('job_tasks', [
            'job_id' => $job->id,
            'user_id' => $user->id,
            'title' => 'Подготовить резюме',
        ]);
    }

    public function test_user_cannot_create_task_on_another_users_job(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $job = $this->createJobForUser($owner);

        $response = $this->actingAs($otherUser)->post(route('job-tasks.store', $job), [
            'title' => 'Попытка',
        ]);

        $response->assertForbidden();
    }

    public function test_title_is_required(): void
    {
        $user = User::factory()->create();
        $job = $this->createJobForUser($user);

        $response = $this->actingAs($user)->post(route('job-tasks.store', $job), [
            'title' => '',
        ]);

        $response->assertSessionHasErrors('title');
    }

    public function test_deadline_must_be_valid_date(): void
    {
        $user = User::factory()->create();
        $job = $this->createJobForUser($user);

        $response = $this->actingAs($user)->post(route('job-tasks.store', $job), [
            'title' => 'Задача',
            'deadline' => 'not-a-date',
        ]);

        $response->assertSessionHasErrors('deadline');
    }

    public function test_user_can_update_own_task(): void
    {
        $user = User::factory()->create();
        $job = $this->createJobForUser($user);
        $task = JobTask::factory()->create([
            'job_id' => $job->id,
            'user_id' => $user->id,
            'title' => 'Старое название',
        ]);

        $response = $this->actingAs($user)->patch(route('job-tasks.update', $task), [
            'title' => 'Новое название',
            'deadline' => '2026-04-01',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('job_tasks', [
            'id' => $task->id,
            'title' => 'Новое название',
        ]);
    }

    public function test_user_cannot_update_another_users_task(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $job = $this->createJobForUser($owner);
        $task = JobTask::factory()->create([
            'job_id' => $job->id,
            'user_id' => $owner->id,
        ]);

        $response = $this->actingAs($otherUser)->patch(route('job-tasks.update', $task), [
            'title' => 'Попытка изменить',
        ]);

        $response->assertForbidden();
    }

    public function test_user_can_toggle_task_completed(): void
    {
        $user = User::factory()->create();
        $job = $this->createJobForUser($user);
        $task = JobTask::factory()->create([
            'job_id' => $job->id,
            'user_id' => $user->id,
            'completed_at' => null,
        ]);

        $response = $this->actingAs($user)->patch(route('job-tasks.toggle', $task));

        $response->assertRedirect();
        $freshTask = $task->fresh();
        $this->assertNotNull($freshTask);
        $this->assertNotNull($freshTask->completed_at);

        // Повторный toggle — снимает выполнение
        $response = $this->actingAs($user)->patch(route('job-tasks.toggle', $task));

        $response->assertRedirect();
        $reopenedTask = $task->fresh();
        $this->assertNotNull($reopenedTask);
        $this->assertNull($reopenedTask->completed_at);
    }

    public function test_user_can_delete_own_task(): void
    {
        $user = User::factory()->create();
        $job = $this->createJobForUser($user);
        $task = JobTask::factory()->create([
            'job_id' => $job->id,
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->delete(route('job-tasks.destroy', $task));

        $response->assertRedirect();
        $this->assertDatabaseMissing('job_tasks', ['id' => $task->id]);
    }

    public function test_user_cannot_delete_another_users_task(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $job = $this->createJobForUser($owner);
        $task = JobTask::factory()->create([
            'job_id' => $job->id,
            'user_id' => $owner->id,
        ]);

        $response = $this->actingAs($otherUser)->delete(route('job-tasks.destroy', $task));

        $response->assertForbidden();
    }

    public function test_guest_cannot_create_task(): void
    {
        $user = User::factory()->create();
        $job = $this->createJobForUser($user);

        $response = $this->post(route('job-tasks.store', $job), [
            'title' => 'Задача',
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_tasks_cascade_deleted_when_job_force_deleted(): void
    {
        $user = User::factory()->create();
        $job = $this->createJobForUser($user);
        $task = JobTask::factory()->create([
            'job_id' => $job->id,
            'user_id' => $user->id,
        ]);

        $job->forceDelete();

        $this->assertDatabaseMissing('job_tasks', ['id' => $task->id]);
    }

    public function test_activity_log_created_on_task_added(): void
    {
        $user = User::factory()->create();
        $job = $this->createJobForUser($user);

        $this->actingAs($user)->post(route('job-tasks.store', $job), [
            'title' => 'Подготовить резюме',
        ]);

        $activity = Activity::query()
            ->where('log_name', 'job')
            ->where('event', 'task_added')
            ->where('subject_id', $job->id)
            ->latest()
            ->first();

        $this->assertNotNull($activity);
        $this->assertInstanceOf(Activity::class, $activity);
        $this->assertSame('Добавлена задача', $activity->description);
        $this->assertSame('Подготовить резюме', $activity->getExtraProperty('task_title'));
    }

    public function test_activity_log_created_on_task_removed(): void
    {
        $user = User::factory()->create();
        $job = $this->createJobForUser($user);
        $task = JobTask::factory()->create([
            'job_id' => $job->id,
            'user_id' => $user->id,
            'title' => 'Удаляемая задача',
        ]);

        $this->actingAs($user)->delete(route('job-tasks.destroy', $task));

        $activity = Activity::query()
            ->where('log_name', 'job')
            ->where('event', 'task_removed')
            ->where('subject_id', $job->id)
            ->latest()
            ->first();

        $this->assertNotNull($activity);
        $this->assertInstanceOf(Activity::class, $activity);
        $this->assertSame('Удалена задача', $activity->description);
        $this->assertSame('Удаляемая задача', $activity->getExtraProperty('task_title'));
    }

    public function test_optional_fields_can_be_null(): void
    {
        $user = User::factory()->create();
        $job = $this->createJobForUser($user);

        $response = $this->actingAs($user)->post(route('job-tasks.store', $job), [
            'title' => 'Простая задача',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('job_tasks', [
            'job_id' => $job->id,
            'title' => 'Простая задача',
            'deadline' => null,
            'completed_at' => null,
        ]);
    }

    private function createJobForUser(User $user): Job
    {
        $status = JobStatus::factory()->for($user)->create();
        $category = JobCategory::factory()->for($user)->create();

        return Job::factory()->for($user)->create([
            'job_status_id' => $status->id,
            'job_category_id' => $category->id,
        ]);
    }
}
