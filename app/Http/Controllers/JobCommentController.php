<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\JobComment\CreateJobCommentAction;
use App\Actions\JobComment\DeleteJobCommentAction;
use App\Actions\JobComment\UpdateJobCommentAction;
use App\Http\Requests\StoreJobCommentRequest;
use App\Http\Requests\UpdateJobCommentRequest;
use App\Models\Job;
use App\Models\JobComment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class JobCommentController extends Controller
{
    /**
     * Создать комментарий к вакансии.
     */
    public function store(StoreJobCommentRequest $request, Job $job, CreateJobCommentAction $action): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        abort_if($job->user_id !== $user->id, 403);

        /** @var array{body: string} $data */
        $data = $request->validated();

        $action->execute($user, $job, $data);

        return back();
    }

    /**
     * Обновить комментарий.
     */
    public function update(UpdateJobCommentRequest $request, JobComment $comment, UpdateJobCommentAction $action): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        abort_if($comment->user_id !== $user->id, 403);

        /** @var array{body: string} $data */
        $data = $request->validated();

        $action->execute($comment, $data);

        return back();
    }

    /**
     * Удалить комментарий.
     */
    public function destroy(Request $request, JobComment $comment, DeleteJobCommentAction $action): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        abort_if($comment->user_id !== $user->id, 403);

        $action->execute($comment);

        return back();
    }
}
