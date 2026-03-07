<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;

final class FolderData extends Data
{
    /**
     * @param  list<ShoppingListData>|null  $lists
     */
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly ?string $icon,
        public readonly int $order_column,
        public readonly ?string $created_at,
        public readonly ?string $updated_at,
        public readonly ?array $lists = null,
    ) {}
}
