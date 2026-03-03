<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\AiFormatterContract;
use App\Exceptions\AiFormatterException;

final readonly class AiFormatterService implements AiFormatterContract
{
    public function __construct(
        private AiClientService $client,
    ) {}

    /**
     * {@inheritDoc}
     */
    public function format(string $text): string
    {
        $result = $this->client->send('/format ' . $text);

        /** @var string|null $formatted */
        $formatted = $result['data'] ?? null;

        if ($formatted === null || $formatted === '') {
            throw AiFormatterException::requestFailed(
                'Пустой ответ от сервиса'
            );
        }

        return $formatted;
    }
}
