<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;

final class JobCommentData extends Data
{
    public function __construct(
        public readonly int $id,
        public readonly string $body,
        public readonly string $author_name,
        public readonly int $user_id,
        public readonly string $created_at,
    ) {}
}
