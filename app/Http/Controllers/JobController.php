<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Job\GetJobStatusTabsAction;
use App\Actions\Job\GetUserJobsQuery;
use App\Actions\JobCategory\GetUserJobCategoriesAction;
use App\Data\JobIndexFiltersData;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class JobController extends Controller
{
    /**
     * Показать список вакансий пользователя.
     */
    public function index(
        Request $request,
        GetUserJobsQuery $getJobs,
        GetJobStatusTabsAction $getStatusTabs,
        GetUserJobCategoriesAction $getCategories,
    ): Response {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $filters = JobIndexFiltersData::from([
            'search' => $request->query('search'),
            'status_id' => $request->query('status_id') !== null ? (int) $request->query('status_id') : null,
            'date_from' => $request->query('date_from'),
            'date_to' => $request->query('date_to'),
            'job_category_id' => $request->query('job_category_id') !== null ? (int) $request->query('job_category_id') : null,
            'sort' => $request->query('sort'),
        ]);

        return Inertia::render('jobs/Index', [
            'jobs' => $getJobs->execute($user, $filters),
            'filters' => $filters,
            'statusTabs' => $getStatusTabs->execute($user),
            'categories' => $getCategories->execute($user),
        ]);
    }
}
