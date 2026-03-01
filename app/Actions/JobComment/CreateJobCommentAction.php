<?php

declare(strict_types=1);

namespace App\Actions\JobComment;

use App\Models\Job;
use App\Models\JobComment;
use App\Models\User;

final readonly class CreateJobCommentAction
{
    /**
     * @param  array{body: string}  $data
     */
    public function execute(User $user, Job $job, array $data): JobComment
    {
        /** @var JobComment */
        return $job->comments()->create([
            'user_id' => $user->id,
            ...$data,
        ]);
    }
}
