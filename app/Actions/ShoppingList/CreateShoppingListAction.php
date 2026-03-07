<?php

declare(strict_types=1);

namespace App\Actions\ShoppingList;

use App\Models\ShoppingList;
use App\Models\User;

final readonly class CreateShoppingListAction
{
    /**
     * @param  array{folder_id: int, name: string, icon?: string|null, is_public?: bool}  $data
     */
    public function execute(User $user, array $data): ShoppingList
    {
        $folderBelongsToUser = $user->folders()
            ->where('id', $data['folder_id'])
            ->exists();

        abort_if(! $folderBelongsToUser, 403);

        /** @var ShoppingList */
        return $user->shoppingLists()->create($data);
    }
}
