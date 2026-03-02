<?php

declare(strict_types=1);

namespace App\Actions\JobDocument;

use App\Enums\DocumentCategory;
use App\Models\Job;
use App\Models\JobDocument;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

final readonly class CreateJobDocumentAction
{
    /**
     * @param  array{title: string, description?: string|null, category: string, external_url?: string|null}  $data
     */
    public function execute(User $user, Job $job, array $data, ?UploadedFile $file = null): JobDocument
    {
        $attributes = [
            'user_id' => $user->id,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'category' => $data['category'],
            'external_url' => $data['external_url'] ?? null,
        ];

        if ($file !== null) {
            $path = Storage::disk('local')->putFile('documents', $file);

            $attributes['file_path'] = $path;
            $attributes['original_filename'] = $file->getClientOriginalName();
            $attributes['mime_type'] = $file->getClientMimeType();
            $attributes['file_size'] = $file->getSize();
        }

        /** @var JobDocument $document */
        $document = $job->documents()->create($attributes);

        $category = DocumentCategory::from($data['category']);

        activity('job')
            ->performedOn($job)
            ->causedBy($user)
            ->withProperties([
                'document_id' => $document->id,
                'document_title' => $document->title,
                'category_label' => $category->label(),
            ])
            ->event('document_added')
            ->log('Добавлен документ');

        return $document;
    }
}
