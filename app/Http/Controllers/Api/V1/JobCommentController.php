<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\JobComment\CreateJobCommentAction;
use App\Actions\JobComment\GetJobCommentsAction;
use App\Data\JobCommentData;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJobCommentRequest;
use App\Http\Resources\Api\V1\JobCommentResource;
use App\Http\Traits\JsonApiResponses;
use App\Models\Job;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\User;

final class JobCommentController extends Controller
{
    use JsonApiResponses;

    /**
     * Получить комментарии вакансии.
     */
    public function index(Request $request, Job $job, GetJobCommentsAction $action): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        abort_if($job->user_id !== $user->id, 403);

        $comments = $action->execute($job);

        $data = array_map(
            fn (JobCommentData $comment): array => new JobCommentResource($comment)->toArray($request),
            $comments,
        );

        return $this->jsonApiList($data);
    }

    /**
     * Добавить комментарий к вакансии.
     */
    public function store(
        StoreJobCommentRequest $request,
        Job $job,
        CreateJobCommentAction $action,
    ): JsonResponse {
        /** @var User $user */
        $user = $request->user();

        abort_if($job->user_id !== $user->id, 403);

        /** @var array{body: string} $data */
        $data = $request->validated();

        $comment = $action->execute($user, $job, $data);

        $commentData = JobCommentData::from([
            'id' => $comment->id,
            'body' => $comment->body,
            'author_name' => $user->name,
            'user_id' => $user->id,
            'created_at' => $comment->created_at?->toISOString() ?? '',
        ]);

        $resource = new JobCommentResource($commentData);

        return $this->jsonApiCreated($resource->toArray($request));
    }
}
