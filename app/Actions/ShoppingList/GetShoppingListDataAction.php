<?php

declare(strict_types=1);

namespace App\Actions\ShoppingList;

use App\Data\FolderData;
use App\Data\ShoppingItemData;
use App\Data\ShoppingListData;
use App\Models\ShoppingList;

final readonly class GetShoppingListDataAction
{
    /**
     * Преобразовать список в DTO.
     */
    public function execute(ShoppingList $shoppingList, bool $withItems = false, bool $withFolder = false): ShoppingListData
    {
        $items = null;
        $folder = null;

        if ($withItems && $shoppingList->relationLoaded('items')) {
            $items = array_values($shoppingList->items->map(
                fn ($item): ShoppingItemData => new ShoppingItemData(
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
                ),
            )->all());
        }

        if ($withFolder && $shoppingList->relationLoaded('folder') && $shoppingList->folder !== null) {
            $folder = new FolderData(
                id: $shoppingList->folder->id,
                name: $shoppingList->folder->name,
                icon: $shoppingList->folder->icon,
                order_column: $shoppingList->folder->order_column,
                created_at: $shoppingList->folder->created_at?->toISOString(),
                updated_at: $shoppingList->folder->updated_at?->toISOString(),
            );
        }

        return new ShoppingListData(
            id: $shoppingList->id,
            folder_id: $shoppingList->folder_id,
            name: $shoppingList->name,
            icon: $shoppingList->icon,
            link: $shoppingList->link,
            is_public: (bool) $shoppingList->is_public,
            order_column: $shoppingList->order_column,
            created_at: $shoppingList->created_at?->toISOString(),
            updated_at: $shoppingList->updated_at?->toISOString(),
            folder: $folder,
            items: $items,
        );
    }
}
