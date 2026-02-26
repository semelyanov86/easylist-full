<script setup lang="ts">
import type { JobStatus } from '@entities/job-status';
import { SettingsLayout } from '@features/settings';
import { Head } from '@inertiajs/vue3';
import type { BreadcrumbItem } from '@shared/types';
import { AppLayout } from '@widgets/app-shell';
import { JobStatusManager } from '@widgets/job-statuses-manager';

import { index } from '@/routes/job-statuses';

type Props = {
    statuses: JobStatus[];
};

defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Статусы откликов',
        href: index().url,
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Статусы откликов" />
        <h1 class="sr-only">Статусы откликов</h1>
        <SettingsLayout>
            <JobStatusManager :statuses="statuses" />
        </SettingsLayout>
    </AppLayout>
</template>
