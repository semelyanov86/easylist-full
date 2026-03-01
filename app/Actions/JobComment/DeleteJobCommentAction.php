<?php

declare(strict_types=1);

namespace App\Actions\JobComment;

use App\Models\JobComment;

final readonly class DeleteJobCommentAction
{
    public function execute(JobComment $comment): void
    {
        $comment->delete();
    }
}
