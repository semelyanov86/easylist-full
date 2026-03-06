<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Contracts\AiCoverLetterContract;
use App\Models\Job;
use App\Models\User;
use App\Services\FakeAiCoverLetterService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GenerateCoverLetterTest extends TestCase
{
    use RefreshDatabase;

    private FakeAiCoverLetterService $fakeGenerator;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->fakeGenerator = new FakeAiCoverLetterService();
        $this->app->instance(AiCoverLetterContract::class, $this->fakeGenerator);
    }

    public function test_unauthenticated_user_gets_redirected(): void
    {
        $user = User::factory()->create();
        $job = Job::factory()->for($user)->create();

        $response = $this->post(route('ai.cover-letter', $job));

        $response->assertRedirect(route('login'));
    }

    public function test_another_users_job_returns_403(): void
    {
        $user = User::factory()->premium()->create();
        $otherUser = User::factory()->create();
        $job = Job::factory()->for($otherUser)->create();

        $response = $this->actingAs($user)->post(route('ai.cover-letter', $job));

        $response->assertForbidden();
    }

    public function test_non_premium_user_gets_403(): void
    {
        $user = User::factory()->create(['is_premium' => false]);
        $job = Job::factory()->for($user)->create();

        $response = $this->actingAs($user)->post(route('ai.cover-letter', $job));

        $response->assertForbidden();
    }

    public function test_premium_user_can_generate_cover_letter(): void
    {
        Storage::fake('local');

        $user = User::factory()->premium()->create([
            'about_me' => 'Опытный разработчик с 10-летним стажем',
        ]);
        $job = Job::factory()->for($user)->create([
            'company_name' => 'ООО Тест',
            'location_city' => 'Берлин',
            'description' => 'Требуется Senior PHP Developer',
        ]);

        $texContent = '\documentclass{article}\begin{document}Cover Letter\end{document}';
        $this->fakeGenerator->withResponse($texContent);

        $response = $this->actingAs($user)->post(route('ai.cover-letter', $job));

        $response->assertRedirect();

        $this->assertDatabaseHas('job_documents', [
            'job_id' => $job->id,
            'user_id' => $user->id,
            'category' => 'cover_letter',
            'mime_type' => 'application/x-tex',
        ]);

        $document = $job->documents()->where('category', 'cover_letter')->first();
        $this->assertNotNull($document);
        $this->assertNotNull($document->file_path);
        Storage::disk('local')->assertExists($document->file_path);
    }

    public function test_service_failure_redirects_with_error(): void
    {
        $user = User::factory()->premium()->create();
        $job = Job::factory()->for($user)->create();

        $this->fakeGenerator->shouldFail();

        $response = $this->actingAs($user)->post(route('ai.cover-letter', $job));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_cover_letter_includes_contacts_in_prompt(): void
    {
        Storage::fake('local');

        $user = User::factory()->premium()->create([
            'about_me' => 'Full-stack Developer',
        ]);
        $job = Job::factory()->for($user)->create([
            'company_name' => 'ООО Тест',
            'location_city' => 'Мюнхен',
        ]);
        $job->contacts()->create([
            'user_id' => $user->id,
            'first_name' => 'Анна',
            'last_name' => 'Иванова',
            'position' => 'HR-менеджер',
            'email' => 'anna@example.com',
        ]);

        $response = $this->actingAs($user)->post(route('ai.cover-letter', $job));

        $response->assertRedirect();

        $this->assertDatabaseHas('job_documents', [
            'job_id' => $job->id,
            'category' => 'cover_letter',
        ]);
    }
}
