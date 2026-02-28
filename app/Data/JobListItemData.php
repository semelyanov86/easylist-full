<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;

final class JobListItemData extends Data
{
    public function __construct(
        public readonly int $id,
        public readonly string $title,
        public readonly string $company_name,
        public readonly ?string $location_city,
        public readonly ?int $salary,
        public readonly bool $is_favorite,
        public readonly string $created_at,
        public readonly JobStatusData $status,
        public readonly JobCategoryData $category,
    ) {}
}
