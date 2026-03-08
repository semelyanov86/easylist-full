<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;

final class DashboardSkillDemandData extends Data
{
    public function __construct(
        public readonly int $id,
        public readonly string $title,
        public readonly int $jobs_count,
    ) {}
}
