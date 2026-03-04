<script setup lang="ts">
import type { JobTask } from '@entities/job-task';
import {
    Calendar,
    Check,
    MoreHorizontal,
    Pencil,
    Trash2,
} from 'lucide-vue-next';
import { ref } from 'vue';

type Props = {
    task: JobTask;
    completed?: boolean;
};

withDefaults(defineProps<Props>(), {
    completed: false,
});

const emit = defineEmits<{
    toggle: [task: JobTask];
    edit: [task: JobTask];
    delete: [task: JobTask];
}>();

const showMenu = ref(false);

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
        class="group relative flex items-start gap-3.5 px-5 transition-colors"
        :class="
            completed
                ? 'bg-muted/10 py-3 hover:bg-muted/25'
                : 'py-3.5 hover:bg-muted/30'
        "
        @mouseleave="showMenu = false"
    >
        <!-- Чекбокс -->
        <button
            v-if="!completed"
            type="button"
            class="mt-0.5 flex size-5 shrink-0 items-center justify-center rounded-full border-2 border-border transition-all hover:border-status-green hover:bg-status-green/10 dark:hover:bg-status-green/15"
            @click="emit('toggle', task)"
        >
            <Check
                class="size-3 text-transparent transition-colors group-hover:text-status-green"
            />
        </button>
        <button
            v-else
            type="button"
            class="mt-0.5 flex size-5 shrink-0 items-center justify-center rounded-full bg-status-green/15 text-status-green transition-all hover:bg-muted hover:text-muted-foreground dark:bg-status-green/20"
            @click="emit('toggle', task)"
        >
            <Check class="size-3" />
        </button>

        <!-- Название + дедлайн -->
        <div class="min-w-0 flex-1">
            <span
                class="text-sm leading-tight"
                :class="
                    completed
                        ? 'text-muted-foreground line-through decoration-muted-foreground/30'
                        : 'text-foreground'
                "
            >
                {{ task.title }}
            </span>
            <div
                v-if="!completed && task.deadline"
                class="mt-1.5 inline-flex items-center gap-1 rounded-md px-1.5 py-0.5"
                :class="
                    isOverdue(task.deadline)
                        ? 'bg-destructive/8 dark:bg-destructive/12'
                        : isSoon(task.deadline)
                          ? 'bg-status-amber/8 dark:bg-status-amber/12'
                          : 'bg-muted/60'
                "
            >
                <Calendar
                    class="size-3"
                    :class="
                        isOverdue(task.deadline)
                            ? 'text-destructive'
                            : isSoon(task.deadline)
                              ? 'text-status-amber'
                              : 'text-muted-foreground/60'
                    "
                />
                <span
                    class="text-xs"
                    :class="
                        isOverdue(task.deadline)
                            ? 'font-medium text-destructive'
                            : isSoon(task.deadline)
                              ? 'font-medium text-status-amber'
                              : 'text-muted-foreground/60'
                    "
                >
                    {{ formatDeadline(task.deadline) }}
                </span>
            </div>
        </div>

        <!-- Меню действий -->
        <div class="relative shrink-0">
            <button
                type="button"
                class="inline-flex size-7 items-center justify-center rounded-md text-muted-foreground/50 transition-colors hover:bg-muted hover:text-foreground"
                :class="
                    showMenu
                        ? 'bg-muted text-foreground'
                        : 'opacity-0 group-hover:opacity-100'
                "
                @click="showMenu = !showMenu"
            >
                <MoreHorizontal class="size-4" />
            </button>
            <Transition
                enter-active-class="transition duration-100 ease-out"
                enter-from-class="scale-95 opacity-0"
                enter-to-class="scale-100 opacity-100"
                leave-active-class="transition duration-75 ease-in"
                leave-from-class="scale-100 opacity-100"
                leave-to-class="scale-95 opacity-0"
            >
                <div
                    v-if="showMenu"
                    class="absolute top-8 right-0 z-10 w-36 overflow-hidden rounded-lg border border-border bg-card py-1 shadow-lg"
                >
                    <button
                        v-if="!completed"
                        type="button"
                        class="flex w-full items-center gap-2 px-3 py-1.5 text-xs text-foreground transition-colors hover:bg-muted"
                        @click="
                            showMenu = false;
                            emit('edit', task);
                        "
                    >
                        <Pencil class="size-3.5 text-muted-foreground" />
                        Редактировать
                    </button>
                    <button
                        type="button"
                        class="flex w-full items-center gap-2 px-3 py-1.5 text-xs text-destructive transition-colors hover:bg-destructive/8"
                        @click="
                            showMenu = false;
                            emit('delete', task);
                        "
                    >
                        <Trash2 class="size-3.5" />
                        Удалить
                    </button>
                </div>
            </Transition>
        </div>
    </div>
</template>
