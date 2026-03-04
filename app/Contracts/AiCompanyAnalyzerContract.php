<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Exceptions\AiFormatterException;

interface AiCompanyAnalyzerContract
{
    /**
     * Проанализировать компанию по названию и городу.
     *
     * @return array<string, mixed>
     *
     * @throws AiFormatterException
     */
    public function analyze(string $companyName, ?string $city): array;
}
