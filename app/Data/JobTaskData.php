<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;

final class JobTaskData extends Data
{
    public function __construct(
        public readonly int $id,
        public readonly int $user_id,
        public readonly string $title,
        public readonly ?string $external_id,
        public readonly ?string $deadline,
        public readonly ?string $completed_at,
        public readonly string $created_at,
    ) {}
}
