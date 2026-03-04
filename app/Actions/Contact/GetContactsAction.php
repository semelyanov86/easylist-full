<?php

declare(strict_types=1);

namespace App\Actions\Contact;

use App\Data\ContactData;
use App\Models\Contact;
use App\Models\Job;

final readonly class GetContactsAction
{
    /**
     * @return list<ContactData>
     */
    public function execute(Job $job): array
    {
        $contacts = $job->contacts()
            ->latest()
            ->get();

        return array_values($contacts->map(fn (Contact $contact): ContactData => new ContactData(
            id: $contact->id,
            user_id: $contact->user_id,
            first_name: $contact->first_name,
            last_name: $contact->last_name,
            position: $contact->position,
            city: $contact->city,
            email: $contact->email,
            phone: $contact->phone,
            description: $contact->description,
            linkedin_url: $contact->linkedin_url,
            facebook_url: $contact->facebook_url,
            whatsapp_url: $contact->whatsapp_url,
            created_at: $contact->created_at?->toISOString() ?? '',
        ))->all());
    }
}
