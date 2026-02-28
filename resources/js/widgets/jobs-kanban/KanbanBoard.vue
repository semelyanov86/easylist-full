<script setup lang="ts">
import type { Job, KanbanColumn } from '@entities/job';
import { router } from '@inertiajs/vue3';
import Sortable from 'sortablejs';
import { nextTick, onMounted, ref, watch } from 'vue';

import { move } from '@/routes/jobs';

import KanbanColumnVue from './KanbanColumn.vue';

type Props = {
    columns: KanbanColumn[];
};

const props = defineProps<Props>();

const emit = defineEmits<{
    create: [statusId: number];
}>();

const localColumns = ref<KanbanColumn[]>(
    props.columns.map((col) => ({ ...col, jobs: [...col.jobs] })),
);

const boardRef = ref<HTMLElement | null>(null);
const sortableInstances: Sortable[] = [];

watch(
    () => props.columns,
    (value) => {
        localColumns.value = value.map((col) => ({
            ...col,
            jobs: [...col.jobs],
        }));

        nextTick(() => initSortables());
    },
);

const findJobInColumns = (
    jobId: number,
): { columnIndex: number; jobIndex: number; job: Job } | null => {
    for (let ci = 0; ci < localColumns.value.length; ci++) {
        const column = localColumns.value[ci];
        if (!column) {
            continue;
        }
        const ji = column.jobs.findIndex((j) => j.id === jobId);
        if (ji !== -1) {
            const job = column.jobs[ji];
            if (job) {
                return { columnIndex: ci, jobIndex: ji, job };
            }
        }
    }
    return null;
};

const handleDrop = (
    jobId: number,
    targetStatusId: number,
    newIndex: number,
): void => {
    const found = findJobInColumns(jobId);
    if (!found) {
        return;
    }

    const { columnIndex: fromColIdx, jobIndex: fromJobIdx, job } = found;
    const toColIdx = localColumns.value.findIndex(
        (col) => col.statusId === targetStatusId,
    );
    if (toColIdx === -1) {
        return;
    }

    const fromColumn = localColumns.value[fromColIdx];
    const toColumn = localColumns.value[toColIdx];
    if (!fromColumn || !toColumn) {
        return;
    }

    fromColumn.jobs.splice(fromJobIdx, 1);

    const updatedJob: Job = {
        ...job,
        status: { ...job.status, id: targetStatusId },
    };
    toColumn.jobs.splice(newIndex, 0, updatedJob);

    const previousColumns = props.columns.map((col) => ({
        ...col,
        jobs: [...col.jobs],
    }));

    router.patch(
        move(jobId).url,
        { status_id: targetStatusId },
        {
            preserveScroll: true,
            preserveState: true,
            onError: () => {
                localColumns.value = previousColumns;
            },
        },
    );
};

const initSortables = (): void => {
    sortableInstances.forEach((instance) => instance.destroy());
    sortableInstances.length = 0;

    if (!boardRef.value) {
        return;
    }

    const lists = boardRef.value.querySelectorAll<HTMLElement>('.kanban-list');
    lists.forEach((list) => {
        const instance = Sortable.create(list, {
            group: 'kanban',
            animation: 200,
            ghostClass: 'kanban-ghost',
            dragClass: 'kanban-drag',
            chosenClass: 'kanban-chosen',
            onEnd: (event) => {
                const jobIdAttr = event.item.getAttribute('data-job-id');
                const statusIdAttr = event.to.getAttribute('data-status-id');

                if (
                    !jobIdAttr ||
                    !statusIdAttr ||
                    event.newIndex === undefined
                ) {
                    return;
                }

                const jobId = parseInt(jobIdAttr, 10);
                const targetStatusId = parseInt(statusIdAttr, 10);

                handleDrop(jobId, targetStatusId, event.newIndex);
            },
        });
        sortableInstances.push(instance);
    });
};

onMounted(() => {
    initSortables();
});

const totalJobs = (): number => {
    return localColumns.value.reduce((sum, col) => sum + col.jobs.length, 0);
};
</script>

<template>
    <div class="flex flex-col gap-3">
        <!-- Информационная полоска -->
        <div class="flex items-center gap-3 text-xs text-muted-foreground">
            <span>{{ totalJobs() }} на доске</span>
            <span class="text-border">&middot;</span>
            <span>{{ localColumns.length }} колонок</span>
        </div>

        <!-- Доска -->
        <div
            ref="boardRef"
            class="kanban-board -mx-4 flex snap-x snap-mandatory gap-4 overflow-x-auto scroll-smooth px-4 pb-4 md:-mx-6 md:gap-5 md:px-6"
        >
            <KanbanColumnVue
                v-for="column in localColumns"
                :key="column.statusId"
                :column="column"
                class="snap-start"
                @create="emit('create', $event)"
            />
        </div>
    </div>
</template>
