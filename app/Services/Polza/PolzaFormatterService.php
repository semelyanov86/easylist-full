<?php

declare(strict_types=1);

namespace App\Services\Polza;

use App\Contracts\AiFormatterContract;
use App\Exceptions\AiFormatterException;

final readonly class PolzaFormatterService implements AiFormatterContract
{
    public function __construct(
        private PolzaChatClient $client,
        private PromptRepository $prompts,
    ) {}

    /**
     * {@inheritDoc}
     */
    public function format(string $text): string
    {
        /** @var array{model: string, web: bool, temperature: float} $task */
        $task = config('ai.polza.tasks.format');

        $formatted = trim($this->client->chat(
            model: $task['model'],
            system: $this->prompts->prompt('format'),
            user: $text,
            webSearch: $task['web'],
            temperature: $task['temperature'],
        ));

        if ($formatted === '') {
            throw AiFormatterException::requestFailed('Пустой ответ от сервиса');
        }

        return $formatted;
    }
}
