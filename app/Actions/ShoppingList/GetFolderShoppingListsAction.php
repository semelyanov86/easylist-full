<?php

declare(strict_types=1);

namespace App\Actions\ShoppingList;

use App\Data\ShoppingListData;
use App\Models\Folder;
use Illuminate\Support\Collection;

final readonly class GetFolderShoppingListsAction
{
    public function __construct(
        private GetShoppingListDataAction $getShoppingListData,
    ) {}

    /**
     * Получить все списки из папки.
     *
     * @return Collection<int, ShoppingListData>
     */
    public function execute(Folder $folder): Collection
    {
        return $folder->lists()
            ->ordered()
            ->get()
            ->map(fn ($list): ShoppingListData => $this->getShoppingListData->execute($list));
    }
}
