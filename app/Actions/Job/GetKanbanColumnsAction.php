<?php

declare(strict_types=1);

namespace App\Actions\Job;

use App\Data\JobIndexFiltersData;
use App\Data\JobListItemData;
use App\Data\KanbanColumnData;
use App\Models\JobStatus;
use App\Models\User;
use Illuminate\Support\Collection;

final readonly class GetKanbanColumnsAction
{
    private const int MAX_JOBS = 200;

    /**
     * @return Collection<int, KanbanColumnData>
     */
    public function execute(User $user, JobIndexFiltersData $filters): Collection
    {
        $statuses = $user->jobStatuses()->ordered()->get();

        $query = $user->jobs()->with(['status', 'category'])->latest('updated_at');

        if ($filters->search !== null && $filters->search !== '') {
            $search = '%' . $filters->search . '%';
            $query->where(function ($q) use ($search): void {
                $q->where('title', 'like', $search)
                    ->orWhere('company_name', 'like', $search)
                    ->orWhere('location_city', 'like', $search);
            });
        }

        if ($filters->status_id !== null) {
            $query->where('job_status_id', $filters->status_id);
        }

        if ($filters->job_category_id !== null) {
            $query->where('job_category_id', $filters->job_category_id);
        }

        if ($filters->date_from !== null && $filters->date_from !== '') {
            $query->whereDate('created_at', '>=', $filters->date_from);
        }

        if ($filters->date_to !== null && $filters->date_to !== '') {
            $query->whereDate('created_at', '<=', $filters->date_to);
        }

        $allJobs = $query->limit(self::MAX_JOBS)->get();
        $grouped = $allJobs->groupBy('job_status_id');

        /** @var Collection<int, KanbanColumnData> */
        return $statuses->map(function (JobStatus $status) use ($grouped): KanbanColumnData {
            /** @var \App\Enums\StatusColor $color */
            $color = $status->color;

            $jobs = $grouped->get($status->id, collect());

            return new KanbanColumnData(
                statusId: $status->id,
                title: $status->title,
                color: $color,
                jobs: array_values(JobListItemData::collect($jobs)->all()),
            );
        });
    }
}
