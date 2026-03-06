<?php

declare(strict_types=1);

namespace App\Actions\CoverLetter;

use App\Contracts\AiCoverLetterContract;
use App\Enums\DocumentCategory;
use App\Models\Job;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

final readonly class GenerateCoverLetterAction
{
    public function __construct(
        private AiCoverLetterContract $generator,
    ) {}

    /**
     * Сгенерировать cover letter через ИИ и сохранить как документ вакансии.
     */
    public function execute(User $user, Job $job): void
    {
        $job->loadMissing('contacts');

        $prompt = $this->buildPrompt($user, $job);
        $texContent = $this->generator->generate($prompt);

        $filename = "cover_letter_{$job->id}_" . time() . '.tex';
        $path = "documents/{$user->id}/{$filename}";

        Storage::disk('local')->put($path, $texContent);

        /** @var int $fileSize */
        $fileSize = Storage::disk('local')->size($path);

        $job->documents()->create([
            'user_id' => $user->id,
            'title' => 'Cover Letter — ' . $job->company_name,
            'category' => DocumentCategory::CoverLetter,
            'file_path' => $path,
            'original_filename' => $filename,
            'mime_type' => 'application/x-tex',
            'file_size' => $fileSize,
        ]);

        activity('job')
            ->performedOn($job)
            ->causedBy($user)
            ->event('cover_letter_generated')
            ->log('Сгенерировано сопроводительное письмо через ИИ');
    }

    /**
     * Собрать prompt для генерации cover letter.
     */
    private function buildPrompt(User $user, Job $job): string
    {
        $parts = ["/cover-letter-generator {$user->about_me}"];

        $parts[] = "\nИнформация о вакансии";
        $parts[] = $job->description ?? '';

        if ($job->contacts->isNotEmpty()) {
            $parts[] = "\nКонтакты";

            foreach ($job->contacts as $contact) {
                $contactLine = trim("{$contact->first_name} {$contact->last_name}");

                if ($contact->position !== null && $contact->position !== '') {
                    $contactLine .= " — {$contact->position}";
                }

                if ($contact->email !== null && $contact->email !== '') {
                    $contactLine .= ", {$contact->email}";
                }

                $parts[] = $contactLine;
            }
        }

        $companyLine = "Компания: {$job->company_name}";

        if ($job->location_city !== null && $job->location_city !== '') {
            $companyLine .= ", {$job->location_city}";
        }

        $parts[] = "\n" . $companyLine;

        return implode("\n", $parts);
    }
}
