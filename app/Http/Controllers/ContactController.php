<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Contact\CreateContactAction;
use App\Actions\Contact\DeleteContactAction;
use App\Actions\Contact\UpdateContactAction;
use App\Http\Requests\StoreContactRequest;
use App\Http\Requests\UpdateContactRequest;
use App\Models\Contact;
use App\Models\Job;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class ContactController extends Controller
{
    /**
     * Создать контакт к вакансии.
     */
    public function store(StoreContactRequest $request, Job $job, CreateContactAction $action): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        abort_if($job->user_id !== $user->id, 403);

        /** @var array{first_name: string, last_name: string, position?: ?string, city?: ?string, email?: ?string, phone?: ?string, description?: ?string, linkedin_url?: ?string, facebook_url?: ?string, whatsapp_url?: ?string} $data */
        $data = $request->validated();

        $action->execute($user, $job, $data);

        return back();
    }

    /**
     * Обновить контакт.
     */
    public function update(UpdateContactRequest $request, Contact $contact, UpdateContactAction $action): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        abort_if($contact->user_id !== $user->id, 403);

        /** @var array{first_name?: string, last_name?: string, position?: ?string, city?: ?string, email?: ?string, phone?: ?string, description?: ?string, linkedin_url?: ?string, facebook_url?: ?string, whatsapp_url?: ?string} $data */
        $data = $request->validated();

        $action->execute($contact, $data);

        return back();
    }

    /**
     * Удалить контакт.
     */
    public function destroy(Request $request, Contact $contact, DeleteContactAction $action): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        abort_if($contact->user_id !== $user->id, 403);

        $action->execute($contact);

        return back();
    }
}
