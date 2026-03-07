<?php

declare(strict_types=1);

namespace App\Actions\ShoppingItem;

use App\Models\ShoppingList;

final readonly class UncrossAllItemsAction
{
    public function execute(ShoppingList $shoppingList): void
    {
        $shoppingList->items()->update(['is_done' => false]);
    }
}
