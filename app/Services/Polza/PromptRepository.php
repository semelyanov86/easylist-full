<?php

declare(strict_types=1);

namespace App\Services\Polza;

use App\Exceptions\AiFormatterException;

/**
 * Загрузчик системных промптов и шаблонов, перенесённых с skill-сервера
 * в приложение (resources/ai).
 *
 * Класс не readonly намеренно: $cache мемоизирует уже прочитанные промпты
 * на время жизни синглтона (в т.ч. под Octane), что безопасно — данные
 * иммутабельны и набор ограничен.
 */
final class PromptRepository
{
    /** @var array<string, string> */
    private array $cache = [];

    private readonly string $basePath;

    public function __construct(?string $basePath = null)
    {
        $this->basePath = $basePath ?? resource_path('ai');
    }

    /**
     * Получить системный промпт по имени задачи.
     *
     * @throws AiFormatterException
     */
    public function prompt(string $name): string
    {
        return $this->cache[$name] ??= $this->read("prompts/{$name}.md");
    }

    /**
     * Получить промпт cover letter вместе с LaTeX-шаблоном.
     *
     * @throws AiFormatterException
     */
    public function coverLetterPrompt(): string
    {
        return $this->cache['cover_letter_full'] ??= $this->prompt('cover-letter')
            . "\n\n## LaTeX-шаблон\n\n```latex\n"
            . $this->read('templates/cover-letter.tex')
            . "\n```\n";
    }

    /**
     * @throws AiFormatterException
     */
    private function read(string $relativePath): string
    {
        $path = "{$this->basePath}/{$relativePath}";

        if (! is_file($path)) {
            throw AiFormatterException::requestFailed(
                "Промпт не найден: {$relativePath}"
            );
        }

        $content = file_get_contents($path);

        if ($content === false || trim($content) === '') {
            throw AiFormatterException::requestFailed(
                "Пустой промпт: {$relativePath}"
            );
        }

        return $content;
    }
}
