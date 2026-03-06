<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\AiCoverLetterContract;
use App\Exceptions\AiFormatterException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

/**
 * Генерация cover letter через внешний ИИ-сервис.
 *
 * Отправляет prompt, получает URL на .tex файл, скачивает и возвращает содержимое.
 */
final readonly class AiCoverLetterService implements AiCoverLetterContract
{
    public function __construct(
        private string $url,
        private string $token,
        private int $timeout,
        private string $baseUrl,
    ) {}

    /**
     * {@inheritDoc}
     */
    public function generate(string $prompt): string
    {
        try {
            $response = Http::timeout($this->timeout)
                ->withToken($this->token)
                ->asMultipart()
                ->post($this->url, [
                    ['name' => 'prompt', 'contents' => $prompt],
                ]);
        } catch (ConnectionException $e) {
            throw AiFormatterException::requestFailed(
                "Таймаут соединения: {$e->getMessage()}"
            );
        }

        if ($response->failed()) {
            throw AiFormatterException::requestFailed(
                "HTTP {$response->status()}"
            );
        }

        /** @var string|null $fileUrl */
        $fileUrl = $response->json('result.url');

        if ($fileUrl === null || $fileUrl === '') {
            throw AiFormatterException::requestFailed(
                'Некорректный ответ: отсутствует url файла'
            );
        }

        $absoluteUrl = str_starts_with($fileUrl, 'http')
            ? $fileUrl
            : $this->baseUrl . $fileUrl;

        try {
            $fileResponse = Http::timeout($this->timeout)
                ->withToken($this->token)
                ->get($absoluteUrl);
        } catch (ConnectionException $e) {
            throw AiFormatterException::requestFailed(
                "Таймаут скачивания файла: {$e->getMessage()}"
            );
        }

        if ($fileResponse->failed()) {
            throw AiFormatterException::requestFailed(
                "Не удалось скачать файл: HTTP {$fileResponse->status()}"
            );
        }

        return $fileResponse->body();
    }
}
