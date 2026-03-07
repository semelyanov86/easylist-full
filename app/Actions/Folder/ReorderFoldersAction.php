<?php

declare(strict_types=1);

namespace App\Actions\Folder;

use App\Models\Folder;
use App\Models\User;

final readonly class ReorderFoldersAction
{
    /**
     * @param  array<int, int>  $ids
     */
    public function execute(User $user, array $ids): void
    {
        $ownIds = $user->folders()->pluck('id')->toArray();
        $validIds = array_values(array_filter($ids, fn (int $id): bool => in_array($id, $ownIds, true)));

        Folder::setNewOrder($validIds);
    }
}
