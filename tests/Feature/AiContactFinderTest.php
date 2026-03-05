<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Contracts\AiContactFinderContract;
use App\Models\Job;
use App\Models\User;
use App\Services\FakeAiContactFinderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiContactFinderTest extends TestCase
{
    use RefreshDatabase;

    private FakeAiContactFinderService $fakeFinder;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->fakeFinder = new FakeAiContactFinderService();
        $this->app->instance(AiContactFinderContract::class, $this->fakeFinder);
    }

    public function test_unauthenticated_user_gets_redirected(): void
    {
        $user = User::factory()->create();
        $job = Job::factory()->for($user)->create();

        $response = $this->post(route('ai.find-contacts', $job));

        $response->assertRedirect(route('login'));
    }

    public function test_another_users_job_returns_403(): void
    {
        $user = User::factory()->premium()->create();
        $otherUser = User::factory()->create();
        $job = Job::factory()->for($otherUser)->create();

        $response = $this->actingAs($user)->post(route('ai.find-contacts', $job));

        $response->assertForbidden();
    }

    public function test_non_premium_user_gets_403(): void
    {
        $user = User::factory()->create(['is_premium' => false]);
        $job = Job::factory()->for($user)->create();

        $response = $this->actingAs($user)->post(route('ai.find-contacts', $job));

        $response->assertForbidden();
    }

    public function test_premium_user_can_find_contacts(): void
    {
        $user = User::factory()->premium()->create();
        $job = Job::factory()->for($user)->create([
            'company_name' => 'ООО Тест',
            'location_city' => 'Берлин',
        ]);

        $this->fakeFinder->withResponse([
            [
                'first_name' => 'Анна',
                'last_name' => 'Иванова',
                'position' => 'HR-менеджер',
                'city' => 'Берлин',
                'email' => 'anna@example.com',
                'phone' => '+49 170 1234567',
                'description' => 'Рекрутер',
                'linkedin_url' => 'https://linkedin.com/in/anna',
                'whatsapp_url' => null,
            ],
            [
                'first_name' => 'Пётр',
                'last_name' => 'Смирнов',
                'position' => 'CTO',
                'city' => 'Берлин',
                'email' => 'peter@example.com',
                'phone' => null,
                'description' => null,
                'linkedin_url' => null,
                'whatsapp_url' => null,
            ],
        ]);

        $response = $this->actingAs($user)->post(route('ai.find-contacts', $job));

        $response->assertRedirect();

        $this->assertDatabaseHas('contacts', [
            'job_id' => $job->id,
            'user_id' => $user->id,
            'first_name' => 'Анна',
            'last_name' => 'Иванова',
            'position' => 'HR-менеджер',
            'email' => 'anna@example.com',
        ]);

        $this->assertDatabaseHas('contacts', [
            'job_id' => $job->id,
            'user_id' => $user->id,
            'first_name' => 'Пётр',
            'last_name' => 'Смирнов',
            'position' => 'CTO',
        ]);

        $this->assertSame(2, $job->contacts()->count());
    }

    public function test_service_failure_redirects_with_error(): void
    {
        $user = User::factory()->premium()->create();
        $job = Job::factory()->for($user)->create();

        $this->fakeFinder->shouldFail();

        $response = $this->actingAs($user)->post(route('ai.find-contacts', $job));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_find_contacts_with_null_city(): void
    {
        $user = User::factory()->premium()->create();
        $job = Job::factory()->for($user)->create([
            'company_name' => 'ООО БезГорода',
            'location_city' => null,
        ]);

        $this->fakeFinder->withResponse([
            [
                'first_name' => 'Мария',
                'last_name' => 'Петрова',
                'position' => 'Recruiter',
                'city' => null,
                'email' => 'maria@example.com',
                'phone' => null,
                'description' => null,
                'linkedin_url' => null,
                'whatsapp_url' => null,
            ],
        ]);

        $response = $this->actingAs($user)->post(route('ai.find-contacts', $job));

        $response->assertRedirect();

        $this->assertDatabaseHas('contacts', [
            'job_id' => $job->id,
            'first_name' => 'Мария',
            'last_name' => 'Петрова',
        ]);
    }
}
