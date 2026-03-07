<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\Job\GetUserJobsQuery;
use App\Actions\JobCategory\GetUserJobCategoriesAction;
use App\Data\JobCategoryData;
use App\Data\JobIndexFiltersData;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\JobCategoryResource;
use App\Http\Traits\BuildsJobListJsonApi;
use App\Http\Traits\JsonApiResponses;
use App\Models\Job;
use App\Models\JobCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

final class JobCategoryController extends Controller
{
    use BuildsJobListJsonApi;
    use JsonApiResponses;

    /**
     * Получить все списки (категории) пользователя.
     */
    public function index(Request $request, GetUserJobCategoriesAction $action): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $categories = $action->execute($user);

        /** @var list<array<string, mixed>> $data */
        $data = $categories->map(
            fn (JobCategoryData $category): array => new JobCategoryResource($category)->toArray($request),
        )->values()->all();

        return $this->jsonApiList($data);
    }

    /**
     * Получить список (категорию) по id.
     */
    public function show(Request $request, JobCategory $jobCategory): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        abort_if($jobCategory->user_id !== $user->id, 403);

        $data = JobCategoryData::from($jobCategory);
        $resource = new JobCategoryResource($data);

        return $this->jsonApiSingle($resource->toArray($request));
    }

    /**
     * Получить вакансии из списка с фильтрацией, пагинацией и include.
     */
    public function jobs(Request $request, JobCategory $jobCategory, GetUserJobsQuery $query): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        abort_if($jobCategory->user_id !== $user->id, 403);

        $includes = $this->parseIncludes($request);
        $this->validateIncludes($includes, ['contacts', 'comments', 'documents', 'tasks', 'company-info']);

        $perPage = $this->getPageSize($request);

        Paginator::currentPageResolver(fn (): int => $this->getPageNumber($request));

        /** @var string|null $statusId */
        $statusId = $request->input('filter.status_id');

        /** @var string|null $isFavorite */
        $isFavorite = $request->input('filter.is_favorite');

        $filters = JobIndexFiltersData::from([
            'search' => $request->input('filter.search'),
            'status_id' => $statusId !== null ? (int) $statusId : null,
            'date_from' => $request->input('filter.date_from'),
            'date_to' => $request->input('filter.date_to'),
            'job_category_id' => $jobCategory->id,
            'is_favorite' => $isFavorite !== null ? (bool) $isFavorite : null,
        ]);

        if ($includes !== []) {
            $eagerLoad = $this->resolveJobEagerLoad($includes);
            $paginator = $query->executeWithModels($user, $filters, $eagerLoad, $perPage);

            $data = [];
            $included = [];
            $seen = [];

            /** @var Job $job */
            foreach ($paginator->items() as $job) {
                $data[] = $this->jobModelToJsonApi($job, $includes);
                $this->collectJobIncluded($job, $includes, $request, $included, $seen);
            }

            return $this->jsonApiPaginated($paginator, $data, $included); // @phpstan-ignore argument.type
        }

        $paginator = $query->execute($user, $filters, $perPage);

        $data = array_map(
            $this->jobListItemToJsonApi(...),
            array_values($paginator->items()),
        );

        return $this->jsonApiPaginated($paginator, $data); // @phpstan-ignore argument.type
    }
}
