<?php

declare(strict_types=1);

namespace App\Actions\Contact;

use App\Models\Contact;

final readonly class UpdateContactAction
{
    /**
     * @param  array{first_name?: string, last_name?: string, position?: ?string, city?: ?string, email?: ?string, phone?: ?string, description?: ?string, linkedin_url?: ?string, facebook_url?: ?string, whatsapp_url?: ?string}  $data
     */
    public function execute(Contact $contact, array $data): void
    {
        $contact->update($data);
    }
}
