<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;

final class CompanyLinksData extends Data
{
    public function __construct(
        public readonly ?string $website,
        public readonly ?string $glassdoor,
        public readonly ?string $kununu,
        public readonly ?string $linkedin,
    ) {}
}
