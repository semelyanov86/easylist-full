<script setup lang="ts">
import type { Job } from '@entities/job';
import { Button } from '@shared/ui/button';
import { Briefcase, Plus } from 'lucide-vue-next';

import JobCard from './JobCard.vue';

type Props = {
    jobs: Job[];
};

defineProps<Props>();

const emit = defineEmits<{
    create: [];
}>();
</script>

<template>
    <div v-if="jobs.length > 0" class="flex flex-col gap-2">
        <JobCard v-for="job in jobs" :key="job.id" :job="job" />
    </div>
    <div
        v-else
        class="flex flex-col items-center justify-center gap-4 rounded-lg border border-dashed border-border py-16 text-center"
    >
        <div
            class="flex size-12 items-center justify-center rounded-full bg-muted"
        >
            <Briefcase class="size-6 text-muted-foreground" />
        </div>
        <div>
            <p class="text-sm font-medium text-foreground">Вакансий пока нет</p>
            <p class="mt-1 text-sm text-muted-foreground">
                Добавьте первую вакансию, чтобы начать отслеживание
            </p>
        </div>
        <Button variant="outline" size="sm" @click="emit('create')">
            <Plus class="size-4" />
            <span>Создать вакансию</span>
        </Button>
    </div>
</template>
