<?php

declare(strict_types=1);

namespace App\Actions\JobComment;

use App\Models\Job;
use App\Models\JobComment;
use App\Models\User;
use Illuminate\Support\Str;

final readonly class CreateJobCommentAction
{
    /**
     * @param  array{body: string}  $data
     */
    public function execute(User $user, Job $job, array $data): JobComment
    {
        /** @var JobComment $comment */
        $comment = $job->comments()->create([
            'user_id' => $user->id,
            ...$data,
        ]);

        activity('job')
            ->performedOn($job)
            ->causedBy($user)
            ->withProperties([
                'comment_id' => $comment->id,
                'comment_body' => Str::limit($comment->body, 100),
            ])
            ->event('comment_added')
            ->log('Добавлен комментарий');

        return $comment;
    }
}
