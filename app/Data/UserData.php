<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;

/**
 * DTO авторизованного пользователя для API.
 */
final class UserData extends Data
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $email,
        public readonly bool $is_premium,
        public readonly ?string $about_me,
        public readonly string $created_at,
    ) {}
}
