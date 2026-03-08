<script setup lang="ts">
import type { DashboardSkillDemand } from '@entities/skill';
import { Flame, Zap } from 'lucide-vue-next';

type Props = {
    skills: DashboardSkillDemand[];
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
                <Flame class="size-3.5 text-status-orange" />
                <h3 class="text-xs font-semibold text-foreground">
                    Навыки в спросе
                </h3>
            </div>
            <span
                v-if="skills.length > 0"
                class="rounded bg-muted px-1.5 py-px text-xs font-medium text-muted-foreground tabular-nums"
            >
                {{ skills.length }}
            </span>
        </div>

        <div v-if="skills.length > 0" class="max-h-80 overflow-y-auto p-2">
            <div class="space-y-px">
                <div
                    v-for="(skill, index) in skills"
                    :key="skill.id"
                    class="group flex items-center gap-2 rounded-md px-1.5 py-1.5 transition-colors hover:bg-muted/40"
                >
                    <span
                        class="flex size-5 shrink-0 items-center justify-center rounded-full text-xs font-semibold tabular-nums"
                        :class="
                            index === 0
                                ? 'bg-status-orange/15 text-status-orange ring-1 ring-status-orange/20'
                                : 'bg-muted text-muted-foreground'
                        "
                    >
                        {{ index + 1 }}
                    </span>

                    <span
                        class="min-w-0 flex-1 truncate text-xs font-medium"
                        :class="
                            index === 0
                                ? 'text-status-orange'
                                : 'text-foreground'
                        "
                    >
                        {{ skill.title }}
                    </span>

                    <span
                        class="inline-flex shrink-0 items-center gap-0.5 rounded-full bg-muted/80 px-1.5 py-px text-xs text-muted-foreground transition-colors group-hover:bg-muted"
                    >
                        <Zap class="size-2.5" />
                        {{ skill.jobs_count }}
                    </span>
                </div>
            </div>
        </div>

        <div
            v-else
            class="flex flex-col items-center gap-2 px-4 py-8 text-center"
        >
            <div
                class="flex size-8 items-center justify-center rounded-full bg-status-orange/10"
            >
                <Flame class="size-4 text-status-orange/40" />
            </div>
            <div class="text-center">
                <p class="text-xs font-medium text-muted-foreground/70">
                    Нет данных по навыкам
                </p>
                <p class="mt-0.5 text-xs text-muted-foreground/50">
                    Добавляйте навыки к вакансиям для аналитики
                </p>
            </div>
        </div>
    </div>
</template>
