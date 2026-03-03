<?php

declare(strict_types=1);

namespace App\Actions\Ai;

use App\Contracts\AiFormatterContract;

final readonly class FormatTextAction
{
    public function __construct(
        private AiFormatterContract $formatter,
    ) {}

    /**
     * Отформатировать текст с помощью ИИ.
     */
    public function execute(string $text): string
    {
        return $this->formatter->format($text);
    }
}
