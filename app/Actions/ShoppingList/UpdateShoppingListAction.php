<?php

declare(strict_types=1);

namespace App\Actions\ShoppingList;

use App\Models\ShoppingList;
use App\Models\User;
use Illuminate\Support\Str;

final readonly class UpdateShoppingListAction
{
    /**
     * @param  array{name?: string, icon?: string|null, folder_id?: int, is_public?: bool, order_column?: int}  $data
     */
    public function execute(User $user, ShoppingList $shoppingList, array $data): void
    {
        if (isset($data['folder_id'])) {
            $folderBelongsToUser = $user->folders()
                ->where('id', $data['folder_id'])
                ->exists();

            abort_if(! $folderBelongsToUser, 403);
        }

        if (isset($data['is_public']) && $data['is_public'] && $shoppingList->link === null) {
            $data['link'] = Str::uuid()->toString();
        }

        if (isset($data['is_public']) && ! $data['is_public']) {
            $data['link'] = null;
        }

        $shoppingList->update($data);
    }
}
