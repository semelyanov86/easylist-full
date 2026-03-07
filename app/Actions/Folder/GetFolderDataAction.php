<?php

declare(strict_types=1);

namespace App\Actions\Folder;

use App\Data\FolderData;
use App\Data\ShoppingListData;
use App\Models\Folder;

final readonly class GetFolderDataAction
{
    /**
     * Преобразовать папку в DTO.
     */
    public function execute(Folder $folder, bool $withLists = false): FolderData
    {
        $lists = null;

        if ($withLists && $folder->relationLoaded('lists')) {
            $lists = array_values($folder->lists->map(
                fn ($list): ShoppingListData => new ShoppingListData(
                    id: $list->id,
                    folder_id: $list->folder_id,
                    name: $list->name,
                    icon: $list->icon,
                    link: $list->link,
                    is_public: (bool) $list->is_public,
                    order_column: $list->order_column,
                    created_at: $list->created_at?->toISOString(),
                    updated_at: $list->updated_at?->toISOString(),
                ),
            )->all());
        }

        return new FolderData(
            id: $folder->id,
            name: $folder->name,
            icon: $folder->icon,
            order_column: $folder->order_column,
            created_at: $folder->created_at?->toISOString(),
            updated_at: $folder->updated_at?->toISOString(),
            lists: $lists,
        );
    }
}
