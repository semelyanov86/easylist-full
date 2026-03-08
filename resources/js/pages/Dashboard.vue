<script setup lang="ts">
import type { DashboardActivityItem } from '@entities/activity';
import type {
    DashboardJobItem,
    DashboardResponsePoint,
    StatusTab,
} from '@entities/job';
import type { DashboardPendingTask } from '@entities/job-task';
import type { DashboardSkillDemand } from '@entities/skill';
import { CreateListDialog } from '@features/shopping-list/create';
import { Deferred, Head } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@shared/types';
import { Button } from '@shared/ui/button';
import { AppLayout } from '@widgets/app-shell';
import {
    DashboardActivitySkeleton,
    DashboardActivityWidget,
} from '@widgets/dashboard-activity';
import {
    DashboardFavoritesSkeleton,
    DashboardFavoritesWidget,
} from '@widgets/dashboard-favorites';
import { DashboardFunnelWidget } from '@widgets/dashboard-funnel';
import {
    DashboardRecentJobsSkeleton,
    DashboardRecentJobsWidget,
} from '@widgets/dashboard-recent-jobs';
import {
    DashboardResponseDynamicsSkeleton,
    DashboardResponseDynamicsWidget,
} from '@widgets/dashboard-response-dynamics';
import {
    DashboardSkillsDemandSkeleton,
    DashboardSkillsDemandWidget,
} from '@widgets/dashboard-skills-demand';
import {
    DashboardTasksSkeleton,
    DashboardTasksWidget,
} from '@widgets/dashboard-tasks';
import { Plus } from 'lucide-vue-next';
import { ref } from 'vue';

import { dashboard } from '@/routes';

defineProps<{
    recentActivities: DashboardActivityItem[];
    pendingTasks: DashboardPendingTask[];
    favoriteJobs: DashboardJobItem[];
    recentJobs: DashboardJobItem[];
    skillsDemand: DashboardSkillDemand[];
    responseDynamics: DashboardResponsePoint[];
    jobFunnel: StatusTab[];
    funnelCategoryId: number | null;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Панель управления',
        href: dashboard().url,
    },
];

const showCreateListDialog = ref(false);
</script>

<template>
    <Head title="Панель управления" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <template #header-actions>
            <Button size="sm">
                <Plus />
                Добавить вакансию
            </Button>
            <Button
                size="sm"
                variant="outline"
                @click="showCreateListDialog = true"
            >
                <Plus />
                Добавить список
            </Button>
        </template>

        <div class="flex flex-col gap-4 p-4 lg:flex-row lg:items-start">
            <div class="flex max-w-3xl min-w-0 flex-1 flex-col gap-4">
                <div class="grid w-full grid-cols-1 gap-4 sm:grid-cols-2">
                    <Deferred data="recentActivities">
                        <template #fallback>
                            <DashboardActivitySkeleton />
                        </template>

                        <DashboardActivityWidget
                            :activities="recentActivities"
                        />
                    </Deferred>

                    <Deferred data="pendingTasks">
                        <template #fallback>
                            <DashboardTasksSkeleton />
                        </template>

                        <DashboardTasksWidget :tasks="pendingTasks" />
                    </Deferred>
                </div>

                <DashboardFunnelWidget
                    :statuses="jobFunnel"
                    :active-category-id="funnelCategoryId"
                />

                <Deferred data="responseDynamics">
                    <template #fallback>
                        <DashboardResponseDynamicsSkeleton />
                    </template>

                    <DashboardResponseDynamicsWidget
                        :points="responseDynamics"
                    />
                </Deferred>
            </div>

            <div
                class="grid w-full grid-cols-1 gap-4 sm:grid-cols-2 lg:w-80 lg:shrink-0 lg:grid-cols-1"
            >
                <Deferred data="favoriteJobs">
                    <template #fallback>
                        <DashboardFavoritesSkeleton />
                    </template>

                    <DashboardFavoritesWidget :jobs="favoriteJobs" />
                </Deferred>

                <Deferred data="recentJobs">
                    <template #fallback>
                        <DashboardRecentJobsSkeleton />
                    </template>

                    <DashboardRecentJobsWidget :jobs="recentJobs" />
                </Deferred>

                <Deferred data="skillsDemand">
                    <template #fallback>
                        <DashboardSkillsDemandSkeleton />
                    </template>

                    <DashboardSkillsDemandWidget :skills="skillsDemand" />
                </Deferred>
            </div>
        </div>
    </AppLayout>

    <CreateListDialog
        :open="showCreateListDialog"
        @close="showCreateListDialog = false"
    />
</template>
