<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;

final class ContactData extends Data
{
    public function __construct(
        public readonly int $id,
        public readonly int $user_id,
        public readonly string $first_name,
        public readonly string $last_name,
        public readonly ?string $position,
        public readonly ?string $city,
        public readonly ?string $email,
        public readonly ?string $phone,
        public readonly ?string $description,
        public readonly ?string $linkedin_url,
        public readonly ?string $facebook_url,
        public readonly ?string $whatsapp_url,
        public readonly string $created_at,
    ) {}
}
