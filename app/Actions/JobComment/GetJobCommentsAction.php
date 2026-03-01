<?php

declare(strict_types=1);

namespace App\Actions\JobComment;

use App\Data\JobCommentData;
use App\Models\Job;
use App\Models\JobComment;

final readonly class GetJobCommentsAction
{
    /**
     * @return list<JobCommentData>
     */
    public function execute(Job $job): array
    {
        $comments = $job->comments()
            ->with('user:id,name')
            ->latest()
            ->get();

        return array_values($comments->map(fn (JobComment $comment): JobCommentData => new JobCommentData(
            id: $comment->id,
            body: $comment->body,
            author_name: $comment->user->name ?? '',
            user_id: $comment->user_id,
            created_at: $comment->created_at?->toISOString() ?? '',
        ))->all());
    }
}
