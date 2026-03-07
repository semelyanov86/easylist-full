<?php

declare(strict_types=1);

namespace App\Actions\ShoppingList;

use App\Data\ShoppingListData;
use App\Models\User;
use Illuminate\Support\Collection;

final readonly class GetUserShoppingListsAction
{
    public function __construct(
        private GetShoppingListDataAction $getShoppingListData,
    ) {}

    /**
     * Получить все списки пользователя.
     *
     * @return Collection<int, ShoppingListData>
     */
    public function execute(User $user, bool $withFolder = false, bool $withItems = false): Collection
    {
        $query = $user->shoppingLists()->ordered();

        if ($withFolder) {
            $query->with('folder');
        }

        if ($withItems) {
            $query->with('items');
        }

        return $query->get()->map(
            fn ($list): ShoppingListData => $this->getShoppingListData->execute($list, $withItems, $withFolder),
        );
    }
}
