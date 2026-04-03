<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Data\JobTaskData;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\JobTaskResource;
use App\Http\Traits\JsonApiResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\User;

final class TaskController extends Controller
{
    use JsonApiResponses;

    /**
     * Получить незавершённые задачи текущего пользователя.
     */
    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $tasks = $user->tasks()
            ->whereNull('completed_at')
            ->with('job')
            ->latest('deadline')
            ->get();

        /** @var list<array<string, mixed>> $data */
        $data = $tasks->map(function ($task) use ($request): array {
            $taskData = JobTaskData::from([
                'id' => $task->id,
                'user_id' => $task->user_id,
                'title' => $task->title,
                'external_id' => $task->external_id,
                'deadline' => $task->deadline?->toISOString(),
                'completed_at' => $task->completed_at?->toISOString(),
                'created_at' => $task->created_at?->toISOString() ?? '',
            ]);

            $resource = new JobTaskResource($taskData)->toArray($request);

            $resource['relationships'] = [
                'job' => [
                    'data' => [
                        'type' => 'jobs',
                        'id' => (string) $task->job_id,
                    ],
                ],
            ];

            return $resource;
        })->values()->all();

        // Собрать included jobs (дедупликация)
        $included = [];
        $seen = [];

        foreach ($tasks as $task) {
            $key = "jobs-{$task->job_id}";
            if (! isset($seen[$key]) && $task->relationLoaded('job') && $task->job !== null) {
                $seen[$key] = true;
                $included[] = [
                    'type' => 'jobs',
                    'id' => (string) $task->job->id,
                    'attributes' => [
                        'title' => $task->job->title,
                        'company_name' => $task->job->company_name,
                    ],
                ];
            }
        }

        return $this->jsonApiList($data, $included);
    }
}
