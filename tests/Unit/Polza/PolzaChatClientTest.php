<?php

declare(strict_types=1);

namespace Tests\Unit\Polza;

use App\Exceptions\AiFormatterException;
use App\Services\Polza\PolzaChatClient;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PolzaChatClientTest extends TestCase
{
    private PolzaChatClient $client;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->client = new PolzaChatClient(
            apiKey: 'test-key',
            baseUrl: 'https://polza.ai/api/v1',
            timeout: 30,
        );
    }

    public function test_returns_assistant_content(): void
    {
        Http::fake([
            'polza.ai/*' => Http::response([
                'choices' => [['message' => ['content' => 'Привет!']]],
                'usage' => ['prompt_tokens' => 10, 'completion_tokens' => 2, 'cost_rub' => 0.01],
            ]),
        ]);

        $content = $this->client->chat('openai/gpt-5-mini', 'system', 'user');

        $this->assertSame('Привет!', $content);

        Http::assertSent(function (Request $request): bool {
            $body = $request->data();

            return $request->url() === 'https://polza.ai/api/v1/chat/completions'
                && $request->hasHeader('Authorization', 'Bearer test-key')
                && data_get($body, 'model') === 'openai/gpt-5-mini'
                && data_get($body, 'messages.0.role') === 'system'
                && data_get($body, 'messages.1.content') === 'user'
                && data_get($body, 'plugins') === null;
        });
    }

    public function test_enables_web_search_plugin(): void
    {
        Http::fake([
            'polza.ai/*' => Http::response([
                'choices' => [['message' => ['content' => 'ok']]],
            ]),
        ]);

        $this->client->chat('x-ai/grok-4.3', 'system', 'user', webSearch: true);

        Http::assertSent(fn (Request $request): bool => $request['plugins'] === [['id' => 'web']]);
    }

    public function test_failed_response_throws(): void
    {
        Http::fake([
            'polza.ai/*' => Http::response(['error' => ['message' => 'Invalid model']], 400),
        ]);

        $this->expectException(AiFormatterException::class);
        $this->expectExceptionMessageMatches('/HTTP 400.*Invalid model/');

        $this->client->chat('bad/model', 'system', 'user');
    }

    public function test_connection_exception_throws(): void
    {
        Http::fake(function (): void {
            throw new ConnectionException('timed out');
        });

        $this->expectException(AiFormatterException::class);
        $this->expectExceptionMessageMatches('/Таймаут соединения/');

        $this->client->chat('openai/gpt-5-mini', 'system', 'user');
    }

    public function test_empty_content_throws(): void
    {
        Http::fake([
            'polza.ai/*' => Http::response([
                'choices' => [['message' => ['content' => '   ']]],
            ]),
        ]);

        $this->expectException(AiFormatterException::class);
        $this->expectExceptionMessageMatches('/Пустой ответ/');

        $this->client->chat('openai/gpt-5-mini', 'system', 'user');
    }
}
