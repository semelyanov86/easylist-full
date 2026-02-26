<script setup lang="ts">
import type { JobStatus } from '@entities/job-status';
import { CreateJobStatusForm } from '@features/job-status/create';
import { DeleteStatusDialog } from '@features/job-status/delete';
import { EditStatusDialog } from '@features/job-status/edit';
import { JobStatusList } from '@features/job-status/reorder';
import Heading from '@shared/components/Heading.vue';
import { ref } from 'vue';

type Props = {
    statuses: JobStatus[];
};

defineProps<Props>();

const statusToEdit = ref<JobStatus | null>(null);
const statusToDelete = ref<JobStatus | null>(null);
</script>

<template>
    <div class="space-y-6">
        <Heading
            variant="small"
            title="Статусы откликов"
            description="Создание и управление статусами для отслеживания откликов на вакансии"
        />
        <CreateJobStatusForm />
        <JobStatusList
            :statuses="statuses"
            @edit="statusToEdit = $event"
            @delete="statusToDelete = $event"
        />
        <EditStatusDialog :status="statusToEdit" @close="statusToEdit = null" />
        <DeleteStatusDialog
            :status="statusToDelete"
            @close="statusToDelete = null"
        />
    </div>
</template>
