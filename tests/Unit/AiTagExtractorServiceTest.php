<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Exceptions\AiFormatterException;
use App\Services\AiClientService;
use App\Services\AiTagExtractorService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use Illuminate\Http\Client\Request;

class AiTagExtractorServiceTest extends TestCase
{
    /** @var array{title: string, company_name: string, description: string|null, existing_tags: list<string>} */
    private array $context;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->context = [
            'title' => 'Senior PHP Developer',
            'company_name' => 'Acme Corp',
            'description' => 'We need a Laravel expert',
            'existing_tags' => ['PHP', 'JavaScript'],
        ];
    }

    public function test_successful_extraction_returns_tags(): void
    {
        Http::fake([
            'https://ask.sergeyem.ru/*' => Http::response([
                'result' => ['tags' => ['PHP', 'Laravel', 'Vue.js']],
            ]),
        ]);

        $client = new AiClientService(
            url: 'https://ask.sergeyem.ru/api/claude/json',
            token: 'test-token',
            timeout: 300,
        );
        $service = new AiTagExtractorService(client: $client);

        $tags = $service->extract($this->context);

        $this->assertSame(['PHP', 'Laravel', 'Vue.js'], $tags);

        Http::assertSentCount(1);
    }

    public function test_http_error_throws_exception(): void
    {
        Http::fake([
            'https://ask.sergeyem.ru/*' => Http::response('Server Error', 500),
        ]);

        $client = new AiClientService(
            url: 'https://ask.sergeyem.ru/api/claude/json',
            token: 'test-token',
            timeout: 300,
        );
        $service = new AiTagExtractorService(client: $client);

        $this->expectException(AiFormatterException::class);
        $this->expectExceptionMessage('HTTP 500');

        $service->extract($this->context);
    }

    public function test_missing_tags_returns_empty_array(): void
    {
        Http::fake([
            'https://ask.sergeyem.ru/*' => Http::response([
                'result' => ['data' => 'no tags here'],
            ]),
        ]);

        $client = new AiClientService(
            url: 'https://ask.sergeyem.ru/api/claude/json',
            token: 'test-token',
            timeout: 300,
        );
        $service = new AiTagExtractorService(client: $client);

        $tags = $service->extract($this->context);

        $this->assertSame([], $tags);
    }

    public function test_empty_tags_array_returns_empty_array(): void
    {
        Http::fake([
            'https://ask.sergeyem.ru/*' => Http::response([
                'result' => ['tags' => []],
            ]),
        ]);

        $client = new AiClientService(
            url: 'https://ask.sergeyem.ru/api/claude/json',
            token: 'test-token',
            timeout: 300,
        );
        $service = new AiTagExtractorService(client: $client);

        $tags = $service->extract($this->context);

        $this->assertSame([], $tags);
    }

    public function test_filters_out_empty_strings_from_tags(): void
    {
        Http::fake([
            'https://ask.sergeyem.ru/*' => Http::response([
                'result' => ['tags' => ['PHP', '', 'Laravel', '']],
            ]),
        ]);

        $client = new AiClientService(
            url: 'https://ask.sergeyem.ru/api/claude/json',
            token: 'test-token',
            timeout: 300,
        );
        $service = new AiTagExtractorService(client: $client);

        $tags = $service->extract($this->context);

        $this->assertSame(['PHP', 'Laravel'], $tags);
    }

    public function test_prompt_contains_job_context(): void
    {
        Http::fake([
            'https://ask.sergeyem.ru/*' => Http::response([
                'result' => ['tags' => ['PHP']],
            ]),
        ]);

        $client = new AiClientService(
            url: 'https://ask.sergeyem.ru/api/claude/json',
            token: 'test-token',
            timeout: 300,
        );
        $service = new AiTagExtractorService(client: $client);

        $service->extract($this->context);

        Http::assertSent(function (Request $request): bool {
            $body = $request->body();

            return str_contains($body, 'Senior PHP Developer')
                && str_contains($body, 'Acme Corp')
                && str_contains($body, 'Laravel expert')
                && str_contains($body, 'PHP, JavaScript');
        });
    }
}
