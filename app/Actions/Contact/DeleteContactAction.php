<?php

declare(strict_types=1);

namespace App\Actions\Contact;

use App\Models\Contact;

final readonly class DeleteContactAction
{
    public function execute(Contact $contact): void
    {
        $job = $contact->job;
        $user = $contact->user;
        $contactName = $contact->first_name . ' ' . $contact->last_name;

        $contact->delete();

        if ($job !== null && $user !== null) {
            activity('job')
                ->performedOn($job)
                ->causedBy($user)
                ->withProperties([
                    'contact_name' => $contactName,
                ])
                ->event('contact_removed')
                ->log('Удалён контакт');
        }
    }
}
