<?php

declare(strict_types=1);

namespace App\Jobs\TickTick;

use App\Contracts\TickTickClientContract;
use App\Models\JobTask;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

final class SyncTickTickTaskCreated implements ShouldQueue
{
    use BuildsTickTickContent;
    use Queueable;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(
        private readonly int $jobTaskId,
    ) {}

    public function handle(TickTickClientContract $client): void
    {
        $task = JobTask::with('job', 'user')->find($this->jobTaskId);

        if ($task === null || $task->user === null || $task->job === null) {
            return;
        }

        $user = $task->user;

        if ($user->ticktick_token === null || $user->ticktick_list_id === null) {
            return;
        }

        $result = $client->createTask($user->ticktick_token, [
            'title' => $task->title,
            'content' => $this->buildContent($task->job),
            'projectId' => $user->ticktick_list_id,
            'dueDate' => $task->deadline?->toIso8601String(),
        ]);

        $task->update(['external_id' => $result['id']]);
    }
}
