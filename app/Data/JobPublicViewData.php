<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;

/**
 * DTO для публичной страницы вакансии (без приватных данных).
 */
final class JobPublicViewData extends Data
{
    /**
     * @param  list<SkillData>  $skills
     * @param  list<PublicContactData>  $contacts
     */
    public function __construct(
        public readonly string $title,
        public readonly string $company_name,
        public readonly ?string $description,
        public readonly ?string $location_city,
        public readonly ?int $salary,
        public readonly ?string $job_url,
        public readonly ?string $currency_symbol,
        public readonly string $created_at,
        public readonly array $skills = [],
        public readonly array $contacts = [],
        public readonly ?CompanyInfoDetailsData $company_info = null,
    ) {}
}
