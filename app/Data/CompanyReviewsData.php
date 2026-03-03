<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;

final class CompanyReviewsData extends Data
{
    /**
     * @param  list<string>|null  $pros
     * @param  list<string>|null  $cons
     */
    public function __construct(
        public readonly ?string $source,
        public readonly ?float $rating,
        public readonly ?int $total_reviews,
        public readonly ?array $pros,
        public readonly ?array $cons,
    ) {}
}
