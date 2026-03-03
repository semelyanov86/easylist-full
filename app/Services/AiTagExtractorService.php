<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\AiTagExtractorContract;

final readonly class AiTagExtractorService implements AiTagExtractorContract
{
    public function __construct(
        private AiClientService $client,
    ) {}

    /**
     * {@inheritDoc}
     */
    public function extract(array $context): array
    {
        $prompt = $this->buildPrompt($context);

        $result = $this->client->send($prompt);

        /** @var mixed $tags */
        $tags = $result['tags'] ?? null;

        if (! is_array($tags)) {
            return [];
        }

        /** @var list<string> */
        return array_values(array_filter($tags, fn (mixed $tag): bool => is_string($tag) && $tag !== ''));
    }

    /**
     * Сформировать prompt для извлечения тегов.
     *
     * @param  array{title: string, company_name: string, description: string|null, existing_tags: list<string>}  $context
     */
    private function buildPrompt(array $context): string
    {
        $existingTags = implode(', ', $context['existing_tags']);

        return "/job-tag-extractor ВАЖНО! Отвечай только в формате JSON, без лишнего текста. Изучи инструкцию по SKILL! \nTitle: {$context['title']}\nCompany: {$context['company_name']}\nDescription: {$context['description']}\nExisting tags: {$existingTags}";
    }
}
