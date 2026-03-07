<?php

declare(strict_types=1);

namespace App\Http\Traits;

use App\Data\ContactData;
use App\Data\JobCategoryData;
use App\Data\JobCommentData;
use App\Data\JobDocumentData;
use App\Data\JobListItemData;
use App\Data\JobStatusData;
use App\Data\JobTaskData;
use App\Http\Resources\Api\V1\ContactResource;
use App\Http\Resources\Api\V1\JobCategoryResource;
use App\Http\Resources\Api\V1\JobCommentResource;
use App\Http\Resources\Api\V1\JobDocumentResource;
use App\Http\Resources\Api\V1\JobStatusResource;
use App\Http\Resources\Api\V1\JobTaskResource;
use App\Models\Job;
use Illuminate\Http\Request;

/**
 * Общая логика для формирования списков вакансий в формате JSON:API.
 */
trait BuildsJobListJsonApi
{
    /**
     * Преобразовать JobListItemData в JSON:API resource object.
     *
     * @return array<string, mixed>
     */
    protected function jobListItemToJsonApi(JobListItemData $item): array
    {
        return [
            'type' => 'jobs',
            'id' => (string) $item->id,
            'attributes' => [
                'uuid' => $item->uuid,
                'title' => $item->title,
                'company_name' => $item->company_name,
                'description' => $item->description,
                'job_url' => $item->job_url,
                'location_city' => $item->location_city,
                'salary' => $item->salary,
                'is_favorite' => $item->is_favorite,
                'created_at' => $item->created_at,
            ],
            'relationships' => [
                'status' => ['data' => ['type' => 'job-statuses', 'id' => (string) $item->job_status_id]],
                'category' => ['data' => ['type' => 'job-categories', 'id' => (string) $item->job_category_id]],
            ],
        ];
    }

    /**
     * Преобразовать модель Job в JSON:API resource object (с поддержкой include).
     *
     * @param  list<string>  $includes
     * @return array<string, mixed>
     */
    protected function jobModelToJsonApi(Job $job, array $includes): array
    {
        $relationships = [
            'status' => ['data' => ['type' => 'job-statuses', 'id' => (string) $job->job_status_id]],
            'category' => ['data' => ['type' => 'job-categories', 'id' => (string) $job->job_category_id]],
        ];

        if (in_array('contacts', $includes, true) && $job->relationLoaded('contacts')) {
            $relationships['contacts'] = [
                'data' => $job->contacts->map(fn ($c): array => ['type' => 'contacts', 'id' => (string) $c->id])->values()->all(),
            ];
        }

        if (in_array('comments', $includes, true) && $job->relationLoaded('comments')) {
            $relationships['comments'] = [
                'data' => $job->comments->map(fn ($c): array => ['type' => 'comments', 'id' => (string) $c->id])->values()->all(),
            ];
        }

        if (in_array('documents', $includes, true) && $job->relationLoaded('documents')) {
            $relationships['documents'] = [
                'data' => $job->documents->map(fn ($d): array => ['type' => 'documents', 'id' => (string) $d->id])->values()->all(),
            ];
        }

        if (in_array('tasks', $includes, true) && $job->relationLoaded('tasks')) {
            $relationships['tasks'] = [
                'data' => $job->tasks->map(fn ($t): array => ['type' => 'tasks', 'id' => (string) $t->id])->values()->all(),
            ];
        }

        return [
            'type' => 'jobs',
            'id' => (string) $job->id,
            'attributes' => [
                'uuid' => $job->uuid,
                'title' => $job->title,
                'company_name' => $job->company_name,
                'description' => $job->description,
                'job_url' => $job->job_url,
                'location_city' => $job->location_city,
                'salary' => $job->salary,
                'is_favorite' => $job->is_favorite,
                'created_at' => $job->created_at?->toISOString(),
            ],
            'relationships' => $relationships,
        ];
    }

