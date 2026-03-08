<script setup lang="ts">
import type { DashboardJobItem } from '@entities/job';
import { Link } from '@inertiajs/vue3';
import { Star } from 'lucide-vue-next';

import { show } from '@/routes/jobs';

type Props = {
    jobs: DashboardJobItem[];
};

defineProps<Props>();
</script>

<template>
    <div
        class="overflow-hidden rounded-lg border border-border bg-card shadow-sm"
    >
        <div
            class="flex items-center justify-between border-b border-border px-3 py-2"
        >
            <div class="flex items-center gap-1.5">
                <Star class="size-3.5 text-status-amber" />
                <h3 class="text-xs font-semibold text-foreground">
                    Избранные вакансии
                </h3>
            </div>
            <span
                v-if="jobs.length > 0"
                class="rounded bg-muted px-1.5 py-px text-xs font-medium text-muted-foreground tabular-nums"
            >
                {{ jobs.length }}
            </span>
        </div>

        <div v-if="jobs.length > 0" class="max-h-80 overflow-y-auto p-2">
            <div class="space-y-px">
                <div
                    v-for="job in jobs"
                    :key="job.id"
                    class="group flex gap-2 rounded-md px-1.5 py-1.5 transition-colors hover:bg-muted/40"
                >
                    <div
                        class="mt-px flex size-5 shrink-0 items-center justify-center rounded-full bg-status-amber/10 ring-1 ring-status-amber/20"
                    >
                        <Star class="size-2.5 text-status-amber" />
                    </div>

                    <div class="min-w-0 flex-1">
                        <Link
                            :href="show.url(job.id)"
                            class="text-xs leading-tight font-medium text-foreground hover:underline"
                        >
                            {{ job.title }}
                        </Link>

                        <div
                            class="mt-1 flex items-center gap-1.5 text-xs leading-tight"
                        >
                            <span
                                class="truncate text-muted-foreground/50 transition-colors group-hover:text-muted-foreground/70"
                            >
                                {{ job.company_name }}
                            </span>

                            <span
                                class="inline-flex shrink-0 items-center gap-1 rounded-full bg-muted/80 px-1.5 py-px text-muted-foreground transition-colors group-hover:bg-muted"
                            >
                                <span
                                    class="inline-block size-1.5 rounded-full"
                                    :class="`bg-status-${job.status_color}`"
                                />
                                {{ job.status_title }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div
            v-else
            class="flex flex-col items-center gap-2 px-4 py-8 text-center"
        >
            <div
                class="flex size-8 items-center justify-center rounded-full bg-status-amber/10"
            >
                <Star class="size-4 text-status-amber/40" />
            </div>
            <div class="text-center">
                <p class="text-xs font-medium text-muted-foreground/70">
                    Нет избранных вакансий
                </p>
                <p class="mt-0.5 text-xs text-muted-foreground/50">
                    Отмечайте вакансии звёздочкой для быстрого доступа
                </p>
            </div>
        </div>
    </div>
</template>
