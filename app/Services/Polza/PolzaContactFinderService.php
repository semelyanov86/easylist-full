<?php

declare(strict_types=1);

namespace App\Services\Polza;

use App\Contracts\AiContactFinderContract;
use App\Support\JsonExtractor;

final readonly class PolzaContactFinderService implements AiContactFinderContract
{
    public function __construct(
        private PolzaChatClient $client,
        private PromptRepository $prompts,
    ) {}

    /**
     * {@inheritDoc}
     */
    public function find(string $companyName, ?string $city): array
    {
        /** @var array{model: string, web: bool, temperature: float} $task */
        $task = config('ai.polza.tasks.contacts');

        $location = $city !== null && $city !== '' ? ", {$city}" : '';

        $content = $this->client->chat(
            model: $task['model'],
            system: $this->prompts->prompt('contacts'),
            user: "Компания: {$companyName}{$location}",
            webSearch: $task['web'],
            temperature: $task['temperature'],
        );

        $decoded = JsonExtractor::decode($content);

        /** @var mixed $contacts */
        $contacts = $decoded['contacts'] ?? null;

        if (is_array($contacts)) {
            return array_values($contacts);
        }

        // Модель могла вернуть «голый» массив контактов без обёртки.
        if (array_is_list($decoded)) {
            return $decoded;
        }

        return [];
    }
}
