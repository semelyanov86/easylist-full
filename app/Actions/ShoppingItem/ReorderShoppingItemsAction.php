<?php

declare(strict_types=1);

namespace App\Actions\ShoppingItem;

use App\Models\ShoppingItem;
use App\Models\User;

final readonly class ReorderShoppingItemsAction
{
    /**
     * @param  array<int, int>  $ids
     */
    public function execute(User $user, array $ids): void
    {
        $ownIds = $user->shoppingItems()->pluck('id')->toArray();
        $validIds = array_values(array_filter($ids, fn (int $id): bool => in_array($id, $ownIds, true)));

        ShoppingItem::setNewOrder($validIds);
    }
}
