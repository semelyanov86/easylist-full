<?php

declare(strict_types=1);

namespace App\Actions\ShoppingItem;

use App\Models\ShoppingItem;

final readonly class DeleteShoppingItemAction
{
    public function execute(ShoppingItem $item): void
    {
        $item->delete();
    }
}
