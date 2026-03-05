<?php

declare(strict_types=1);

namespace App\Jobs\TickTick;

use App\Contracts\TickTickClientContract;
use App\Models\JobTask;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

final class SyncTickTickTaskUpdated implements ShouldQueue
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

        if ($task === null || $task->external_id === null || $task->user === null || $task->job === null) {
            return;
        }

        $user = $task->user;

        if ($user->ticktick_token === null || $user->ticktick_list_id === null) {
            return;
        }

        $client->updateTask($user->ticktick_token, $task->external_id, [
            'id' => $task->external_id,
            'projectId' => $user->ticktick_list_id,
            'title' => $task->title,
            'content' => $this->buildContent($task->job),
            'dueDate' => $task->deadline?->toIso8601String(),
        ]);
    }
}
