<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\Contact\CreateContactAction;
use App\Actions\Contact\DeleteContactAction;
use App\Actions\Contact\GetContactsAction;
use App\Data\ContactData;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContactRequest;
use App\Http\Resources\Api\V1\ContactResource;
use App\Http\Traits\JsonApiResponses;
use App\Models\Contact;
use App\Models\Job;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ContactController extends Controller
{
    use JsonApiResponses;

    /**
     * Получить контакты вакансии.
     */
    public function index(Request $request, Job $job, GetContactsAction $action): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        abort_if($job->user_id !== $user->id, 403);

        $contacts = $action->execute($job);

        $data = array_map(
            fn (ContactData $contact): array => new ContactResource($contact)->toArray($request),
            $contacts,
        );

        return $this->jsonApiList($data);
    }

    /**
     * Прикрепить контакт к вакансии.
     */
    public function store(
        StoreContactRequest $request,
        Job $job,
        CreateContactAction $action,
    ): JsonResponse {
        /** @var \App\Models\User $user */
        $user = $request->user();

        abort_if($job->user_id !== $user->id, 403);

        /** @var array{first_name: string, last_name: string, position?: ?string, city?: ?string, email?: ?string, phone?: ?string, description?: ?string, linkedin_url?: ?string, facebook_url?: ?string, whatsapp_url?: ?string} $data */
        $data = $request->validated();

        $contact = $action->execute($user, $job, $data);

        $contactData = ContactData::from($contact);
        $resource = new ContactResource($contactData);

        return $this->jsonApiCreated($resource->toArray($request));
    }

    /**
     * Удалить контакт из вакансии.
     */
    public function destroy(Request $request, Job $job, Contact $contact, DeleteContactAction $action): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        abort_if($job->user_id !== $user->id, 403);
        abort_if($contact->job_id !== $job->id, 404);

        $action->execute($contact);

        return $this->jsonApiNoContent();
    }
}
