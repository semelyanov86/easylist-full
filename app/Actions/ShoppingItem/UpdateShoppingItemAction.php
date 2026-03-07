<?php

declare(strict_types=1);

namespace App\Actions\ShoppingItem;

use App\Models\ShoppingItem;

final readonly class UpdateShoppingItemAction
{
    /**
     * @param  array{name?: string, description?: string|null, quantity?: int, quantity_type?: string|null, price?: int|null, is_starred?: bool, is_done?: bool, file?: string|null, order_column?: int}  $data
     */
    public function execute(ShoppingItem $item, array $data): void
    {
        $item->update($data);
    }
}
