<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;

final class ShoppingListData extends Data
{
    /**
     * @param  list<ShoppingItemData>|null  $items
     */
    public function __construct(
        public readonly int $id,
        public readonly ?int $folder_id,
        public readonly string $name,
        public readonly ?string $icon,
        public readonly ?string $link,
        public readonly bool $is_public,
        public readonly int $order_column,
        public readonly ?string $created_at,
        public readonly ?string $updated_at,
        public readonly ?FolderData $folder = null,
        public readonly ?array $items = null,
    ) {}
}
