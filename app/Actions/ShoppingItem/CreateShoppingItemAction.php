<?php

declare(strict_types=1);

namespace App\Actions\ShoppingItem;

use App\Models\ShoppingItem;
use App\Models\User;

final readonly class CreateShoppingItemAction
{
    /**
     * @param  array{shopping_list_id: int, name: string, description?: string|null, quantity?: int, quantity_type?: string|null, price?: int|null, is_starred?: bool, is_done?: bool, file?: string|null}  $data
     */
    public function execute(User $user, array $data): ShoppingItem
    {
        $listBelongsToUser = $user->shoppingLists()
            ->where('id', $data['shopping_list_id'])
            ->exists();

        abort_if(! $listBelongsToUser, 403);

        /** @var ShoppingItem */
        return $user->shoppingItems()->create($data);
    }
}
