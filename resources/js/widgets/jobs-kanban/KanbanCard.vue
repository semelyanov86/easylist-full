<script setup lang="ts">
import type { Job } from '@entities/job';
import { Badge } from '@shared/ui/badge';
import { Building2, Calendar, MapPin } from 'lucide-vue-next';

type Props = {
    job: Job;
};

defineProps<Props>();

const formatSalary = (salary: number): string => {
    return new Intl.NumberFormat('ru-RU').format(salary) + ' \u20BD';
};

const formatDate = (dateString: string): string => {
    return new Date(dateString).toLocaleDateString('ru-RU', {
        day: 'numeric',
        month: 'short',
    });
};
</script>

<template>
    <div
        class="group/card cursor-grab rounded-lg border border-border bg-card p-3 shadow-sm transition-all duration-200 hover:shadow-md active:cursor-grabbing active:shadow-lg"
        :data-job-id="job.id"
    >
        <div class="mb-2 flex items-start justify-between gap-2">
            <h4 class="text-sm leading-snug font-medium text-foreground">
                {{ job.title }}
            </h4>
        </div>

        <div class="flex flex-col gap-1.5 text-xs text-muted-foreground">
            <span class="inline-flex items-center gap-1.5 truncate">
                <Building2 class="size-3 shrink-0" />
                <span class="truncate font-medium text-foreground/80">{{
                    job.company_name
                }}</span>
            </span>

            <span
                v-if="job.location_city"
                class="inline-flex items-center gap-1.5 truncate"
            >
                <MapPin class="size-3 shrink-0" />
                {{ job.location_city }}
            </span>

            <div class="flex items-center justify-between gap-2">
                <span class="inline-flex items-center gap-1.5">
                    <Calendar class="size-3 shrink-0" />
                    {{ formatDate(job.created_at) }}
                </span>

                <span
                    v-if="job.salary"
                    class="shrink-0 text-xs font-semibold text-foreground"
                >
                    {{ formatSalary(job.salary) }}
                </span>
            </div>
        </div>

        <div v-if="job.category" class="mt-2.5 flex items-center gap-1.5">
            <Badge variant="secondary" class="px-1.5 py-0 text-xs leading-5">
                {{ job.category.title }}
            </Badge>
        </div>
    </div>
</template>
