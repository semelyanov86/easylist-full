<?php

declare(strict_types=1);

namespace Tests\Unit\Polza;

use App\Services\Polza\PolzaChatClient;
use App\Services\Polza\PolzaCompanyAnalyzerService;
use App\Services\Polza\PolzaContactFinderService;
use App\Services\Polza\PolzaCoverLetterService;
use App\Services\Polza\PolzaFormatterService;
use App\Services\Polza\PolzaTagExtractorService;
use App\Services\Polza\PromptRepository;
use App\Exceptions\AiFormatterException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PolzaServicesTest extends TestCase
{
    private PolzaChatClient $client;

    private PromptRepository $prompts;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->client = new PolzaChatClient('key', 'https://polza.ai/api/v1', 30);
        $this->prompts = new PromptRepository();
    }

    public function test_formatter_returns_trimmed_markdown(): void
    {
        $this->fakeContent("  # Заголовок\n\nТекст  ");

        $service = new PolzaFormatterService($this->client, $this->prompts);

        $this->assertSame("# Заголовок\n\nТекст", $service->format('сырой текст'));
    }

    public function test_tag_extractor_parses_tags(): void
    {
        $this->fakeContent('{"tags": ["PHP", "Laravel", "Vue.js"]}');

        $service = new PolzaTagExtractorService($this->client, $this->prompts);

        $tags = $service->extract([
            'title' => 'Backend Developer',
            'company_name' => 'Acme',
            'description' => 'PHP, Laravel',
            'existing_tags' => ['PHP'],
        ]);

        $this->assertSame(['PHP', 'Laravel', 'Vue.js'], $tags);

        // Контекст вакансии должен попасть в пользовательское сообщение запроса.
        Http::assertSent(function (Request $request): bool {
            $content = data_get($request->data(), 'messages.1.content');
            $user = is_string($content) ? $content : '';

            return str_contains($user, 'Backend Developer')
                && str_contains($user, 'Acme')
                && str_contains($user, 'PHP');
        });
    }

    public function test_tag_extractor_throws_on_unparseable_json(): void
    {
        $this->fakeContent('извините, не могу выполнить запрос');

        $service = new PolzaTagExtractorService($this->client, $this->prompts);

        $this->expectException(AiFormatterException::class);

        $service->extract([
            'title' => 't',
            'company_name' => 'c',
            'description' => null,
            'existing_tags' => [],
        ]);
    }

    public function test_tag_extractor_returns_empty_on_missing_tags(): void
    {
        $this->fakeContent('{"other": true}');

        $service = new PolzaTagExtractorService($this->client, $this->prompts);

        $this->assertSame([], $service->extract([
            'title' => 't',
            'company_name' => 'c',
            'description' => null,
            'existing_tags' => [],
        ]));
    }

    public function test_company_analyzer_returns_decoded_object(): void
    {
        $this->fakeContent('{"overview": "Делает софт", "industry": "IT", "tech_stack": ["PHP"]}');

        $service = new PolzaCompanyAnalyzerService($this->client, $this->prompts);

        $result = $service->analyze('Acme', 'Berlin');

        $this->assertSame('Делает софт', $result['overview']);
        $this->assertSame(['PHP'], $result['tech_stack']);
    }

    public function test_contact_finder_parses_wrapped_contacts(): void
    {
        $this->fakeContent('{"contacts": [{"first_name": "Anna", "last_name": "Müller", "position": "Recruiter"}]}');

        $service = new PolzaContactFinderService($this->client, $this->prompts);

        $contacts = $service->find('Acme', 'Berlin');

        $this->assertCount(1, $contacts);
        $this->assertSame('Anna', data_get($contacts, '0.first_name'));
    }

    public function test_contact_finder_parses_bare_array(): void
    {
        $this->fakeContent('[{"first_name": "Bob"}]');

        $service = new PolzaContactFinderService($this->client, $this->prompts);

        $contacts = $service->find('Acme', null);

        $this->assertSame('Bob', data_get($contacts, '0.first_name'));
    }

    public function test_cover_letter_returns_latex_and_strips_command(): void
    {
        $this->fakeContent("```latex\n\\documentclass{article}\\begin{document}Hi\\end{document}\n```");

        $service = new PolzaCoverLetterService($this->client, $this->prompts);

        $tex = $service->generate('/cover-letter-generator Обо мне...');

        $this->assertStringStartsWith('\documentclass', $tex);
        $this->assertStringContainsString('\end{document}', $tex);
        $this->assertStringNotContainsString('```', $tex);
    }

    public function test_cover_letter_throws_when_no_documentclass(): void
    {
        $this->fakeContent('Извините, я не смог сгенерировать письмо.');

        $service = new PolzaCoverLetterService($this->client, $this->prompts);

        $this->expectException(AiFormatterException::class);

        $service->generate('/cover-letter-generator Обо мне...');
    }

    /**
     * Захелпить ответ polza с заданным content.
     */
    private function fakeContent(string $content): void
    {
        Http::fake([
            'polza.ai/*' => Http::response([
                'choices' => [['message' => ['content' => $content]]],
            ]),
        ]);
    }
}
