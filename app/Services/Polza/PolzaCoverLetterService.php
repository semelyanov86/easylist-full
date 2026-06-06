<?php

declare(strict_types=1);

namespace App\Services\Polza;

use App\Contracts\AiCoverLetterContract;
use App\Exceptions\AiFormatterException;
use App\Support\JsonExtractor;

/**
 * Генерация cover letter через polza.ai.
 *
 * В отличие от skill-сервера, модель возвращает содержимое .tex напрямую,
 * без промежуточного файла.
 */
final readonly class PolzaCoverLetterService implements AiCoverLetterContract
{
    public function __construct(
        private PolzaChatClient $client,
        private PromptRepository $prompts,
    ) {}

    /**
     * {@inheritDoc}
     */
    public function generate(string $prompt): string
    {
        /** @var array{model: string, web: bool, temperature: float} $task */
        $task = config('ai.polza.tasks.cover_letter');

        $tex = JsonExtractor::stripFences($this->client->chat(
            model: $task['model'],
            system: $this->prompts->coverLetterPrompt(),
            user: $this->stripCommand($prompt),
            webSearch: $task['web'],
            temperature: $task['temperature'],
        ));

        if (! str_contains($tex, '\documentclass')) {
            throw AiFormatterException::requestFailed(
                'Модель вернула некорректный LaTeX (нет \documentclass)'
            );
        }

        return $tex;
    }

    /**
     * Убрать ведущую slash-команду «/cover-letter-generator », оставшуюся
     * от формата skill-сервера (claude-провайдер использует её для маршрутизации).
     */
    private function stripCommand(string $prompt): string
    {
        return preg_replace('/^\/cover-letter-generator\s+/', '', $prompt, 1) ?? $prompt;
    }
}
