<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Exceptions\AiFormatterException;

interface AiTagExtractorContract
{
    /**
     * Извлечь теги навыков из контекста вакансии.
     *
     * @param  array{title: string, company_name: string, description: string|null, existing_tags: list<string>}  $context
     * @return list<string>
     *
     * @throws AiFormatterException
     */
    public function extract(array $context): array;
}
