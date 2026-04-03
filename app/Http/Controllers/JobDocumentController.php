<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\JobDocument\CreateJobDocumentAction;
use App\Actions\JobDocument\DeleteJobDocumentAction;
use App\Http\Requests\StoreJobDocumentRequest;
use App\Models\Job;
use App\Models\JobDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

final class JobDocumentController extends Controller
{
    /**
     * Добавить документ к вакансии.
     */
    public function store(StoreJobDocumentRequest $request, Job $job, CreateJobDocumentAction $action): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        abort_if($job->user_id !== $user->id, 403);

        /** @var array{title: string, description?: string|null, category: string, external_url?: string|null} $data */
        $data = $request->validated();

        $action->execute($user, $job, $data, $request->file('file'));

        return back();
    }

    /**
     * Скачать файл документа.
     */
    public function download(Request $request, JobDocument $document): StreamedResponse
    {
        /** @var User $user */
        $user = $request->user();

        /** @var Job $job */
        $job = $document->job;

        abort_if($job->user_id !== $user->id, 403);
        abort_if($document->file_path === null, 404);

        return Storage::disk('local')->download(
            $document->file_path,
            $document->original_filename,
        );
    }

    /**
     * Удалить документ.
     */
    public function destroy(Request $request, JobDocument $document, DeleteJobDocumentAction $action): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        /** @var Job $job */
        $job = $document->job;

        abort_if($job->user_id !== $user->id, 403);

        $action->execute($document);

        return back();
    }
}
