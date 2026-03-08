<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;

final class DashboardResponsePointData extends Data
{
    public function __construct(
        public readonly string $label,
        public readonly int $count,
    ) {}
}
