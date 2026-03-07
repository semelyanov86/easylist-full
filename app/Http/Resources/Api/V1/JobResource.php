<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Data\ContactData;
use App\Data\JobCommentData;
use App\Data\JobDocumentData;
use App\Data\JobShowData;
use App\Data\JobTaskData;
use App\Data\SkillData;
use Illuminate\Http\Request;

/**
 * Ресурс вакансии для детального просмотра (работает с JobShowData).
 *
 * @property JobShowData $resource
 */
final class JobResource extends BaseJsonApiResource
{
    /**
     * @param  list<string>  $includes
     */
    public function __construct(
        JobShowData $resource,
        private readonly array $includes = [],
    ) {
        parent::__construct($resource);
    }

    /**
     * Построить массив included ресурсов для JSON:API.
     *
     * @return list<array<string, mixed>>
     */
    public function buildIncluded(Request $request): array
    {
        $included = [];

        $included[] = new JobStatusResource($this->resource->status)->toArray($request);
        $included[] = new JobCategoryResource($this->resource->category)->toArray($request);

        foreach ($this->resource->skills as $skill) {
            $included[] = [
                'type' => 'skills',
                'id' => (string) $skill->id,
                'attributes' => ['title' => $skill->title],
            ];
        }

        if (in_array('contacts', $this->includes, true)) {
            foreach ($this->resource->contacts as $contact) {
                $included[] = new ContactResource($contact)->toArray($request);
            }
        }

        if (in_array('comments', $this->includes, true)) {
            foreach ($this->resource->comments as $comment) {
                $included[] = new JobCommentResource($comment)->toArray($request);
            }
        }

        if (in_array('documents', $this->includes, true)) {
            foreach ($this->resource->documents as $document) {
                $included[] = new JobDocumentResource($document)->toArray($request);
            }
        }

        if (in_array('tasks', $this->includes, true)) {
            foreach ($this->resource->tasks as $task) {
                $included[] = new JobTaskResource($task)->toArray($request);
            }
        }

        if (in_array('company-info', $this->includes, true) && $this->resource->company_info !== null) {
            $included[] = new CompanyInfoResource($this->resource->company_info)->toArray($request);
        }

        return $included;
    }

    protected function resourceType(): string
    {
        return 'jobs';
    }

    /**
     * @return array<string, mixed>
     */
    protected function resourceAttributes(Request $request): array
    {
        return [
            'uuid' => $this->resource->uuid,
            'title' => $this->resource->title,
            'company_name' => $this->resource->company_name,
            'description' => $this->resource->description,
            'job_url' => $this->resource->job_url,
            'location_city' => $this->resource->location_city,
            'salary' => $this->resource->salary,
            'is_favorite' => $this->resource->is_favorite,
            'created_at' => $this->resource->created_at,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    #[\Override]
    protected function resourceRelationships(Request $request): array
    {
        $relationships = [
            'status' => $this->toOneRelationship('job-statuses', $this->resource->job_status_id),
            'category' => $this->toOneRelationship('job-categories', $this->resource->job_category_id),
        ];

        if ($this->resource->skills !== []) {
            $relationships['skills'] = $this->toManyRelationship(
                'skills',
                array_map(fn (SkillData $s): int => $s->id, $this->resource->skills),
            );
        }

        if (in_array('contacts', $this->includes, true)) {
            $relationships['contacts'] = $this->toManyRelationship(
                'contacts',
                array_map(fn (ContactData $c): int => $c->id, $this->resource->contacts),
            );
        }

        if (in_array('comments', $this->includes, true)) {
            $relationships['comments'] = $this->toManyRelationship(
                'comments',
                array_map(fn (JobCommentData $c): int => $c->id, $this->resource->comments),
            );
        }

        if (in_array('documents', $this->includes, true)) {
            $relationships['documents'] = $this->toManyRelationship(
                'documents',
                array_map(fn (JobDocumentData $d): int => $d->id, $this->resource->documents),
            );
        }

        if (in_array('tasks', $this->includes, true)) {
            $relationships['tasks'] = $this->toManyRelationship(
                'tasks',
                array_map(fn (JobTaskData $t): int => $t->id, $this->resource->tasks),
            );
        }

        if (in_array('company-info', $this->includes, true) && $this->resource->company_info !== null) {
            $relationships['company-info'] = $this->toOneRelationship(
                'company-infos',
                $this->resource->company_info->id,
            );
        }

        return $relationships;
    }
}
