<?php

declare(strict_types=1);

namespace App\Services\Polza;

use App\Contracts\AiTagExtractorContract;
use App\Support\JsonExtractor;

final readonly class PolzaTagExtractorService implements AiTagExtractorContract
{
    public function __construct(
        private PolzaChatClient $client,
        private PromptRepository $prompts,
    ) {}

    /**
     * {@inheritDoc}
     */
    public function extract(array $context): array
    {
        /** @var array{model: string, web: bool, temperature: float} $task */
        $task = config('ai.polza.tasks.tags');

        $content = $this->client->chat(
            model: $task['model'],
            system: $this->prompts->prompt('tags'),
            user: $this->buildUser($context),
            webSearch: $task['web'],
            temperature: $task['temperature'],
        );

        $decoded = JsonExtractor::decode($content);

        /** @var mixed $tags */
        $tags = $decoded['tags'] ?? null;

        if (! is_array($tags)) {
            return [];
        }

        /** @var list<string> */
        return array_values(array_filter($tags, fn (mixed $tag): bool => is_string($tag) && $tag !== ''));
    }

    /**
     * Сформировать пользовательское сообщение из контекста вакансии.
     *
     * @param  array{title: string, company_name: string, description: string|null, existing_tags: list<string>}  $context
     */
    private function buildUser(array $context): string
    {
        $existingTags = implode(', ', $context['existing_tags']);

        return "Title: {$context['title']}\nCompany: {$context['company_name']}\nDescription: {$context['description']}\nExisting tags: {$existingTags}";
    }
}
