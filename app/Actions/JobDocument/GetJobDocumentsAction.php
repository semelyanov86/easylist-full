<?php

declare(strict_types=1);

namespace App\Actions\JobDocument;

use App\Data\JobDocumentData;
use App\Models\Job;
use App\Models\JobDocument;

final readonly class GetJobDocumentsAction
{
    /**
     * @return list<JobDocumentData>
     */
    public function execute(Job $job): array
    {
        $documents = $job->documents()
            ->with('user:id,name')
            ->latest()
            ->get();

        return array_values($documents->map(fn (JobDocument $document): JobDocumentData => new JobDocumentData(
            id: $document->id,
            title: $document->title,
            description: $document->description,
            category: $document->category->value,
            category_label: $document->category->label(),
            file_path: $document->file_path,
            original_filename: $document->original_filename,
            mime_type: $document->mime_type,
            file_size: $document->file_size,
            external_url: $document->external_url,
            author_name: $document->user->name ?? '',
            created_at: $document->created_at?->toISOString() ?? '',
        ))->all());
    }
}
