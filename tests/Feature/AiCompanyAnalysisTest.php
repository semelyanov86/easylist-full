<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Contracts\AiCompanyAnalyzerContract;
use App\Data\CompanyInfoDetailsData;
use App\Models\CompanyInfo;
use App\Models\Job;
use App\Models\User;
use App\Services\FakeAiCompanyAnalyzerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiCompanyAnalysisTest extends TestCase
{
    use RefreshDatabase;

    private FakeAiCompanyAnalyzerService $fakeAnalyzer;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->fakeAnalyzer = new FakeAiCompanyAnalyzerService();
        $this->app->instance(AiCompanyAnalyzerContract::class, $this->fakeAnalyzer);
    }

    public function test_unauthenticated_user_gets_redirected(): void
    {
        $user = User::factory()->create();
        $job = Job::factory()->for($user)->create();

        $response = $this->post(route('ai.company-analysis', $job));

        $response->assertRedirect(route('login'));
    }

    public function test_another_users_job_returns_403(): void
    {
        $user = User::factory()->premium()->create();
        $otherUser = User::factory()->create();
        $job = Job::factory()->for($otherUser)->create();

        $response = $this->actingAs($user)->post(route('ai.company-analysis', $job));

        $response->assertForbidden();
    }

    public function test_non_premium_user_gets_403(): void
    {
        $user = User::factory()->create(['is_premium' => false]);
        $job = Job::factory()->for($user)->create();

        $response = $this->actingAs($user)->post(route('ai.company-analysis', $job));

        $response->assertForbidden();
    }

    public function test_premium_user_can_analyze_company(): void
    {
        $user = User::factory()->premium()->create();
        $job = Job::factory()->for($user)->create([
            'company_name' => 'ООО Тест',
            'location_city' => 'Берлин',
        ]);

        $this->fakeAnalyzer->withResponse([
            'overview' => 'Отличная компания',
            'industry' => 'IT',
            'tech_stack' => ['PHP', 'Laravel'],
        ]);

        $response = $this->actingAs($user)->post(route('ai.company-analysis', $job));

        $response->assertRedirect();

        $this->assertDatabaseHas('company_infos', [
            'name' => 'ООО Тест',
            'city' => 'Берлин',
        ]);

        $companyInfo = CompanyInfo::where('name', 'ООО Тест')->firstOrFail();

        /** @var CompanyInfoDetailsData $info */
        $info = $companyInfo->info;
        $this->assertSame('Отличная компания', $info->overview);
    }

    public function test_existing_company_info_gets_updated(): void
    {
        $user = User::factory()->premium()->create();
        $job = Job::factory()->for($user)->create([
            'company_name' => 'ООО Тест',
            'location_city' => 'Берлин',
        ]);

        CompanyInfo::factory()->create([
            'name' => 'ООО Тест',
            'city' => 'Берлин',
            'info' => ['overview' => 'Старые данные'],
        ]);

        $this->fakeAnalyzer->withResponse([
            'overview' => 'Новые данные',
            'industry' => 'Fintech',
        ]);

        $response = $this->actingAs($user)->post(route('ai.company-analysis', $job));

        $response->assertRedirect();

        $this->assertSame(1, CompanyInfo::where('name', 'ООО Тест')->where('city', 'Берлин')->count());

        $companyInfo = CompanyInfo::where('name', 'ООО Тест')->firstOrFail();

        /** @var CompanyInfoDetailsData $info */
        $info = $companyInfo->info;
        $this->assertSame('Новые данные', $info->overview);
    }

    public function test_service_failure_redirects_with_error(): void
    {
        $user = User::factory()->premium()->create();
        $job = Job::factory()->for($user)->create();

        $this->fakeAnalyzer->shouldFail();

        $response = $this->actingAs($user)->post(route('ai.company-analysis', $job));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_analysis_with_null_city(): void
    {
        $user = User::factory()->premium()->create();
        $job = Job::factory()->for($user)->create([
            'company_name' => 'ООО БезГорода',
            'location_city' => null,
        ]);

        $this->fakeAnalyzer->withResponse([
            'overview' => 'Компания без города',
        ]);

        $response = $this->actingAs($user)->post(route('ai.company-analysis', $job));

        $response->assertRedirect();

        $this->assertDatabaseHas('company_infos', [
            'name' => 'ООО БезГорода',
            'city' => null,
        ]);
    }
}
