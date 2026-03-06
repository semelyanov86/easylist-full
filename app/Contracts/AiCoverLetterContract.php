<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Exceptions\AiFormatterException;

interface AiCoverLetterContract
{
    /**
     * Сгенерировать cover letter и вернуть содержимое .tex файла.
     *
     * @throws AiFormatterException
     */
    public function generate(string $prompt): string;
}
