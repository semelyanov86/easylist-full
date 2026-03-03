<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Exceptions\AiFormatterException;
use App\Services\AiClientService;
use App\Services\AiFormatterService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiFormatterServiceTest extends TestCase
{
    public function test_successful_format_returns_text(): void
    {
        Http::fake([
            'https://ask.sergeyem.ru/*' => Http::response([
                'result' => ['data' => '**Отформатированный текст**'],
            ]),
        ]);

        $client = new AiClientService(
            url: 'https://ask.sergeyem.ru/api/claude/json',
            token: 'test-token',
        );
        $service = new AiFormatterService(client: $client);

        $result = $service->format('Сырой текст');

        $this->assertSame('**Отформатированный текст**', $result);

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
        );
        $service = new AiFormatterService(client: $client);

        $this->expectException(AiFormatterException::class);
        $this->expectExceptionMessage('HTTP 500');

        $service->format('Текст');
    }

    public function test_empty_result_throws_exception(): void
    {
        Http::fake([
            'https://ask.sergeyem.ru/*' => Http::response([
                'result' => ['data' => ''],
            ]),
        ]);

        $client = new AiClientService(
            url: 'https://ask.sergeyem.ru/api/claude/json',
            token: 'test-token',
        );
        $service = new AiFormatterService(client: $client);

        $this->expectException(AiFormatterException::class);
        $this->expectExceptionMessage('Пустой ответ от сервиса');

        $service->format('Текст');
    }

    public function test_null_result_throws_exception(): void
    {
        Http::fake([
            'https://ask.sergeyem.ru/*' => Http::response([
                'result' => ['data' => null],
            ]),
        ]);

        $client = new AiClientService(
            url: 'https://ask.sergeyem.ru/api/claude/json',
            token: 'test-token',
        );
        $service = new AiFormatterService(client: $client);

        $this->expectException(AiFormatterException::class);
        $this->expectExceptionMessage('Пустой ответ от сервиса');

        $service->format('Текст');
    }

    public function test_missing_result_key_throws_exception(): void
    {
        Http::fake([
            'https://ask.sergeyem.ru/*' => Http::response([
                'something' => 'else',
            ]),
        ]);

        $client = new AiClientService(
            url: 'https://ask.sergeyem.ru/api/claude/json',
            token: 'test-token',
        );
        $service = new AiFormatterService(client: $client);

        $this->expectException(AiFormatterException::class);

        $service->format('Текст');
    }
}
