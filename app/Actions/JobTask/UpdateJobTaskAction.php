<?php

declare(strict_types=1);

namespace App\Actions\JobTask;

use App\Models\JobTask;

final readonly class UpdateJobTaskAction
{
    /**
     * @param  array{title?: string, deadline?: ?string}  $data
     */
    public function execute(JobTask $task, array $data): void
    {
        $task->update($data);
    }
}
