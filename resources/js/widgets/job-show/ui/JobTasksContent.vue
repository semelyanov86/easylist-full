<script setup lang="ts">
import type { JobDetail } from '@entities/job';
import type { JobTask } from '@entities/job-task';
import { AddTaskDialog } from '@features/job-task/add';
import { EditTaskDialog } from '@features/job-task/edit';
import { router } from '@inertiajs/vue3';
import { Button } from '@shared/ui/button';
import {
    Check,
    ChevronDown,
    CircleDashed,
    ListTodo,
    Plus,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

import JobTaskController from '@/actions/App/Http/Controllers/JobTaskController';

import TaskRow from './TaskRow.vue';
import TaskSummaryCard from './TaskSummaryCard.vue';

type Props = {
    job: JobDetail;
};

const props = defineProps<Props>();

const showAddDialog = ref(false);
const editingTask = ref<JobTask | null>(null);
const showCompleted = ref(false);

const activeTasks = computed(() =>
    props.job.tasks.filter((t) => t.completed_at === null),
);

const completedTasks = computed(() =>
    props.job.tasks.filter((t) => t.completed_at !== null),
);

const progressPercent = computed(() => {
    if (props.job.tasks.length === 0) {
        return 0;
    }
    return Math.round(
        (completedTasks.value.length / props.job.tasks.length) * 100,
    );
});

const overdueCount = computed(
    () =>
        activeTasks.value.filter(
            (t) => t.deadline && new Date(t.deadline) < new Date(),
        ).length,
);

const toggleTask = (task: JobTask): void => {
    router.patch(
        JobTaskController.toggle.url(task.id),
        {},
        { preserveScroll: true },
    );
};

const deleteTask = (task: JobTask): void => {
    router.delete(JobTaskController.destroy.url(task.id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <div
                class="flex flex-col rounded-xl border border-border bg-card shadow-sm"
            >
                <!-- Заголовок карточки -->
                <div
                    class="flex items-center justify-between border-b border-border px-5 py-3"
                >
                    <div class="flex items-center gap-2">
                        <ListTodo class="size-4 text-muted-foreground" />
                        <h3 class="text-sm font-semibold text-foreground">
                            Задачи
                        </h3>
                        <span
                            v-if="job.tasks.length > 0"
                            class="rounded-md bg-muted px-2 py-0.5 text-xs font-medium text-muted-foreground tabular-nums"
                        >
                            {{ completedTasks.length }}/{{ job.tasks.length }}
                        </span>
                    </div>
                    <Button size="sm" @click="showAddDialog = true">
                        <Plus class="size-3.5" />
                        <span>Добавить</span>
                    </Button>
                </div>

                <!-- Прогресс-бар -->
                <div
                    v-if="job.tasks.length > 0"
                    class="border-b border-border/50 bg-muted/20 px-5 py-3"
                >
                    <div class="flex min-w-0 flex-1 items-center gap-3">
                        <div
                            class="h-2 flex-1 overflow-hidden rounded-full bg-border/50"
                        >
                            <div
                                class="h-full rounded-full transition-all duration-500 ease-out"
                                :class="
                                    progressPercent === 100
                                        ? 'bg-status-green'
                                        : 'bg-primary'
                                "
                                :style="{ width: `${progressPercent}%` }"
                            />
                        </div>
                        <span
                            class="shrink-0 text-xs font-medium tabular-nums"
                            :class="
                                progressPercent === 100
                                    ? 'text-status-green'
                                    : 'text-muted-foreground'
                            "
                        >
                            {{ progressPercent }}%
                        </span>
                    </div>
                </div>

                <!-- Активные задачи -->
                <div
                    v-if="activeTasks.length > 0"
                    class="divide-y divide-border/50"
                >
                    <TaskRow
                        v-for="task in activeTasks"
                        :key="task.id"
                        :task="task"
                        @toggle="toggleTask"
                        @edit="editingTask = $event"
                        @delete="deleteTask"
                    />
                </div>

                <!-- Выполненные задачи -->
                <div v-if="completedTasks.length > 0">
                    <button
                        type="button"
                        class="flex w-full items-center gap-2 border-t border-border/50 bg-muted/20 px-5 py-2.5 text-xs text-muted-foreground transition-colors hover:bg-muted/40 hover:text-foreground"
                        @click="showCompleted = !showCompleted"
                    >
                        <ChevronDown
                            class="size-3.5 transition-transform duration-200"
                            :class="{ '-rotate-90': !showCompleted }"
                        />
                        <Check class="size-3.5 text-status-green" />
                        <span>Выполненные</span>
                        <span
                            class="rounded-md bg-muted px-1.5 py-0.5 text-xs tabular-nums"
                        >
                            {{ completedTasks.length }}
                        </span>
                    </button>

                    <div v-if="showCompleted" class="divide-y divide-border/30">
                        <TaskRow
                            v-for="task in completedTasks"
                            :key="task.id"
                            :task="task"
                            completed
                            @toggle="toggleTask"
                            @delete="deleteTask"
                        />
                    </div>
                </div>

                <!-- Пустое состояние -->
                <div
                    v-if="job.tasks.length === 0"
                    class="flex flex-1 flex-col items-center justify-center gap-3 px-5 py-16"
                >
                    <div
                        class="flex size-12 items-center justify-center rounded-full bg-muted/60"
                    >
                        <CircleDashed class="size-6 text-muted-foreground/40" />
                    </div>
                    <div class="text-center">
                        <p class="text-sm font-medium text-muted-foreground/70">
                            Задач пока нет
                        </p>
                        <p class="mt-0.5 text-xs text-muted-foreground/50">
                            Создайте задачи для отслеживания шагов по вакансии
                        </p>
                    </div>
                    <Button
                        size="sm"
                        variant="outline"
                        @click="showAddDialog = true"
                    >
                        <ListTodo class="size-3.5" />
                        <span>Создать первую задачу</span>
                    </Button>
                </div>
            </div>
        </div>

        <!-- Боковая панель: сводка -->
        <div v-if="job.tasks.length > 0">
            <TaskSummaryCard
                :total="job.tasks.length"
                :active="activeTasks.length"
                :completed="completedTasks.length"
                :overdue="overdueCount"
            />
        </div>
    </div>

    <AddTaskDialog
        :open="showAddDialog"
        :job-id="job.id"
        @close="showAddDialog = false"
    />
    <EditTaskDialog
        v-if="editingTask"
        :open="editingTask !== null"
        :task="editingTask"
        @close="editingTask = null"
    />
</template>
