<?php

declare(strict_types=1);

namespace App\Actions\Ai;

use App\Contracts\AiContactFinderContract;
use App\Models\Job;
use App\Models\User;
use App\Models\Contact;

final readonly class FindContactsAction
{
    public function __construct(
        private AiContactFinderContract $finder,
    ) {}

    /**
     * Найти контакты через ИИ и сохранить их к вакансии.
     */
    public function execute(User $user, Job $job): void
    {
        $contacts = $this->finder->find(
            $job->company_name,
            $job->location_city,
        );

        /** @var array<string, mixed> $contactData */
        foreach ($contacts as $contactData) {
            /** @var Contact $contact */
            $contact = $job->contacts()->create([
                'user_id' => $user->id,
                'first_name' => $contactData['first_name'] ?? '',
                'last_name' => $contactData['last_name'] ?? '',
                'position' => $contactData['position'] ?? null,
                'city' => $contactData['city'] ?? null,
                'email' => $contactData['email'] ?? null,
                'phone' => $contactData['phone'] ?? null,
                'description' => $contactData['description'] ?? null,
                'linkedin_url' => $contactData['linkedin_url'] ?? null,
                'whatsapp_url' => $contactData['whatsapp_url'] ?? null,
            ]);

            activity('job')
                ->performedOn($job)
                ->causedBy($user)
                ->withProperties([
                    'contact_id' => $contact->id,
                    'contact_name' => $contact->first_name . ' ' . $contact->last_name,
                ])
                ->event('contact_added')
                ->log('Добавлен контакт через ИИ');
        }
    }
}
