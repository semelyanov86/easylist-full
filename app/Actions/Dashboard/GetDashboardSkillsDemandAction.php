<?php

declare(strict_types=1);

namespace App\Actions\Dashboard;

use App\Data\DashboardSkillDemandData;
use App\Models\Skill;
use App\Models\User;

final readonly class GetDashboardSkillsDemandAction
{
    /**
     * @return list<DashboardSkillDemandData>
     */
    public function execute(User $user, int $limit = 5): array
    {
        $skills = Skill::query()
            ->where('skills.user_id', $user->id)
            ->whereHas('jobs')
            ->withCount('jobs')
            ->orderByDesc('jobs_count')
            ->limit($limit)
            ->get();

        return array_values($skills->map(
            fn (Skill $skill): DashboardSkillDemandData => new DashboardSkillDemandData(
                id: $skill->id,
                title: $skill->title ?? '',
                jobs_count: (int) $skill->jobs_count,
            )
        )->all());
    }
}
