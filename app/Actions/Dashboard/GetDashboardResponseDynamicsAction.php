<?php

declare(strict_types=1);

namespace App\Actions\Dashboard;

use App\Data\DashboardResponsePointData;
use App\Models\Job;
use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

final readonly class GetDashboardResponseDynamicsAction
{
    /**
     * @return list<DashboardResponsePointData>
     */
    public function execute(User $user, int $weeks = 12): array
    {
        $now = CarbonImmutable::now();
        $startDate = $now->startOfWeek()->subWeeks($weeks - 1);

        /** @var Collection<int, CarbonInterface> $dates */
        $dates = Job::query()
            ->where('user_id', $user->id)
            ->where('created_at', '>=', $startDate)
            ->pluck('created_at');

        // Группируем по ISO-неделям в PHP (совместимо с SQLite и MySQL)
        /** @var Collection<string, int> $grouped */
        $grouped = $dates->countBy(
            fn (CarbonInterface $date): string => $date->format('o-W')
        );

        $points = [];

        for ($i = 0; $i < $weeks; $i++) {
            $weekStart = $startDate->addWeeks($i);
            $key = $weekStart->format('o-W');
            $label = $weekStart->format('d.m');

            $points[] = new DashboardResponsePointData(
                label: $label,
                count: $grouped->get($key, 0),
            );
        }

        return $points;
    }
}
