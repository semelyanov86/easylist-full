<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\Currency;
use Spatie\LaravelData\Data;

final class JobCategoryData extends Data
{
    public function __construct(
        public readonly int $id,
        public readonly string $title,
        public readonly ?string $description,
        public readonly Currency $currency,
        public readonly string $currency_symbol,
        public readonly int $order_column,
    ) {}
}
