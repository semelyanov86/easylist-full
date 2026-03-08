<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\StatusColor;
use Spatie\LaravelData\Data;

final class DashboardJobItemData extends Data
{
    public function __construct(
        public readonly int $id,
        public readonly string $title,
        public readonly string $company_name,
        public readonly string $status_title,
        public readonly StatusColor $status_color,
        public readonly string $created_at,
    ) {}
}
