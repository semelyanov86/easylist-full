<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Ai\FindContactsAction;
use App\Exceptions\AiFormatterException;
use App\Models\Job;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Models\User;

final class AiContactFinderController extends Controller
{
    /**
     * Найти контакты компании через ИИ.
     */
    public function __invoke(Request $request, Job $job, FindContactsAction $action): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($job->user_id !== $user->id) {
            abort(403);
        }

        if (! $user->is_premium) {
            abort(403, 'Данная функция доступна только для Premium аккаунтов');
        }

        try {
            $action->execute($user, $job);

            return back();
        } catch (AiFormatterException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
