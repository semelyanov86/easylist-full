<?php

declare(strict_types=1);

namespace App\Actions\Job;

use App\Data\JobIndexFiltersData;
use App\Data\JobListItemData;
use App\Models\Job;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Pagination\LengthAwarePaginator;

final readonly class GetUserJobsQuery
{
    /**
     * @return LengthAwarePaginator<int, JobListItemData>
     */
    public function execute(User $user, JobIndexFiltersData $filters, int $perPage = 15): LengthAwarePaginator
    {
        /** @var LengthAwarePaginator<int, JobListItemData> */
        return JobListItemData::collect(
            $this->buildQuery($user, $filters)->paginate($perPage)->withQueryString(),
        );
    }

    /**
     * Пагинированные модели с дополнительным eager loading (для API с include).
     *
     * @param  list<string>  $with
     * @return LengthAwarePaginator<int, Job>
     */
    public function executeWithModels(
        User $user,
        JobIndexFiltersData $filters,
        array $with = [],
        int $perPage = 15,
    ): LengthAwarePaginator {
        $query = $this->buildQuery($user, $filters);

        if ($with !== []) {
            $query->with($with);
        }

        /** @var LengthAwarePaginator<int, Job> */
        return $query->paginate($perPage);
    }

    /**
     * @return HasMany<Job, User>
     */
    private function buildQuery(User $user, JobIndexFiltersData $filters): HasMany
    {
        $query = $user->jobs()
            ->with(['status', 'category', 'skills'])->latest('updated_at');

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

        if ($filters->is_favorite !== null) {
            $query->where('is_favorite', $filters->is_favorite);
        }

        if ($filters->date_from !== null && $filters->date_from !== '') {
            $query->whereDate('created_at', '>=', $filters->date_from);
        }

        if ($filters->date_to !== null && $filters->date_to !== '') {
            $query->whereDate('created_at', '<=', $filters->date_to);
        }

        return $query;
    }
}
