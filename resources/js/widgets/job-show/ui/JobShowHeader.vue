<script setup lang="ts">
import type { JobDetail } from '@entities/job';
import { ShareJobDialog } from '@features/job/share';
import { router } from '@inertiajs/vue3';
import { Badge } from '@shared/ui/badge';
import { Button } from '@shared/ui/button';
import {
    Building2,
    Calendar,
    ExternalLink,
    Heart,
    MapPin,
    Pencil,
    Share2,
} from 'lucide-vue-next';
import { ref } from 'vue';

import { toggleFavorite } from '@/routes/jobs';

const showShareDialog = ref(false);

type Props = {
    job: JobDetail;
};

const props = defineProps<Props>();

const emit = defineEmits<{
    edit: [];
}>();

const handleToggleFavorite = (): void => {
    router.patch(
        toggleFavorite(props.job.id).url,
        {},
        {
            preserveScroll: true,
            preserveState: true,
        },
    );
};

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
    <div
        class="overflow-hidden rounded-xl border border-border bg-card shadow-sm"
    >
        <div
            class="h-1"
            :style="{
                backgroundColor: `var(--status-${job.status.color})`,
            }"
        />

        <div class="flex flex-col gap-4 p-5 sm:p-6">
            <div
                class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"
            >
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2.5">
                        <h1
                            class="text-2xl font-bold tracking-tight text-foreground"
                        >
                            {{ job.title }}
                        </h1>
                        <Badge
                            class="shrink-0 border-transparent text-xs font-semibold"
                            :style="{
                                backgroundColor: `color-mix(in srgb, var(--status-${job.status.color}) 15%, transparent)`,
                                color: `var(--status-${job.status.color})`,
                            }"
                        >
                            {{ job.status.title }}
                        </Badge>
                    </div>

                    <div
                        class="mt-3 flex flex-wrap items-center gap-x-1 gap-y-1.5 text-sm text-muted-foreground"
                    >
                        <span
                            class="inline-flex items-center gap-1.5 font-medium text-foreground/80"
                        >
                            <Building2 class="size-4 shrink-0 opacity-60" />
                            {{ job.company_name }}
                        </span>

                        <template v-if="job.location_city">
                            <span class="text-border">·</span>
                            <span class="inline-flex items-center gap-1.5">
                                <MapPin class="size-3.5 shrink-0 opacity-60" />
                                {{ job.location_city }}
                            </span>
                        </template>

                        <template v-if="job.salary">
                            <span class="text-border">·</span>
                            <span class="font-semibold text-foreground">
                                {{
                                    formatSalary(
                                        job.salary,
                                        job.category?.currency_symbol ?? '₽',
                                    )
                                }}
                            </span>
                        </template>

                        <span class="text-border">·</span>
                        <span class="inline-flex items-center gap-1.5">
                            <Calendar class="size-3.5 shrink-0 opacity-60" />
                            {{ formatDate(job.created_at) }}
                        </span>
                    </div>
                </div>

                <div class="flex shrink-0 items-center gap-1.5">
                    <Button
                        variant="ghost"
                        size="icon-sm"
                        :class="
                            job.is_favorite
                                ? 'text-red-500 hover:text-red-600'
                                : 'text-muted-foreground'
                        "
                        @click="handleToggleFavorite"
                    >
                        <Heart
                            class="size-4"
                            :fill="job.is_favorite ? 'currentColor' : 'none'"
                        />
                    </Button>

                    <div class="mx-1 h-5 w-px bg-border" />

                    <Button
                        v-if="job.job_url"
                        variant="outline"
                        size="sm"
                        as="a"
                        :href="job.job_url"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        <ExternalLink class="size-3.5" />
                        <span>Открыть</span>
                    </Button>
                    <Button
                        variant="outline"
                        size="sm"
                        @click="showShareDialog = true"
                    >
                        <Share2 class="size-3.5" />
                        <span>Поделиться</span>
                    </Button>
                    <Button variant="outline" size="sm" @click="emit('edit')">
                        <Pencil class="size-3.5" />
                        <span>Редактировать</span>
                    </Button>
                </div>
            </div>
        </div>

        <ShareJobDialog
            v-model:is-open="showShareDialog"
            :job-id="job.id"
            :uuid="job.uuid"
        />
    </div>
</template>
