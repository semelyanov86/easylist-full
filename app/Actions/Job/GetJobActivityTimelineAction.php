<?php

declare(strict_types=1);

namespace App\Actions\Job;

use App\Data\ActivityTimelineItemData;
use App\Models\Job;
use App\Models\User;
use Spatie\Activitylog\Models\Activity;

final readonly class GetJobActivityTimelineAction
{
    /**
     * @return list<ActivityTimelineItemData>
     */
    public function execute(Job $job): array
    {
        $activities = Activity::query()
            ->where('subject_type', Job::class)
            ->where('subject_id', $job->id)
            ->where('log_name', 'job')
            ->with('causer:id,name')
            ->latest('id')
            ->get();

        return array_values($activities->map(
            function (Activity $activity): ActivityTimelineItemData {
                $causer = $activity->causer;
                $causerName = $causer instanceof User ? $causer->name : '';

                /** @var array<string, mixed> $properties */
                $properties = $activity->properties->toArray(); // @phpstan-ignore method.nonObject

                /** @var array<string, mixed> $changes */
                $changes = $activity->changes()->toArray();

                return new ActivityTimelineItemData(
                    id: $activity->id,
                    description: $activity->description,
                    event: $activity->event,
                    causer_name: $causerName,
                    properties: $properties,
                    changes: $changes,
                    created_at: $activity->created_at?->toISOString() ?? '',
                );
            }
        )->all());
    }
}
