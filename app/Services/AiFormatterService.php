<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\AiFormatterContract;
use App\Exceptions\AiFormatterException;
use Illuminate\Support\Facades\Http;

final readonly class AiFormatterService implements AiFormatterContract
{
    public function __construct(
        private string $url,
        private string $token,
    ) {}

    /**
     * {@inheritDoc}
     */
    public function format(string $text): string
    {
        $response = Http::timeout(120)
            ->withToken($this->token)
            ->asMultipart()
            ->post($this->url, [
                ['name' => 'prompt', 'contents' => '/format ' . $text],
            ]);

        if ($response->failed()) {
            throw AiFormatterException::requestFailed(
                "HTTP {$response->status()}"
            );
        }

        /** @var string|null $formatted */
        $formatted = $response->json('result.data');

        if ($formatted === null || $formatted === '') {
            throw AiFormatterException::requestFailed(
                'Пустой ответ от сервиса'
            );
        }

        return $formatted;
    }
}
