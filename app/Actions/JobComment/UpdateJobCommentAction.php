<?php

declare(strict_types=1);

namespace App\Actions\JobComment;

use App\Models\JobComment;

final readonly class UpdateJobCommentAction
{
    /**
     * @param  array{body: string}  $data
     */
    public function execute(JobComment $comment, array $data): void
    {
        $comment->update($data);
    }
}
