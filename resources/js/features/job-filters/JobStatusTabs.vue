<script setup lang="ts">
import type { StatusTab } from '@entities/job';
import { router } from '@inertiajs/vue3';

import { index as jobsIndex } from '@/routes/jobs';

type Props = {
    tabs: StatusTab[];
    activeStatusId: number | null;
};

const props = defineProps<Props>();

const selectStatus = (statusId: number | null): void => {
    router.get(
        jobsIndex().url,
        { status_id: statusId ?? undefined },
        { preserveState: true, preserveScroll: true },
    );
};

const totalCount = (): number => {
    return props.tabs.reduce((sum, tab) => sum + tab.count, 0);
};
</script>

<template>
    <div class="-mx-4 overflow-x-auto px-4 md:-mx-0 md:px-0">
        <div class="flex items-center gap-1.5">
            <button
                type="button"
                class="inline-flex shrink-0 items-center gap-1.5 rounded-md px-2.5 py-1.5 text-xs font-medium transition-colors"
                :class="
                    activeStatusId === null
                        ? 'bg-primary text-primary-foreground'
                        : 'text-muted-foreground hover:bg-accent hover:text-foreground'
                "
                @click="selectStatus(null)"
            >
                <span>Все</span>
                <span
                    class="tabular-nums"
                    :class="
                        activeStatusId === null
                            ? 'text-primary-foreground/70'
                            : 'text-muted-foreground'
                    "
                >
                    {{ totalCount() }}
                </span>
            </button>

            <button
                v-for="tab in tabs"
                :key="tab.id"
                type="button"
                class="inline-flex shrink-0 items-center gap-1.5 rounded-md px-2.5 py-1.5 text-xs font-medium transition-colors"
                :class="
                    activeStatusId === tab.id
                        ? 'text-white'
                        : 'text-muted-foreground hover:bg-accent hover:text-foreground'
                "
                :style="
                    activeStatusId === tab.id
                        ? { backgroundColor: `var(--status-${tab.color})` }
                        : {}
                "
                @click="selectStatus(tab.id)"
            >
                <span
                    v-if="activeStatusId !== tab.id"
                    class="size-1.5 rounded-full"
                    :style="{
                        backgroundColor: `var(--status-${tab.color})`,
                    }"
                />
                <span>{{ tab.title }}</span>
                <span
                    class="tabular-nums"
                    :class="
                        activeStatusId === tab.id
                            ? 'text-white/70'
                            : 'text-muted-foreground'
                    "
                >
                    {{ tab.count }}
                </span>
            </button>
        </div>
    </div>
</template>
