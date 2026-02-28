<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\StatusColor;
use Spatie\LaravelData\Data;

final class StatusTabData extends Data
{
    public function __construct(
        public readonly int $id,
        public readonly string $title,
        public readonly StatusColor $color,
        public readonly int $count,
    ) {}
}
