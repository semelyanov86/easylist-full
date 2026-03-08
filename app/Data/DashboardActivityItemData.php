<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;

final class DashboardActivityItemData extends Data
{
    /**
     * @param  array<string, mixed>  $properties
     * @param  array<string, mixed>  $changes
     */
    public function __construct(
        public readonly int $id,
        public readonly string $description,
        public readonly ?string $event,
        public readonly ?string $causer_name,
        public readonly array $properties,
        public readonly array $changes,
        public readonly string $created_at,
        public readonly int $job_id,
        public readonly string $job_title,
        public readonly string $job_company_name,
    ) {}
}
