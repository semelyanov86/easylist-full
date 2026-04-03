<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Traits\JsonApiResponses;
use App\Models\Job;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Jobs\FindContactsJob;
use App\Models\User;

/**
 * Запуск фонового поиска контактов через ИИ.
 */
final class ContactFinderController extends Controller
{
    use JsonApiResponses;

    public function __invoke(Request $request, Job $job): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        abort_if($job->user_id !== $user->id, 403);
        abort_if(! $user->is_premium, 403, 'Данная функция доступна только для Premium аккаунтов');

        dispatch(new FindContactsJob($user->id, $job->id));

        return $this->jsonApiNoContent();
    }
}
