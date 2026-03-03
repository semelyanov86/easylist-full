<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;

final class CompanyNewsItemData extends Data
{
    public function __construct(
        public readonly ?string $title,
        public readonly ?string $date,
        public readonly ?string $url,
    ) {}
}
