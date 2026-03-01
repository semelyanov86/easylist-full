<?php

declare(strict_types=1);

namespace App\Actions\Skill;

use App\Models\Job;
use App\Models\User;

final readonly class SyncJobSkillsAction
{
    /**
     * Синхронизирует навыки вакансии, фильтруя только навыки текущего пользователя.
     *
     * @param  list<int>  $skillIds
     */
    public function execute(User $user, Job $job, array $skillIds): void
    {
        $validSkillIds = $user->skills()
            ->whereIn('id', $skillIds)
            ->pluck('id')
            ->all();

        $job->skills()->sync($validSkillIds);
    }
}
