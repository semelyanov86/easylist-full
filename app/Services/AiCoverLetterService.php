<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\AiCoverLetterContract;
use App\Exceptions\AiFormatterException;
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

        /** @var string|null $fileUrl */
        $fileUrl = $response->json('url');

        if ($fileUrl === null || $fileUrl === '') {
            throw AiFormatterException::requestFailed(
                'Некорректный ответ: отсутствует url файла'
            );
        }

        $absoluteUrl = str_starts_with($fileUrl, 'http')
            ? $fileUrl
            : $this->baseUrl . $fileUrl;

        $fileResponse = Http::timeout($this->timeout)
            ->withToken($this->token)
            ->get($absoluteUrl);

        if ($fileResponse->failed()) {
            throw AiFormatterException::requestFailed(
                "Не удалось скачать файл: HTTP {$fileResponse->status()}"
            );
        }

        return $fileResponse->body();
    }
}
