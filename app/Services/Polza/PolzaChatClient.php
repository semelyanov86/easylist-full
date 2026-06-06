<?php

declare(strict_types=1);

namespace App\Services\Polza;

use App\Exceptions\AiFormatterException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Транспорт к polza.ai (OpenAI-совместимый Chat Completions API).
 *
 * Поддерживает веб-поиск (plugins:[{id:"web"}]) и строгий структурированный
 * вывод (response_format: json_schema). Возвращает текст ответа модели.
 */
final readonly class PolzaChatClient
{
    public function __construct(
        private string $apiKey,
        private string $baseUrl,
        private int $timeout,
    ) {}

    /**
     * Отправить запрос в чат-модель и вернуть содержимое ответа.
     *
     * Структурированный вывод не навязывается через response_format —
     * это сохраняет совместимость с любой моделью агрегатора (включая те,
     * что не поддерживают json_schema, например Perplexity). За формат JSON
     * отвечает промпт, а за разбор — App\Support\JsonExtractor.
     *
     * @throws AiFormatterException
     */
    public function chat(
        string $model,
        string $system,
        string $user,
        bool $webSearch = false,
        float $temperature = 0.3,
    ): string {
        $payload = [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user', 'content' => $user],
            ],
            'temperature' => $temperature,
        ];

        if ($webSearch) {
            $payload['plugins'] = [['id' => 'web']];
        }

        try {
            $response = Http::withToken($this->apiKey)
                ->timeout($this->timeout)
                ->acceptJson()
                ->asJson()
                ->post(rtrim($this->baseUrl, '/') . '/chat/completions', $payload);
        } catch (ConnectionException $e) {
            throw AiFormatterException::requestFailed(
                "Таймаут соединения с polza.ai: {$e->getMessage()}"
            );
        }

        if ($response->failed()) {
            /** @var string $error */
            $error = $response->json('error.message') ?? $response->body();

            throw AiFormatterException::requestFailed(
                "polza.ai HTTP {$response->status()}: {$error}"
            );
        }

        /** @var mixed $content */
        $content = $response->json('choices.0.message.content');

        if (! is_string($content) || trim($content) === '') {
            throw AiFormatterException::requestFailed(
                'Пустой ответ от модели polza.ai'
            );
        }

        $this->logUsage($model, $response->json('usage'));

        return $content;
    }

    /**
     * Записать расход токенов и стоимость запроса в debug-лог.
     */
    private function logUsage(string $model, mixed $usage): void
    {
        if (! is_array($usage)) {
            return;
        }

        Log::debug('polza.usage', [
            'model' => $model,
            'prompt_tokens' => $usage['prompt_tokens'] ?? null,
            'completion_tokens' => $usage['completion_tokens'] ?? null,
            'cost_rub' => $usage['cost_rub'] ?? null,
        ]);
    }
}
