<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Exceptions\AiFormatterException;

interface AiContactFinderContract
{
    /**
     * Найти контакты компании по названию и городу.
     *
     * @return array<int|string, mixed>
     *
     * @throws AiFormatterException
     */
    public function find(string $companyName, ?string $city): array;
}
