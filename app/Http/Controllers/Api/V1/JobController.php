<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\Job\CreateJobAction;
use App\Actions\Job\DeleteJobAction;
use App\Actions\Job\GetJobShowDataAction;
use App\Actions\Job\GetUserJobsQuery;
use App\Actions\Job\MoveJobToStatusAction;
use App\Actions\Job\ShareJobAction;
use App\Actions\Job\ToggleFavoriteAction;
use App\Actions\Job\UpdateJobAction;
use App\Actions\Skill\SyncJobSkillsAction;
use App\Data\JobIndexFiltersData;
use App\Http\Controllers\Controller;
use App\Http\Requests\MoveJobRequest;
use App\Http\Requests\StoreJobRequest;
use App\Http\Requests\UpdateJobRequest;
use App\Http\Resources\Api\V1\JobResource;
use App\Http\Traits\BuildsJobListJsonApi;
use App\Http\Traits\JsonApiResponses;
use App\Models\Job;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use App\Models\User;

final class JobController extends Controller
{
    use BuildsJobListJsonApi;
    use JsonApiResponses;

    private const array ALLOWED_INCLUDES = ['contacts', 'comments', 'documents', 'tasks', 'company-info'];

    /**
     * Список вакансий с фильтрацией и пагинацией.
     */
    public function index(Request $request, GetUserJobsQuery $query): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $filters = $this->buildFilters($request);
        $perPage = $this->getPageSize($request);
        $includes = $this->parseIncludes($request);
        $this->validateIncludes($includes, self::ALLOWED_INCLUDES);

        Paginator::currentPageResolver(fn (): int => $this->getPageNumber($request));

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

    /**
     * Детальный просмотр вакансии.
     */
    public function show(Request $request, Job $job, GetJobShowDataAction $action): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        abort_if($job->user_id !== $user->id, 403);

        $includes = $this->parseIncludes($request);
        $this->validateIncludes($includes, self::ALLOWED_INCLUDES);

        $data = $action->execute($job);
        $resource = new JobResource($data, $includes);

        return $this->jsonApiSingle(
            $resource->toArray($request),
            $resource->buildIncluded($request),
        );
    }

    /**
     * Создать вакансию.
     */
    public function store(
        StoreJobRequest $request,
        CreateJobAction $action,
        SyncJobSkillsAction $syncSkills,
        GetJobShowDataAction $showAction,
    ): JsonResponse {
        /** @var User $user */
        $user = $request->user();

        /** @var array{title: string, company_name: string, job_status_id: int, job_category_id: int, description?: string|null, job_url?: string|null, salary?: int|null, location_city?: string|null, skill_ids?: list<int>|null} $data */
        $data = $request->validated();

        /** @var list<int> $skillIds */
        $skillIds = $data['skill_ids'] ?? [];
        unset($data['skill_ids']);

        $job = $action->execute($user, $data);
        $syncSkills->execute($user, $job, $skillIds);

        $showData = $showAction->execute($job->fresh() ?? $job);
        $resource = new JobResource($showData);

        return $this->jsonApiCreated(
            $resource->toArray($request),
            $resource->buildIncluded($request),
        );
    }

    /**
     * Обновить вакансию.
     */
    public function update(
        UpdateJobRequest $request,
        Job $job,
        UpdateJobAction $action,
        SyncJobSkillsAction $syncSkills,
        GetJobShowDataAction $showAction,
    ): JsonResponse {
        /** @var User $user */
        $user = $request->user();

        abort_if($job->user_id !== $user->id, 403);

        /** @var array{title: string, company_name: string, job_status_id: int, job_category_id: int, description?: string|null, job_url?: string|null, salary?: int|null, location_city?: string|null, skill_ids?: list<int>|null} $data */
        $data = $request->validated();

        /** @var list<int> $skillIds */
        $skillIds = $data['skill_ids'] ?? [];
        unset($data['skill_ids']);

        $action->execute($user, $job, $data);
        $syncSkills->execute($user, $job, $skillIds);

        $showData = $showAction->execute($job->fresh() ?? $job);
        $resource = new JobResource($showData);

        return $this->jsonApiSingle(
            $resource->toArray($request),
            $resource->buildIncluded($request),
        );
    }

    /**
     * Удалить вакансию.
     */
    public function destroy(Request $request, Job $job, DeleteJobAction $action): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        abort_if($job->user_id !== $user->id, 403);

        $action->execute($job);

        return $this->jsonApiNoContent();
    }

    /**
     * Переместить вакансию в другой статус.
     */
    public function moveStatus(
        MoveJobRequest $request,
        Job $job,
        MoveJobToStatusAction $action,
        GetJobShowDataAction $showAction,
    ): JsonResponse {
        /** @var User $user */
        $user = $request->user();

        abort_if($job->user_id !== $user->id, 403);

        /** @var int $statusId */
        $statusId = $request->validated('status_id');

        $action->execute($user, $job, $statusId);

        $showData = $showAction->execute($job->fresh() ?? $job);
        $resource = new JobResource($showData);

        return $this->jsonApiSingle(
            $resource->toArray($request),
            $resource->buildIncluded($request),
        );
    }

    /**
     * Переключить статус избранного.
     */
    public function toggleFavorite(
        Request $request,
        Job $job,
        ToggleFavoriteAction $action,
        GetJobShowDataAction $showAction,
    ): JsonResponse {
        /** @var User $user */
        $user = $request->user();

        abort_if($job->user_id !== $user->id, 403);

        $action->execute($job);

        $showData = $showAction->execute($job->fresh() ?? $job);
        $resource = new JobResource($showData);

        return $this->jsonApiSingle(
            $resource->toArray($request),
            $resource->buildIncluded($request),
        );
    }

    /**
     * Сгенерировать публичную ссылку (назначить uuid).
     */
    public function share(Request $request, Job $job, ShareJobAction $action): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        abort_if($job->user_id !== $user->id, 403);

        $uuid = $action->execute($job);

        return $this->jsonApiSingle([
            'type' => 'jobs',
            'id' => (string) $job->id,
            'attributes' => ['uuid' => $uuid],
        ]);
    }

    /**
     * Построить фильтры из JSON:API query parameters.
     */
    private function buildFilters(Request $request): JobIndexFiltersData
    {
        /** @var string|null $statusId */
        $statusId = $request->input('filter.status_id');

        /** @var string|null $categoryId */
        $categoryId = $request->input('filter.job_category_id');

        /** @var string|null $isFavorite */
        $isFavorite = $request->input('filter.is_favorite');

        return JobIndexFiltersData::from([
            'search' => $request->input('filter.search'),
            'status_id' => $statusId !== null ? (int) $statusId : null,
            'date_from' => $request->input('filter.date_from'),
            'date_to' => $request->input('filter.date_to'),
            'job_category_id' => $categoryId !== null ? (int) $categoryId : null,
            'is_favorite' => $isFavorite !== null ? (bool) $isFavorite : null,
        ]);
    }
}
