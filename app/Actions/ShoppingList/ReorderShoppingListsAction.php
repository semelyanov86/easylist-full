<?php

declare(strict_types=1);

namespace App\Actions\ShoppingList;

use App\Models\ShoppingList;
use App\Models\User;

final readonly class ReorderShoppingListsAction
{
    /**
     * @param  array<int, int>  $ids
     */
    public function execute(User $user, array $ids): void
    {
        $ownIds = $user->shoppingLists()->pluck('id')->toArray();
        $validIds = array_values(array_filter($ids, fn (int $id): bool => in_array($id, $ownIds, true)));

        ShoppingList::setNewOrder($validIds);
    }
}
