<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\AiFormatterException;
use Illuminate\Support\Facades\Http;

/**
 * Общий HTTP-транспорт для ИИ-сервисов.
 */
final readonly class AiClientService
{
    public function __construct(
        private string $url,
        private string $token,
        private int $timeout,
    ) {}

    /**
     * Отправить prompt в ИИ-сервис и вернуть массив result.
     *
     * @return array<string, mixed>
     *
     * @throws AiFormatterException
     */
    public function send(string $prompt): array
    {
        $response = Http::timeout($this->timeout)
            ->withToken($this->token)
            ->asMultipart()
            ->post($this->url, [
                ['name' => 'prompt', 'contents' => $prompt],
            ]);

        if ($response->failed()) {
            throw AiFormatterException::requestFailed(
                "HTTP {$response->status()}"
            );
        }

        /** @var array<string, mixed>|null $result */
        $result = $response->json('result');

        if (! is_array($result)) {
            throw AiFormatterException::requestFailed(
                'Некорректный ответ от сервиса'
            );
        }

        return $result;
    }
}
