<script setup lang="ts">
import type { StatusTab } from '@entities/job';
import { router } from '@inertiajs/vue3';

import { move } from '@/routes/jobs';

type Props = {
    statuses: StatusTab[];
    currentStatusId: number;
    jobId: number;
};

const props = defineProps<Props>();

const handleStatusClick = (statusId: number): void => {
    if (statusId === props.currentStatusId) {
        return;
    }

    router.patch(
        move(props.jobId).url,
        { status_id: statusId },
        {
            preserveScroll: true,
            preserveState: true,
        },
    );
};
</script>

<template>
    <div class="overflow-x-auto">
        <div class="flex items-center">
            <template v-for="(status, index) in statuses" :key="status.id">
                <button
                    type="button"
                    class="group relative flex shrink-0 items-center gap-2 rounded-lg px-3 py-2 text-xs font-medium whitespace-nowrap transition-all duration-200"
                    :class="
                        status.id === currentStatusId
                            ? 'shadow-sm ring-1 ring-inset'
                            : 'text-muted-foreground hover:bg-accent hover:text-foreground'
                    "
                    :style="
                        status.id === currentStatusId
                            ? {
                                  backgroundColor: `color-mix(in srgb, var(--status-${status.color}) 12%, transparent)`,
                                  color: `var(--status-${status.color})`,
                                  '--tw-ring-color': `color-mix(in srgb, var(--status-${status.color}) 30%, transparent)`,
                              }
                            : undefined
                    "
                    @click="handleStatusClick(status.id)"
                >
                    <span
                        class="size-2 shrink-0 rounded-full transition-transform duration-200"
                        :class="
                            status.id === currentStatusId
                                ? 'scale-125'
                                : 'opacity-50 group-hover:opacity-80'
                        "
                        :style="{
                            backgroundColor: `var(--status-${status.color})`,
                        }"
                    />
                    <span>{{ status.title }}</span>
                    <span
                        v-if="status.count > 0"
                        class="tabular-nums opacity-50"
                    >
                        {{ status.count }}
                    </span>
                </button>

                <div
                    v-if="index < statuses.length - 1"
                    class="mx-0.5 h-px w-3 shrink-0 bg-border"
                />
            </template>
        </div>
    </div>
</template>
