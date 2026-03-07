<?php

declare(strict_types=1);

namespace App\Actions\ShoppingItem;

use App\Data\ShoppingItemData;
use App\Models\ShoppingList;
use Illuminate\Support\Collection;

final readonly class GetListItemsAction
{
    public function __construct(
        private GetShoppingItemDataAction $getShoppingItemData,
    ) {}

    /**
     * Получить все позиции из списка.
     *
     * @return Collection<int, ShoppingItemData>
     */
    public function execute(ShoppingList $shoppingList): Collection
    {
        return $shoppingList->items()
            ->ordered()
            ->get()
            ->map(fn ($item): ShoppingItemData => $this->getShoppingItemData->execute($item));
    }
}
