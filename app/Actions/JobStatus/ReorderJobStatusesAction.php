<?php

declare(strict_types=1);

namespace App\Actions\JobStatus;

use App\Models\JobStatus;
use App\Models\User;

final readonly class ReorderJobStatusesAction
{
    /**
     * @param  array<int, int>  $ids
     */
    public function execute(User $user, array $ids): void
    {
        // Фильтруем только id, принадлежащие пользователю
        $ownIds = $user->jobStatuses()->pluck('id')->toArray();
        $validIds = array_values(array_filter($ids, fn (int $id): bool => in_array($id, $ownIds, true)));

        JobStatus::setNewOrder($validIds);
    }
}
