<?php

declare(strict_types=1);

namespace App\Actions\ShoppingItem;

use App\Data\ShoppingItemData;
use App\Models\User;
use Illuminate\Support\Collection;

final readonly class GetUserItemsAction
{
    public function __construct(
        private GetShoppingItemDataAction $getShoppingItemData,
    ) {}

    /**
     * Получить все позиции пользователя.
     *
     * @return Collection<int, ShoppingItemData>
     */
    public function execute(User $user): Collection
    {
        return $user->shoppingItems()
            ->ordered()
            ->get()
            ->map(fn ($item): ShoppingItemData => $this->getShoppingItemData->execute($item));
    }
}
