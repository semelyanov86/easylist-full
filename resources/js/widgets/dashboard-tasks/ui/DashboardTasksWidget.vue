<script setup lang="ts">
import type { DashboardPendingTask } from '@entities/job-task';
import { Link, router } from '@inertiajs/vue3';
import { Checkbox } from '@shared/ui/checkbox';
import { Calendar, CheckCircle2, ListTodo } from 'lucide-vue-next';

import { toggle } from '@/routes/job-tasks';
import { show } from '@/routes/jobs';

type Props = {
    tasks: DashboardPendingTask[];
};

defineProps<Props>();

const onToggle = (taskId: number): void => {
    router.patch(
        toggle.url(taskId),
        {},
        {
            preserveScroll: true,
            preserveState: true,
        },
    );
};

const formatDeadline = (deadline: string): string => {
    const date = new Date(deadline);
    return date.toLocaleDateString('ru-RU', {
        day: 'numeric',
        month: 'short',
    });
};

const isOverdue = (deadline: string): boolean => {
    return new Date(deadline) < new Date();
};

const isSoon = (deadline: string): boolean => {
    const date = new Date(deadline);
    const now = new Date();
    const diffDays = (date.getTime() - now.getTime()) / 86400000;
    return diffDays >= 0 && diffDays <= 3;
};
</script>

<template>
    <div
        class="overflow-hidden rounded-lg border border-border bg-card shadow-sm"
    >
        <div
            class="flex items-center justify-between border-b border-border px-3 py-2"
        >
            <div class="flex items-center gap-1.5">
                <ListTodo class="size-3.5 text-muted-foreground" />
                <h3 class="text-xs font-semibold text-foreground">
                    Предстоящие задачи
                </h3>
            </div>
            <span
                v-if="tasks.length > 0"
                class="rounded bg-muted px-1.5 py-px text-xs font-medium text-muted-foreground tabular-nums"
            >
                {{ tasks.length }}
            </span>
        </div>

        <div v-if="tasks.length > 0" class="max-h-80 overflow-y-auto p-2">
            <div class="space-y-px">
                <div
                    v-for="task in tasks"
                    :key="task.id"
                    class="group flex gap-2 rounded-md px-1.5 py-1 transition-colors hover:bg-muted/40"
                >
                    <div class="mt-0.5 shrink-0">
                        <Checkbox
                            :checked="false"
                            @update:checked="onToggle(task.id)"
                        />
                    </div>

                    <div class="min-w-0 flex-1">
                        <span
                            class="text-xs leading-tight font-medium text-foreground"
                        >
                            {{ task.title }}
                        </span>

                        <div
                            class="mt-0.5 flex items-center gap-1 text-xs leading-tight text-muted-foreground/50 transition-colors group-hover:text-muted-foreground/70"
                        >
                            <Link
                                :href="show.url(task.job_id)"
                                class="truncate hover:text-foreground hover:underline"
                            >
                                {{ task.job_title }}
                                <span v-if="task.job_company_name">
                                    &middot;
                                    {{ task.job_company_name }}
                                </span>
                            </Link>

                            <template v-if="task.deadline">
                                <span class="text-muted-foreground/20">
                                    &middot;
                                </span>
                                <span
                                    class="inline-flex shrink-0 items-center gap-0.5"
                                    :class="
                                        isOverdue(task.deadline)
                                            ? 'font-medium text-destructive'
                                            : isSoon(task.deadline)
                                              ? 'font-medium text-status-amber'
                                              : ''
                                    "
                                >
                                    <Calendar class="size-2.5" />
                                    {{ formatDeadline(task.deadline) }}
                                </span>
                            </template>
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
                class="flex size-8 items-center justify-center rounded-full bg-muted/60"
            >
                <CheckCircle2 class="size-4 text-muted-foreground/40" />
            </div>
            <div class="text-center">
                <p class="text-xs font-medium text-muted-foreground/70">
                    Нет предстоящих задач
                </p>
                <p class="mt-0.5 text-xs text-muted-foreground/50">
                    Задачи из вакансий появятся здесь
                </p>
            </div>
        </div>
    </div>
</template>
