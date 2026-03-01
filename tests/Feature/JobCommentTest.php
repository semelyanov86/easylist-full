<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Job;
use App\Models\JobCategory;
use App\Models\JobComment;
use App\Models\JobStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobCommentTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_comment_on_own_job(): void
    {
        $user = User::factory()->create();
        $job = $this->createJobForUser($user);

        $response = $this->actingAs($user)->post(route('job-comments.store', $job), [
            'body' => 'Отправил резюме',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('job_comments', [
            'job_id' => $job->id,
            'user_id' => $user->id,
            'body' => 'Отправил резюме',
        ]);
    }

    public function test_user_cannot_create_comment_on_another_users_job(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $job = $this->createJobForUser($owner);

        $response = $this->actingAs($otherUser)->post(route('job-comments.store', $job), [
            'body' => 'Чужой комментарий',
        ]);

        $response->assertForbidden();
    }

    public function test_body_is_required_for_creating_comment(): void
    {
        $user = User::factory()->create();
        $job = $this->createJobForUser($user);

        $response = $this->actingAs($user)->post(route('job-comments.store', $job), []);

        $response->assertSessionHasErrors('body');
    }

    public function test_body_max_length_is_validated(): void
    {
        $user = User::factory()->create();
        $job = $this->createJobForUser($user);

        $response = $this->actingAs($user)->post(route('job-comments.store', $job), [
            'body' => str_repeat('a', 5001),
        ]);

        $response->assertSessionHasErrors('body');
    }

    public function test_user_can_update_own_comment(): void
    {
        $user = User::factory()->create();
        $job = $this->createJobForUser($user);
        $comment = JobComment::factory()->create([
            'job_id' => $job->id,
            'user_id' => $user->id,
            'body' => 'Старый текст',
        ]);

        $response = $this->actingAs($user)->patch(route('job-comments.update', $comment), [
            'body' => 'Новый текст',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('job_comments', [
            'id' => $comment->id,
            'body' => 'Новый текст',
        ]);
    }

    public function test_user_cannot_update_another_users_comment(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $job = $this->createJobForUser($owner);
        $comment = JobComment::factory()->create([
            'job_id' => $job->id,
            'user_id' => $owner->id,
        ]);

        $response = $this->actingAs($otherUser)->patch(route('job-comments.update', $comment), [
            'body' => 'Попытка изменить',
        ]);

        $response->assertForbidden();
    }

    public function test_user_can_delete_own_comment(): void
    {
        $user = User::factory()->create();
        $job = $this->createJobForUser($user);
        $comment = JobComment::factory()->create([
            'job_id' => $job->id,
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->delete(route('job-comments.destroy', $comment));

        $response->assertRedirect();
        $this->assertDatabaseMissing('job_comments', ['id' => $comment->id]);
    }

    public function test_user_cannot_delete_another_users_comment(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $job = $this->createJobForUser($owner);
        $comment = JobComment::factory()->create([
            'job_id' => $job->id,
            'user_id' => $owner->id,
        ]);

        $response = $this->actingAs($otherUser)->delete(route('job-comments.destroy', $comment));

        $response->assertForbidden();
    }

    public function test_guest_cannot_create_comment(): void
    {
        $user = User::factory()->create();
        $job = $this->createJobForUser($user);

        $response = $this->post(route('job-comments.store', $job), [
            'body' => 'Комментарий гостя',
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_comments_are_deleted_when_job_is_deleted(): void
    {
        $user = User::factory()->create();
        $job = $this->createJobForUser($user);
        $comment = JobComment::factory()->create([
            'job_id' => $job->id,
            'user_id' => $user->id,
        ]);

        $job->forceDelete();

        $this->assertDatabaseMissing('job_comments', ['id' => $comment->id]);
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
