<?php

declare(strict_types=1);

namespace App\Actions\JobCategory;

use App\Models\JobCategory;
use App\Models\User;

final readonly class ReorderJobCategoriesAction
{
    /**
     * @param  array<int, int>  $ids
     */
    public function execute(User $user, array $ids): void
    {
        // Фильтруем только id, принадлежащие пользователю
        $ownIds = $user->jobCategories()->pluck('id')->toArray();
        $validIds = array_values(array_filter($ids, fn (int $id): bool => in_array($id, $ownIds, true)));

        JobCategory::setNewOrder($validIds);
    }
}
