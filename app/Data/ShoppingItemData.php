<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;

final class ShoppingItemData extends Data
{
    public function __construct(
        public readonly int $id,
        public readonly int $shopping_list_id,
        public readonly string $name,
        public readonly ?string $description,
        public readonly int $quantity,
        public readonly ?string $quantity_type,
        public readonly ?int $price,
        public readonly bool $is_starred,
        public readonly bool $is_done,
        public readonly ?string $file,
        public readonly int $order_column,
        public readonly ?string $created_at,
        public readonly ?string $updated_at,
    ) {}
}
