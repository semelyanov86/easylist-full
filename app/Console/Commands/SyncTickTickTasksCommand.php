<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Contracts\TickTickClientContract;
use App\Exceptions\TickTickException;
use App\Models\JobTask;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

final class SyncTickTickTasksCommand extends Command
{
    protected $signature = 'ticktick:sync';

    protected $description = 'Синхронизировать статусы задач из TickTick';

    public function handle(TickTickClientContract $client): int
    {
        $users = User::query()
            ->whereNotNull('ticktick_token')
            ->whereNotNull('ticktick_list_id')
            ->get();

        foreach ($users as $user) {
            $this->syncUserTasks($client, $user);
        }

        $this->info('Синхронизация TickTick завершена.');

        return self::SUCCESS;
    }

    private function syncUserTasks(TickTickClientContract $client, User $user): void
    {
        /** @var string $token */
        $token = $user->ticktick_token;

        /** @var string $projectId */
        $projectId = $user->ticktick_list_id;

        $tasks = JobTask::query()
            ->where('user_id', $user->id)
            ->whereNotNull('external_id')
            ->whereNull('completed_at')
            ->get();

        foreach ($tasks as $task) {
            $this->syncTask($client, $task, $token, $projectId);
        }
    }

    private function syncTask(TickTickClientContract $client, JobTask $task, string $token, string $projectId): void
    {
        /** @var string $externalId */
        $externalId = $task->external_id;

        try {
            $data = $client->getTask($token, $projectId, $externalId);

            if ($data['status'] === 2) {
                $task->update(['completed_at' => now()]);

                $job = $task->job;

                if ($job !== null) {
                    activity('job')
                        ->performedOn($job)
                        ->causedBy($task->user)
                        ->withProperties([
                            'task_id' => $task->id,
                            'task_title' => $task->title,
                        ])
                        ->event('task_completed')
                        ->log('Задача выполнена (TickTick)');
                }
            }
        } catch (TickTickException $e) {
            if (str_contains($e->getMessage(), 'не найдена')) {
                $job = $task->job;
                $user = $task->user;
                $taskTitle = $task->title;

                $task->delete();

                if ($job !== null && $user !== null) {
                    activity('job')
                        ->performedOn($job)
                        ->causedBy($user)
                        ->withProperties([
                            'task_title' => $taskTitle,
                        ])
                        ->event('task_removed')
                        ->log('Задача удалена (TickTick)');
                }

                return;
            }

            Log::warning("Ошибка синхронизации TickTick задачи {$externalId}: {$e->getMessage()}");
        }
    }
}
