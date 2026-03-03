<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Exceptions\AiFormatterException;

interface AiFormatterContract
{
    /**
     * Отформатировать текст с помощью ИИ.
     *
     * @throws AiFormatterException
     */
    public function format(string $text): string;
}
