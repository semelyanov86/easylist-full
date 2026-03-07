<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\JobDocument\CreateJobDocumentAction;
use App\Actions\JobDocument\GetJobDocumentsAction;
use App\Data\JobDocumentData;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJobDocumentRequest;
use App\Http\Resources\Api\V1\JobDocumentResource;
use App\Http\Traits\JsonApiResponses;
use App\Models\Job;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class JobDocumentController extends Controller
{
    use JsonApiResponses;

    /**
     * Получить документы вакансии.
     */
    public function index(Request $request, Job $job, GetJobDocumentsAction $action): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        abort_if($job->user_id !== $user->id, 403);

        $documents = $action->execute($job);

        $data = array_map(
            fn (JobDocumentData $doc): array => new JobDocumentResource($doc)->toArray($request),
            $documents,
        );

        return $this->jsonApiList($data);
    }

    /**
     * Прикрепить документ к вакансии.
     */
    public function store(
        StoreJobDocumentRequest $request,
        Job $job,
        CreateJobDocumentAction $action,
    ): JsonResponse {
        /** @var \App\Models\User $user */
        $user = $request->user();

        abort_if($job->user_id !== $user->id, 403);

        /** @var array{title: string, description?: string|null, category: string, external_url?: string|null} $data */
        $data = $request->validated();

        $document = $action->execute($user, $job, $data, $request->file('file'));

        $documentData = JobDocumentData::from([
            'id' => $document->id,
            'title' => $document->title,
            'description' => $document->description,
            'category' => $document->category->value,
            'category_label' => $document->category->label(),
            'file_path' => $document->file_path,
            'original_filename' => $document->original_filename,
            'mime_type' => $document->mime_type,
            'file_size' => $document->file_size,
            'external_url' => $document->external_url,
            'author_name' => $user->name,
            'created_at' => $document->created_at?->toISOString() ?? '',
        ]);

        $resource = new JobDocumentResource($documentData);

        return $this->jsonApiCreated($resource->toArray($request));
    }
}
