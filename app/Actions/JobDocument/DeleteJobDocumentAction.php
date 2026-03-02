<?php

declare(strict_types=1);

namespace App\Actions\JobDocument;

use App\Models\JobDocument;
use Illuminate\Support\Facades\Storage;

final readonly class DeleteJobDocumentAction
{
    public function execute(JobDocument $document): void
    {
        /** @var \App\Models\Job $job */
        $job = $document->job;
        $title = $document->title;
        $userId = $document->user_id;

        if ($document->file_path !== null) {
            Storage::disk('local')->delete($document->file_path);
        }

        $document->delete();

        activity('job')
            ->performedOn($job)
            ->causedBy($userId)
            ->withProperties([
                'document_title' => $title,
            ])
            ->event('document_removed')
            ->log('Удалён документ');
    }
}
