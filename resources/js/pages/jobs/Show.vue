<script setup lang="ts">
import type { JobDetail, StatusTab } from '@entities/job';
import type { JobCategory } from '@entities/job-category';
import type { Skill } from '@entities/skill';
import { EditJobDialog } from '@features/job/edit';
import { StatusPipeline } from '@features/job-status-pipeline';
import { Head } from '@inertiajs/vue3';
import type { BreadcrumbItem } from '@shared/types';
import { AppLayout } from '@widgets/app-shell';
import {
    JobOverviewContent,
    JobShowHeader,
    JobShowTabs,
} from '@widgets/job-show';
import { ref } from 'vue';

import { index as jobsIndex } from '@/routes/jobs';

type Props = {
    job: JobDetail;
    statusTabs: StatusTab[];
    categories: JobCategory[];
    skills: Skill[];
};

const props = defineProps<Props>();

const showEditDialog = ref(false);

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Job Tracker',
        href: jobsIndex().url,
    },
    {
        title: props.job.title,
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems" show-job-categories>
        <Head :title="job.title" />

        <div class="flex flex-col gap-6 p-4 md:p-6 lg:p-8">
            <JobShowHeader :job="job" @edit="showEditDialog = true" />

            <StatusPipeline
                :statuses="statusTabs"
                :current-status-id="job.job_status_id"
                :job-id="job.id"
            />

            <JobShowTabs />

            <JobOverviewContent :job="job" />
        </div>

        <EditJobDialog
            :job="showEditDialog ? job : null"
            :statuses="statusTabs"
            :categories="categories"
            :skills="skills"
            @close="showEditDialog = false"
        />
    </AppLayout>
</template>
