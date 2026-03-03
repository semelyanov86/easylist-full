<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;

final class CompanyInfoDetailsData extends Data
{
    /**
     * @param  list<string>|null  $tech_stack
     * @param  list<CompanyNewsItemData>|null  $recent_news
     */
    public function __construct(
        public readonly ?string $overview = null,
        public readonly ?string $industry = null,
        public readonly ?string $founded = null,
        public readonly ?string $employees = null,
        public readonly ?string $revenue = null,
        public readonly ?string $funding = null,
        public readonly ?string $hq = null,
        public readonly ?string $local_office = null,
        public readonly ?array $tech_stack = null,
        public readonly ?CompanyReviewsData $reviews = null,
        public readonly ?array $recent_news = null,
        public readonly ?CompanyLinksData $links = null,
    ) {}
}
