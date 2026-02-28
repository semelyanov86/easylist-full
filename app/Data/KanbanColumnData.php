<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\StatusColor;
use Spatie\LaravelData\Data;

final class KanbanColumnData extends Data
{
    /**
     * @param  list<JobListItemData>  $jobs
     */
    public function __construct(
        public readonly int $statusId,
        public readonly string $title,
        public readonly StatusColor $color,
        public readonly array $jobs,
    ) {}
}
