<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\Job\GetJobPublicViewDataAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\JobPublicResource;
use App\Http\Traits\JsonApiResponses;
use App\Models\Job;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Публичный просмотр вакансии по UUID (без авторизации).
 */
final class JobPublicController extends Controller
{
    use JsonApiResponses;

    public function show(Request $request, string $uuid, GetJobPublicViewDataAction $action): JsonResponse
    {
        $job = Job::query()
            ->where('uuid', $uuid)
            ->firstOrFail();

        $data = $action->execute($job);
        $resource = new JobPublicResource($data);

        return $this->jsonApiSingle(
            $resource->toArray($request),
            $resource->buildIncluded($request),
        );
    }
}