    /**
     * Определить какие связи нужно eager load.
     *
     * @param  list<string>  $includes
     * @return list<string>
     */
    protected function resolveJobEagerLoad(array $includes): array
    {
        $eagerLoad = [];

        if (in_array('contacts', $includes, true)) {
            $eagerLoad[] = 'contacts';
        }

        if (in_array('comments', $includes, true)) {
            $eagerLoad[] = 'comments.user';
        }

        if (in_array('documents', $includes, true)) {
            $eagerLoad[] = 'documents.user';
        }

        if (in_array('tasks', $includes, true)) {
            $eagerLoad[] = 'tasks';
        }

        return $eagerLoad;
    }

    /**
     * Собрать included ресурсы из модели Job (дедупликация по type-id).
     *
     * @param  list<string>  $includes
     * @param  list<array<string, mixed>>  $included
     * @param  array<string, true>  $seen
     */
    protected function collectJobIncluded(
        Job $job,
        array $includes,
        Request $request,
        array &$included,
        array &$seen,
    ): void {
        $statusKey = "job-statuses-{$job->job_status_id}";
        if (! isset($seen[$statusKey]) && $job->relationLoaded('status')) {
            $seen[$statusKey] = true;
            $included[] = new JobStatusResource(JobStatusData::from($job->status))->toArray($request);
        }

        $categoryKey = "job-categories-{$job->job_category_id}";
        if (! isset($seen[$categoryKey]) && $job->relationLoaded('category')) {
            $seen[$categoryKey] = true;
            $included[] = new JobCategoryResource(JobCategoryData::from($job->category))->toArray($request);
        }

        if ($job->relationLoaded('skills')) {
            foreach ($job->skills as $skill) {
                $key = "skills-{$skill->id}";
                if (! isset($seen[$key])) {
                    $seen[$key] = true;
                    $included[] = [
                        'type' => 'skills',
                        'id' => (string) $skill->id,
                        'attributes' => ['title' => $skill->title],
                    ];
                }
            }
        }

        if (in_array('contacts', $includes, true) && $job->relationLoaded('contacts')) {
            foreach ($job->contacts as $contact) {
                $key = "contacts-{$contact->id}";
                if (! isset($seen[$key])) {
                    $seen[$key] = true;
                    $included[] = new ContactResource(ContactData::from($contact))->toArray($request);
                }
            }
        }

        if (in_array('comments', $includes, true) && $job->relationLoaded('comments')) {
            foreach ($job->comments as $comment) {
                $key = "comments-{$comment->id}";
                if (! isset($seen[$key])) {
                    $seen[$key] = true;
                    $included[] = new JobCommentResource(JobCommentData::from([
                        'id' => $comment->id,
                        'body' => $comment->body,
                        'author_name' => $comment->user->name ?? '',
                        'user_id' => $comment->user_id,
                        'created_at' => $comment->created_at?->toISOString() ?? '',
                    ]))->toArray($request);
                }
            }
        }

        if (in_array('documents', $includes, true) && $job->relationLoaded('documents')) {
            foreach ($job->documents as $document) {
                $key = "documents-{$document->id}";
                if (! isset($seen[$key])) {
                    $seen[$key] = true;
                    $included[] = new JobDocumentResource(JobDocumentData::from([
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
                        'author_name' => $document->user->name ?? '',
                        'created_at' => $document->created_at?->toISOString() ?? '',
                    ]))->toArray($request);
                }
            }
        }

        if (in_array('tasks', $includes, true) && $job->relationLoaded('tasks')) {
            foreach ($job->tasks as $task) {
                $key = "tasks-{$task->id}";
                if (! isset($seen[$key])) {
                    $seen[$key] = true;
                    $included[] = new JobTaskResource(JobTaskData::from([
                        'id' => $task->id,
                        'user_id' => $task->user_id,
                        'title' => $task->title,
                        'external_id' => $task->external_id,
                        'deadline' => $task->deadline?->toISOString(),
                        'completed_at' => $task->completed_at?->toISOString(),
                        'created_at' => $task->created_at?->toISOString() ?? '',
                    ]))->toArray($request);
                }
            }
        }
    }
}
