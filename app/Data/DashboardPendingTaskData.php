<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;

final class DashboardPendingTaskData extends Data
{
    public function __construct(
        public readonly int $id,
        public readonly string $title,
        public readonly ?string $deadline,
        public readonly int $job_id,
        public readonly string $job_title,
        public readonly string $job_company_name,
    ) {}
}
