<script setup lang="ts">
import { Calendar, Check, Circle, Clock, Target } from 'lucide-vue-next';

type Props = {
    total: number;
    active: number;
    completed: number;
    overdue: number;
};

defineProps<Props>();
</script>

<template>
    <div
        class="overflow-hidden rounded-xl border border-border bg-card shadow-sm"
    >
        <div class="flex items-center gap-2 border-b border-border px-5 py-3">
            <Target class="size-4 text-muted-foreground" />
            <h3 class="text-sm font-semibold text-foreground">Сводка</h3>
        </div>
        <div class="space-y-3 px-5 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <Circle class="size-3.5 text-muted-foreground/50" />
                    <span class="text-sm text-muted-foreground">Всего</span>
                </div>
                <span
                    class="text-sm font-semibold text-foreground tabular-nums"
                >
                    {{ total }}
                </span>
            </div>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <Clock class="size-3.5 text-status-amber" />
                    <span class="text-sm text-muted-foreground">
                        Активные
                    </span>
                </div>
                <span
                    class="text-sm font-semibold text-foreground tabular-nums"
                >
                    {{ active }}
                </span>
            </div>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <Check class="size-3.5 text-status-green" />
                    <span class="text-sm text-muted-foreground">
                        Выполнены
                    </span>
                </div>
                <span
                    class="text-sm font-semibold text-foreground tabular-nums"
                >
                    {{ completed }}
                </span>
            </div>
            <div v-if="overdue > 0" class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <Calendar class="size-3.5 text-destructive" />
                    <span class="text-sm text-destructive">Просрочены</span>
                </div>
                <span
                    class="text-sm font-semibold text-destructive tabular-nums"
                >
                    {{ overdue }}
                </span>
            </div>
        </div>
    </div>
</template>
