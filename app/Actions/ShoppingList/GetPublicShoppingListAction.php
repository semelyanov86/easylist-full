<?php

declare(strict_types=1);

namespace App\Actions\ShoppingList;

use App\Data\ShoppingListData;
use App\Models\ShoppingList;

final readonly class GetPublicShoppingListAction
{
    public function __construct(
        private GetShoppingListDataAction $getShoppingListData,
    ) {}

    /**
     * Загрузить публичный список по UUID с позициями.
     */
    public function execute(string $uuid): ShoppingListData
    {
        $shoppingList = ShoppingList::query()
            ->where('link', $uuid)
            ->where('is_public', true)
            ->with('items')
            ->firstOrFail();

        return $this->getShoppingListData->execute($shoppingList, withItems: true);
    }
}
