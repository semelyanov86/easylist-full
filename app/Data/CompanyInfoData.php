<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;

final class CompanyInfoData extends Data
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly ?string $city,
        public readonly ?CompanyInfoDetailsData $info,
    ) {}
}
