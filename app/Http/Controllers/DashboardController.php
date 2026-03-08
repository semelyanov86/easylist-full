<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Dashboard\GetDashboardActivityAction;
use App\Actions\Dashboard\GetDashboardFavoriteJobsAction;
use App\Actions\Dashboard\GetDashboardJobFunnelAction;
use App\Actions\Dashboard\GetDashboardPendingTasksAction;
use App\Actions\Dashboard\GetDashboardRecentJobsAction;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class DashboardController extends Controller
{
    public function __invoke(
        Request $request,
        GetDashboardActivityAction $getActivity,
        GetDashboardPendingTasksAction $getPendingTasks,
        GetDashboardJobFunnelAction $getJobFunnel,
        GetDashboardFavoriteJobsAction $getFavoriteJobs,
        GetDashboardRecentJobsAction $getRecentJobs,
    ): Response {
        /** @var User $user */
        $user = $request->user();

        $funnelCategoryId = $request->integer('funnel_category_id') ?: null;

        return Inertia::render('Dashboard', [
            'recentActivities' => Inertia::defer(
                fn () => $getActivity->execute($user),
            ),
            'pendingTasks' => Inertia::defer(
                fn () => $getPendingTasks->execute($user),
            ),
            'favoriteJobs' => Inertia::defer(
                fn () => $getFavoriteJobs->execute($user),
            ),
            'recentJobs' => Inertia::defer(
                fn () => $getRecentJobs->execute($user),
            ),
            'jobFunnel' => $getJobFunnel->execute($user, $funnelCategoryId),
            'funnelCategoryId' => $funnelCategoryId,
        ]);
    }
}
