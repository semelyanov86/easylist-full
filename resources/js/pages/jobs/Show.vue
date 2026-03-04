<script setup lang="ts">
import type {
    JobDetail,
    JobShowTab,
    JobShowTabId,
    StatusTab,
} from '@entities/job';
import type { JobCategory } from '@entities/job-category';
import type { Skill } from '@entities/skill';
import { EditJobDialog } from '@features/job/edit';
import { StatusPipeline } from '@features/job-status-pipeline';
import { Head } from '@inertiajs/vue3';
import type { BreadcrumbItem } from '@shared/types';
import { AppLayout } from '@widgets/app-shell';
import {
    JobCommentsContent,
    JobCompanyContent,
    JobContactsContent,
    JobDocumentsContent,
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
const activeTab = ref<JobShowTabId>('overview');

const tabs: JobShowTab[] = [
    { id: 'overview', title: 'Общий обзор', enabled: true },
    { id: 'comments', title: 'Комментарии', enabled: true },
    { id: 'documents', title: 'Документы', enabled: true },
    { id: 'company', title: 'Компания', enabled: true },
    { id: 'contacts', title: 'Контакты', enabled: true },
    { id: 'tasks', title: 'Задачи', enabled: false },
];

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

            <JobShowTabs v-model="activeTab" :tabs="tabs" />

            <JobOverviewContent v-if="activeTab === 'overview'" :job="job" />
            <JobCommentsContent v-if="activeTab === 'comments'" :job="job" />
            <JobDocumentsContent v-if="activeTab === 'documents'" :job="job" />
            <JobCompanyContent v-if="activeTab === 'company'" :job="job" />
            <JobContactsContent v-if="activeTab === 'contacts'" :job="job" />
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
