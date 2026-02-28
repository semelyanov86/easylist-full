<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Actions\User\UpdateJobsViewModeAction;
use App\Enums\JobsViewMode;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UpdateJobsViewModeRequest;
use Illuminate\Http\RedirectResponse;

final class JobsPreferenceController extends Controller
{
    /**
     * Сохранить предпочтение режима отображения вакансий.
     */
    public function update(UpdateJobsViewModeRequest $request, UpdateJobsViewModeAction $action): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        /** @var string $viewMode */
        $viewMode = $request->validated('view_mode');

        $action->execute($user, JobsViewMode::from($viewMode));

        return back();
    }
}
