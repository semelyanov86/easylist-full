<?php

declare(strict_types=1);

namespace App\Actions\ShoppingItem;

use App\Models\ShoppingList;

final readonly class DeleteAllItemsAction
{
    public function execute(ShoppingList $shoppingList): void
    {
        $shoppingList->items()->delete();
    }
}
