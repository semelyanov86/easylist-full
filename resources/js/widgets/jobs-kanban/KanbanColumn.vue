<script setup lang="ts">
import type { Job, KanbanColumn } from '@entities/job';
import { Briefcase, Plus } from 'lucide-vue-next';

import KanbanCard from './KanbanCard.vue';

type Props = {
    column: KanbanColumn;
};

defineProps<Props>();

const emit = defineEmits<{
    create: [statusId: number];
    delete: [job: Job];
}>();
</script>

<template>
    <div
        class="flex w-80 shrink-0 flex-col overflow-hidden rounded-xl border border-border bg-muted/30 dark:bg-muted/20"
    >
        <!-- Цветной акцент сверху -->
        <div
            class="h-1 w-full shrink-0"
            :style="{
                backgroundColor: `var(--status-${column.color})`,
            }"
        />

        <!-- Заголовок колонки -->
        <div class="flex items-center gap-2.5 px-3.5 py-3">
            <span
                class="block size-2 shrink-0 rounded-full ring-2 ring-background"
                :style="{
                    backgroundColor: `var(--status-${column.color})`,
                }"
            />
            <h3 class="truncate text-sm font-semibold text-foreground">
                {{ column.title }}
            </h3>
            <span
                class="ml-auto inline-flex size-5 shrink-0 items-center justify-center rounded-md bg-muted text-xs font-medium text-muted-foreground tabular-nums"
            >
                {{ column.jobs.length }}
            </span>
            <button
                type="button"
                class="inline-flex size-5 shrink-0 items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                @click="emit('create', column.statusId)"
            >
                <Plus class="size-3.5" />
            </button>
        </div>

        <!-- Разделитель -->
        <div class="mx-3 border-t border-border" />

        <!-- Список карточек -->
        <div
            class="kanban-list flex min-h-32 flex-1 flex-col gap-2.5 overflow-y-auto p-3"
            :data-status-id="column.statusId"
        >
            <KanbanCard
                v-for="job in column.jobs"
                :key="job.id"
                :job="job"
                @delete="emit('delete', $event)"
            />

            <!-- Пустое состояние -->
            <div
                v-if="column.jobs.length === 0"
                class="flex flex-1 flex-col items-center justify-center gap-2 rounded-lg border border-dashed border-border py-8"
            >
                <Briefcase class="size-5 text-muted-foreground/50" />
                <p class="text-xs text-muted-foreground/70">Нет вакансий</p>
            </div>
        </div>
    </div>
</template>
