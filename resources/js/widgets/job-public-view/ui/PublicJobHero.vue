<script setup lang="ts">
import type { JobPublicView } from '@entities/job';
import { Badge } from '@shared/ui/badge';
import { Building2, Calendar, ExternalLink, MapPin } from 'lucide-vue-next';

type Props = {
    job: JobPublicView;
};

defineProps<Props>();

const formatSalary = (salary: number, currencySymbol: string): string => {
    return new Intl.NumberFormat('ru-RU').format(salary) + ' ' + currencySymbol;
};

const formatDate = (dateString: string): string => {
    return new Date(dateString).toLocaleDateString('ru-RU', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
};
</script>

<template>
    <div class="border-b border-border bg-card">
        <div class="mx-auto max-w-4xl px-6 py-8 sm:py-10">
            <h1
                class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl"
            >
                {{ job.title }}
            </h1>

            <div
                class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-muted-foreground"
            >
                <span
                    class="inline-flex items-center gap-1.5 font-medium text-foreground/80"
                >
                    <Building2 class="size-4 shrink-0 opacity-60" />
                    {{ job.company_name }}
                </span>

                <span
                    v-if="job.location_city"
                    class="inline-flex items-center gap-1.5"
                >
                    <MapPin class="size-3.5 shrink-0 opacity-60" />
                    {{ job.location_city }}
                </span>

                <span class="inline-flex items-center gap-1.5">
                    <Calendar class="size-3.5 shrink-0 opacity-60" />
                    {{ formatDate(job.created_at) }}
                </span>
            </div>

            <div class="mt-4 flex flex-wrap items-center gap-3">
                <div
                    v-if="job.salary"
                    class="inline-flex items-center rounded-lg bg-primary px-4 py-2 text-lg font-bold text-primary-foreground"
                >
                    {{ formatSalary(job.salary, job.currency_symbol ?? '₽') }}
                </div>

                <a
                    v-if="job.job_url"
                    :href="job.job_url"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-border px-4 py-2 text-sm font-medium text-muted-foreground transition hover:bg-muted hover:text-foreground"
                >
                    <ExternalLink class="size-4 shrink-0" />
                    Открыть вакансию
                </a>
            </div>

            <div
                v-if="job.skills.length > 0"
                class="mt-5 flex flex-wrap gap-1.5"
            >
                <Badge
                    v-for="skill in job.skills"
                    :key="skill.id"
                    variant="outline"
                    class="px-2.5 py-1 text-xs"
                >
                    {{ skill.title }}
                </Badge>
            </div>
        </div>
    </div>
</template>
