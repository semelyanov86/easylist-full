<?php

declare(strict_types=1);

namespace App\Actions\Job;

use App\Data\JobIndexFiltersData;
use App\Data\JobListItemData;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

final readonly class GetUserJobsQuery
{
    /**
     * @return LengthAwarePaginator<int, JobListItemData>
     */
    public function execute(User $user, JobIndexFiltersData $filters): LengthAwarePaginator
    {
        $query = $user->jobs()
            ->with(['status', 'category'])->latest('updated_at');

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

        /** @var LengthAwarePaginator<int, JobListItemData> */
        return JobListItemData::collect(
            $query->paginate(15)->withQueryString(),
        );
    }
}
