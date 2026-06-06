<?php

declare(strict_types=1);

namespace Tests\Unit\Polza;

use App\Exceptions\AiFormatterException;
use App\Services\Polza\PromptRepository;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PromptRepositoryTest extends TestCase
{
    /**
     * @return array<string, array{string}>
     */
    public static function promptProvider(): array
    {
        return [
            'format' => ['format'],
            'tags' => ['tags'],
            'company' => ['company'],
            'contacts' => ['contacts'],
            'cover-letter' => ['cover-letter'],
        ];
    }

    #[DataProvider('promptProvider')]
    public function test_loads_each_task_prompt(string $name): void
    {
        $repository = new PromptRepository();

        $this->assertNotSame('', trim($repository->prompt($name)));
    }

    public function test_cover_letter_prompt_includes_latex_template(): void
    {
        $repository = new PromptRepository();

        $prompt = $repository->coverLetterPrompt();

        $this->assertStringContainsString('documentclass', $prompt);
        $this->assertStringContainsString('Sergei Emelianov', $prompt);
    }

    public function test_throws_on_missing_prompt(): void
    {
        $repository = new PromptRepository();

        $this->expectException(AiFormatterException::class);

        $repository->prompt('nonexistent-prompt');
    }
}
