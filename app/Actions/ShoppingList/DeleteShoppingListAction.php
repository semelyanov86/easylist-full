<?php

declare(strict_types=1);

namespace App\Actions\ShoppingList;

use App\Models\ShoppingList;

final readonly class DeleteShoppingListAction
{
    public function execute(ShoppingList $shoppingList): void
    {
        $shoppingList->delete();
    }
}
