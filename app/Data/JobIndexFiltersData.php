<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;

final class JobIndexFiltersData extends Data
{
    public function __construct(
        public readonly ?string $search = null,
        public readonly ?int $status_id = null,
        public readonly ?string $date_from = null,
        public readonly ?string $date_to = null,
        public readonly ?int $job_category_id = null,
        public readonly ?bool $is_favorite = null,
        public readonly ?string $sort = null,
    ) {}
}
