<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;

/**
 * Урезанный контакт для публичной страницы вакансии.
 */
final class PublicContactData extends Data
{
    public function __construct(
        public readonly string $first_name,
        public readonly string $last_name,
        public readonly ?string $position,
        public readonly ?string $city,
        public readonly ?string $email,
        public readonly ?string $phone,
        public readonly ?string $linkedin_url,
    ) {}
}
