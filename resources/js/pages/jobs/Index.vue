<script setup lang="ts">
import type {
    JobFilters,
    JobsViewMode,
    KanbanColumn,
    PaginatedJobs,
    StatusTab,
} from '@entities/job';
import { JobFiltersBar, JobStatusTabs } from '@features/job-filters';
import { Head } from '@inertiajs/vue3';
import type { BreadcrumbItem } from '@shared/types';
import { Button } from '@shared/ui/button';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@shared/ui/tooltip';
import { AppLayout } from '@widgets/app-shell';
import { KanbanBoard } from '@widgets/jobs-kanban';
import { JobsList, JobsPagination } from '@widgets/jobs-list';
import { Plus } from 'lucide-vue-next';
import { ref } from 'vue';

import { index as jobsIndex } from '@/routes/jobs';

type Props = {
    jobs: PaginatedJobs;
    filters: JobFilters;
    statusTabs: StatusTab[];
    viewMode: JobsViewMode;
    kanbanColumns: KanbanColumn[];
};

const props = defineProps<Props>();

const currentViewMode = ref<JobsViewMode>(props.viewMode);

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Job Tracker',
        href: jobsIndex().url,
    },
];

const totalJobsLabel = (): string => {
    const total = props.jobs.total;
    const mod10 = total % 10;
    const mod100 = total % 100;

    if (mod100 >= 11 && mod100 <= 19) {
        return `${total} вакансий`;
    }
    if (mod10 === 1) {
        return `${total} вакансия`;
    }
    if (mod10 >= 2 && mod10 <= 4) {
        return `${total} вакансии`;
    }
    return `${total} вакансий`;
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems" show-job-categories>
        <Head title="Job Tracker" />

        <div class="flex flex-col gap-5 p-4 md:p-6">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h1
                        class="text-lg font-semibold tracking-tight text-foreground"
                    >
                        Job Tracker
                    </h1>
                    <p class="text-sm text-muted-foreground">
                        {{ totalJobsLabel() }} в отслеживании
                    </p>
                </div>
                <TooltipProvider v-if="currentViewMode === 'table'">
                    <Tooltip>
                        <TooltipTrigger as-child>
                            <Button disabled>
                                <Plus class="size-4" />
                                <span>Создать</span>
                            </Button>
                        </TooltipTrigger>
                        <TooltipContent>Скоро</TooltipContent>
                    </Tooltip>
                </TooltipProvider>
            </div>

            <JobStatusTabs
                :tabs="statusTabs"
                :active-status-id="filters.status_id"
            />

            <JobFiltersBar
                :filters="filters"
                :view-mode="currentViewMode"
                @update:view-mode="currentViewMode = $event"
            />

            <template v-if="currentViewMode === 'table'">
                <JobsList :jobs="jobs.data" />
                <JobsPagination
                    :links="jobs.links"
                    :last-page="jobs.last_page"
                />
            </template>

            <KanbanBoard v-else :columns="kanbanColumns" />
        </div>
    </AppLayout>
</template>
