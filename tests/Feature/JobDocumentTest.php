<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Job;
use App\Models\JobCategory;
use App\Models\JobDocument;
use App\Models\JobStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class JobDocumentTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_upload_document_to_own_job(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $job = $this->createJobForUser($user);

        $file = UploadedFile::fake()->create('resume.pdf', 1024, 'application/pdf');

        $response = $this->actingAs($user)->post(route('job-documents.store', $job), [
            'title' => 'Моё резюме',
            'category' => 'resume',
            'file' => $file,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('job_documents', [
            'job_id' => $job->id,
            'user_id' => $user->id,
            'title' => 'Моё резюме',
            'category' => 'resume',
            'original_filename' => 'resume.pdf',
        ]);

        /** @var JobDocument $document */
        $document = JobDocument::query()->first();
        $this->assertNotNull($document->file_path);
        Storage::disk('local')->assertExists($document->file_path);
    }

    public function test_user_can_link_document_to_own_job(): void
    {
        $user = User::factory()->create();
        $job = $this->createJobForUser($user);

        $response = $this->actingAs($user)->post(route('job-documents.store', $job), [
            'title' => 'Портфолио на Behance',
            'category' => 'portfolio',
            'external_url' => 'https://behance.net/myportfolio',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('job_documents', [
            'job_id' => $job->id,
            'user_id' => $user->id,
            'title' => 'Портфолио на Behance',
            'category' => 'portfolio',
            'external_url' => 'https://behance.net/myportfolio',
        ]);
    }

    public function test_user_cannot_add_document_to_another_users_job(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $job = $this->createJobForUser($owner);

        $response = $this->actingAs($otherUser)->post(route('job-documents.store', $job), [
            'title' => 'Чужой документ',
            'category' => 'other',
            'external_url' => 'https://example.com',
        ]);

        $response->assertForbidden();
    }

    public function test_title_is_required(): void
    {
        $user = User::factory()->create();
        $job = $this->createJobForUser($user);

        $response = $this->actingAs($user)->post(route('job-documents.store', $job), [
            'category' => 'resume',
            'external_url' => 'https://example.com',
        ]);

        $response->assertSessionHasErrors('title');
    }

    public function test_category_is_required(): void
    {
        $user = User::factory()->create();
        $job = $this->createJobForUser($user);

        $response = $this->actingAs($user)->post(route('job-documents.store', $job), [
            'title' => 'Документ',
            'external_url' => 'https://example.com',
        ]);

        $response->assertSessionHasErrors('category');
    }

    public function test_invalid_category_is_rejected(): void
    {
        $user = User::factory()->create();
        $job = $this->createJobForUser($user);

        $response = $this->actingAs($user)->post(route('job-documents.store', $job), [
            'title' => 'Документ',
            'category' => 'invalid_category',
            'external_url' => 'https://example.com',
        ]);

        $response->assertSessionHasErrors('category');
    }

    public function test_file_or_url_is_required(): void
    {
        $user = User::factory()->create();
        $job = $this->createJobForUser($user);

        $response = $this->actingAs($user)->post(route('job-documents.store', $job), [
            'title' => 'Документ без источника',
            'category' => 'other',
        ]);

        $response->assertSessionHasErrors('file');
    }

    public function test_file_and_url_cannot_both_be_present(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $job = $this->createJobForUser($user);
        $file = UploadedFile::fake()->create('doc.pdf', 512, 'application/pdf');

        $response = $this->actingAs($user)->post(route('job-documents.store', $job), [
            'title' => 'Документ',
            'category' => 'other',
            'file' => $file,
            'external_url' => 'https://example.com',
        ]);

        $response->assertSessionHasErrors('file');
    }

    public function test_invalid_file_type_is_rejected(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $job = $this->createJobForUser($user);
        $file = UploadedFile::fake()->create('script.exe', 512, 'application/x-msdownload');

        $response = $this->actingAs($user)->post(route('job-documents.store', $job), [
            'title' => 'Документ',
            'category' => 'other',
            'file' => $file,
        ]);

        $response->assertSessionHasErrors('file');
    }

    public function test_user_can_delete_own_document(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $job = $this->createJobForUser($user);

        $filePath = 'documents/test-file.pdf';
        Storage::disk('local')->put($filePath, 'fake content');

        $document = JobDocument::factory()->withFile()->create([
            'job_id' => $job->id,
            'user_id' => $user->id,
            'file_path' => $filePath,
        ]);

        $response = $this->actingAs($user)->delete(route('job-documents.destroy', $document));

        $response->assertRedirect();
        $this->assertDatabaseMissing('job_documents', ['id' => $document->id]);
        Storage::disk('local')->assertMissing($filePath);
    }

    public function test_user_cannot_delete_another_users_document(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $job = $this->createJobForUser($owner);

        $document = JobDocument::factory()->withLink()->create([
            'job_id' => $job->id,
            'user_id' => $owner->id,
        ]);

        $response = $this->actingAs($otherUser)->delete(route('job-documents.destroy', $document));

        $response->assertForbidden();
    }

    public function test_document_upload_creates_activity_log(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $job = $this->createJobForUser($user);

        $this->actingAs($user)->post(route('job-documents.store', $job), [
            'title' => 'Резюме для лога',
            'category' => 'resume',
            'external_url' => 'https://example.com/resume',
        ]);

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => Job::class,
            'subject_id' => $job->id,
            'causer_id' => $user->id,
            'event' => 'document_added',
        ]);
    }

    public function test_document_delete_creates_activity_log(): void
    {
        $user = User::factory()->create();
        $job = $this->createJobForUser($user);

        $document = JobDocument::factory()->withLink()->create([
            'job_id' => $job->id,
            'user_id' => $user->id,
        ]);

        $this->actingAs($user)->delete(route('job-documents.destroy', $document));

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => Job::class,
            'subject_id' => $job->id,
            'event' => 'document_removed',
        ]);
    }

    public function test_guest_cannot_add_document(): void
    {
        $user = User::factory()->create();
        $job = $this->createJobForUser($user);

        $response = $this->post(route('job-documents.store', $job), [
            'title' => 'Документ гостя',
            'category' => 'other',
            'external_url' => 'https://example.com',
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_documents_are_deleted_when_job_is_deleted(): void
    {
        $user = User::factory()->create();
        $job = $this->createJobForUser($user);

        $document = JobDocument::factory()->withLink()->create([
            'job_id' => $job->id,
            'user_id' => $user->id,
        ]);

        $job->forceDelete();

        $this->assertDatabaseMissing('job_documents', ['id' => $document->id]);
    }

    public function test_user_can_download_own_document(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $job = $this->createJobForUser($user);

        $filePath = 'documents/download-test.pdf';
        Storage::disk('local')->put($filePath, 'fake pdf content');

        $document = JobDocument::factory()->withFile()->create([
            'job_id' => $job->id,
            'user_id' => $user->id,
            'file_path' => $filePath,
            'original_filename' => 'download-test.pdf',
        ]);

        $response = $this->actingAs($user)->get(route('job-documents.download', $document));

        $response->assertOk();
    }

    public function test_user_cannot_download_another_users_document(): void
    {
        Storage::fake('local');

        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $job = $this->createJobForUser($owner);

        $filePath = 'documents/private.pdf';
        Storage::disk('local')->put($filePath, 'secret content');

        $document = JobDocument::factory()->withFile()->create([
            'job_id' => $job->id,
            'user_id' => $owner->id,
            'file_path' => $filePath,
        ]);

        $response = $this->actingAs($otherUser)->get(route('job-documents.download', $document));

        $response->assertForbidden();
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
