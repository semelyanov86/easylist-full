<?php

declare(strict_types=1);

namespace App\Services\Polza;

use App\Contracts\AiCompanyAnalyzerContract;
use App\Support\JsonExtractor;

final readonly class PolzaCompanyAnalyzerService implements AiCompanyAnalyzerContract
{
    public function __construct(
        private PolzaChatClient $client,
        private PromptRepository $prompts,
    ) {}

    /**
     * {@inheritDoc}
     */
    public function analyze(string $companyName, ?string $city): array
    {
        /** @var array{model: string, web: bool, temperature: float} $task */
        $task = config('ai.polza.tasks.company');

        $location = $city !== null && $city !== '' ? ", {$city}" : '';

        $content = $this->client->chat(
            model: $task['model'],
            system: $this->prompts->prompt('company'),
            user: "Компания: {$companyName}{$location}",
            webSearch: $task['web'],
            temperature: $task['temperature'],
        );

        /** @var array<string, mixed> $decoded */
        $decoded = JsonExtractor::decode($content);

        return $decoded;
    }
}
