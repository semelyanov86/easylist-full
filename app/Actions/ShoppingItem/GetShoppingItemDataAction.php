<?php

declare(strict_types=1);

namespace App\Actions\ShoppingItem;

use App\Data\ShoppingItemData;
use App\Models\ShoppingItem;

final readonly class GetShoppingItemDataAction
{
    /**
     * Преобразовать позицию в DTO.
     */
    public function execute(ShoppingItem $item): ShoppingItemData
    {
        return new ShoppingItemData(
            id: $item->id,
            shopping_list_id: $item->shopping_list_id,
            name: $item->name,
            description: $item->description,
            quantity: $item->quantity,
            quantity_type: $item->quantity_type,
            price: $item->price,
            is_starred: (bool) $item->is_starred,
            is_done: (bool) $item->is_done,
            file: $item->file,
            order_column: $item->order_column,
            created_at: $item->created_at?->toISOString(),
            updated_at: $item->updated_at?->toISOString(),
        );
    }
}
