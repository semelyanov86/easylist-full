<?php

declare(strict_types=1);

namespace App\Actions\Dashboard;

use App\Data\DashboardActivityItemData;
use App\Models\Job;
use App\Models\User;
use Spatie\Activitylog\Models\Activity;

final readonly class GetDashboardActivityAction
{
    /**
     * @return list<DashboardActivityItemData>
     */
    public function execute(User $user, int $limit = 20): array
    {
        /** @var list<int> $jobIds */
        $jobIds = $user->jobs()->pluck('id')->all();

        if ($jobIds === []) {
            return [];
        }

        $activities = Activity::query()
            ->where('subject_type', Job::class)
            ->whereIn('subject_id', $jobIds)
            ->where('log_name', 'job')
            ->with(['causer:id,name', 'subject:id,title,company_name'])
            ->latest('id')
            ->limit($limit)
            ->get();

        return array_values($activities->map(
            function (Activity $activity): DashboardActivityItemData {
                $causer = $activity->causer;
                $causerName = $causer instanceof User ? $causer->name : '';

                /** @var Job $subject */
                $subject = $activity->subject;

                /** @var array<string, mixed> $properties */
                $properties = $activity->properties->toArray(); // @phpstan-ignore method.nonObject

                /** @var array<string, mixed> $changes */
                $changes = $activity->changes()->toArray();

                return new DashboardActivityItemData(
                    id: $activity->id,
                    description: $activity->description,
                    event: $activity->event,
                    causer_name: $causerName,
                    properties: $properties,
                    changes: $changes,
                    created_at: $activity->created_at?->toISOString() ?? '',
                    job_id: $subject->id,
                    job_title: $subject->title ?? '',
                    job_company_name: $subject->company_name ?? '',
                );
            }
        )->all());
    }
}
