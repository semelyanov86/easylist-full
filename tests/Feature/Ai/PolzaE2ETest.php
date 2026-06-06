<?php

declare(strict_types=1);

namespace Tests\Feature\Ai;

use App\Contracts\AiCompanyAnalyzerContract;
use App\Contracts\AiContactFinderContract;
use App\Contracts\AiCoverLetterContract;
use App\Contracts\AiFormatterContract;
use App\Contracts\AiTagExtractorContract;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

/**
 * Реальные e2e-тесты против polza.ai. Делают платные сетевые запросы.
 *
 * Запуск: task test:e2e  (POLZA_E2E_LIVE=1 php artisan test --group=e2e-polza)
 */
#[Group('e2e-polza')]
class PolzaE2ETest extends TestCase
{
    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        if (! filter_var(getenv('POLZA_E2E_LIVE'), FILTER_VALIDATE_BOOLEAN)) {
            $this->markTestSkipped('Установите POLZA_E2E_LIVE=1 для запуска реальных e2e-тестов polza.');
        }

        /** @var string $apiKey */
        $apiKey = config('ai.polza.api_key');

        if ($apiKey === '') {
            $this->markTestSkipped('POLZA_API_KEY не задан.');
        }

        config()->set('ai.provider', 'polza');
    }

    public function test_format_returns_markdown(): void
    {
        $formatted = $this->app->make(AiFormatterContract::class)
            ->format('разработчик php laravel опыт 5 лет docker kubernetes ищу работу в берлине');

        $this->assertNotSame('', trim($formatted));
        fwrite(STDERR, "\n[format]\n" . $formatted . "\n");
    }

    public function test_tags_extracts_tech_stack(): void
    {
        $tags = $this->app->make(AiTagExtractorContract::class)->extract([
            'title' => 'Senior Backend Developer',
            'company_name' => 'Acme GmbH',
            'description' => 'We need PHP, Laravel, PostgreSQL, Docker and AWS experience. Vue.js is a plus.',
            'existing_tags' => ['PHP', 'Laravel'],
        ]);

        $this->assertNotEmpty($tags);
        fwrite(STDERR, "\n[tags] " . implode(', ', $tags) . "\n");
    }

    public function test_company_analysis_with_web_search(): void
    {
        $info = $this->app->make(AiCompanyAnalyzerContract::class)->analyze('SAP', 'Walldorf');

        $this->assertArrayHasKey('overview', $info);

        $overview = $info['overview'] ?? null;
        $this->assertIsString($overview);
        $this->assertNotSame('', trim($overview));
        fwrite(STDERR, "\n[company] " . json_encode($info, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n");
    }

    public function test_contacts_search_with_web_search(): void
    {
        $contacts = $this->app->make(AiContactFinderContract::class)->find('SAP', 'Walldorf');

        $this->assertLessThanOrEqual(20, count($contacts));

        foreach ($contacts as $contact) {
            $this->assertIsArray($contact);
            if (isset($contact['linkedin_url']) && is_string($contact['linkedin_url']) && $contact['linkedin_url'] !== '') {
                $this->assertStringStartsWith('https://', $contact['linkedin_url']);
            }
        }

        fwrite(STDERR, "\n[contacts] найдено: " . count($contacts) . "\n"
            . json_encode($contacts, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n");
    }

    public function test_cover_letter_returns_compilable_latex(): void
    {
        $prompt = "/cover-letter-generator Я Sergei, backend-разработчик, PHP/Laravel, 6 лет опыта, Docker, Kubernetes.\n"
            . "Информация о вакансии\nSenior PHP Developer at Acme GmbH. Требуется Laravel, PostgreSQL, AWS. Berlin.\n"
            . 'Компания: Acme GmbH, Berlin';

        $tex = $this->app->make(AiCoverLetterContract::class)->generate($prompt);

        $this->assertStringContainsString('\documentclass', $tex);
        $this->assertStringContainsString('\end{document}', $tex);
        fwrite(STDERR, "\n[cover_letter] " . strlen($tex) . " байт LaTeX\n");
    }
}
