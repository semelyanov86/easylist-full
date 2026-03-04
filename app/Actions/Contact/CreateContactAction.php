<?php

declare(strict_types=1);

namespace App\Actions\Contact;

use App\Models\Contact;
use App\Models\Job;
use App\Models\User;

final readonly class CreateContactAction
{
    /**
     * @param  array{first_name: string, last_name: string, position?: ?string, city?: ?string, email?: ?string, phone?: ?string, description?: ?string, linkedin_url?: ?string, facebook_url?: ?string, whatsapp_url?: ?string}  $data
     */
    public function execute(User $user, Job $job, array $data): Contact
    {
        /** @var Contact $contact */
        $contact = $job->contacts()->create([
            'user_id' => $user->id,
            ...$data,
        ]);

        activity('job')
            ->performedOn($job)
            ->causedBy($user)
            ->withProperties([
                'contact_id' => $contact->id,
                'contact_name' => $contact->first_name . ' ' . $contact->last_name,
            ])
            ->event('contact_added')
            ->log('Добавлен контакт');

        return $contact;
    }
}
