<script setup lang="ts">
import type { DashboardActivityItem } from '@entities/activity';
import { Link } from '@inertiajs/vue3';
import { formatRelativeDate } from '@shared/lib/format';
import {
    ArrowRight,
    ArrowRightLeft,
    Clock,
    FileText,
    MessageSquare,
    Pencil,
    Plus,
    Trash2,
} from 'lucide-vue-next';
import type { Component } from 'vue';

import { show } from '@/routes/jobs';

type Props = {
    activities: DashboardActivityItem[];
};

defineProps<Props>();

type EventStyle = {
    icon: Component;
    nodeClass: string;
    iconClass: string;
};

const eventStyles: Record<string, EventStyle> = {
    created: {
        icon: Plus,
        nodeClass: 'bg-status-green/15 ring-status-green/25',
        iconClass: 'text-status-green',
    },
    updated: {
        icon: Pencil,
        nodeClass: 'bg-status-blue/15 ring-status-blue/25',
        iconClass: 'text-status-blue',
    },
    deleted: {
        icon: Trash2,
        nodeClass: 'bg-destructive/15 ring-destructive/25',
        iconClass: 'text-destructive',
    },
    status_changed: {
        icon: ArrowRightLeft,
        nodeClass: 'bg-status-purple/15 ring-status-purple/25',
        iconClass: 'text-status-purple',
    },
    comment_added: {
        icon: MessageSquare,
        nodeClass: 'bg-status-amber/15 ring-status-amber/25',
        iconClass: 'text-status-amber',
    },
    document_added: {
        icon: FileText,
        nodeClass: 'bg-status-cyan/15 ring-status-cyan/25',
        iconClass: 'text-status-cyan',
    },
    document_removed: {
        icon: Trash2,
        nodeClass: 'bg-destructive/15 ring-destructive/25',
        iconClass: 'text-destructive',
    },
};

const defaultStyle: EventStyle = {
    icon: Clock,
    nodeClass: 'bg-muted ring-border',
    iconClass: 'text-muted-foreground',
};

const getStyle = (event: string | null): EventStyle => {
    return eventStyles[event ?? ''] ?? defaultStyle;
};

const getStatusChange = (
    properties: Record<string, unknown>,
): { oldStatus: string; newStatus: string } | null => {
    if (
        typeof properties.old_status === 'string' &&
        typeof properties.new_status === 'string'
    ) {
        return {
            oldStatus: properties.old_status,
            newStatus: properties.new_status,
        };
    }

    return null;
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
                <Clock class="size-3.5 text-muted-foreground" />
                <h3 class="text-xs font-semibold text-foreground">
                    Последние действия
                </h3>
            </div>
            <span
                v-if="activities.length > 0"
                class="rounded bg-muted px-1.5 py-px text-xs font-medium text-muted-foreground tabular-nums"
            >
                {{ activities.length }}
            </span>
        </div>

        <div v-if="activities.length > 0" class="max-h-80 overflow-y-auto p-2">
            <div class="space-y-px">
                <div
                    v-for="activity in activities"
                    :key="activity.id"
                    class="group flex gap-2 rounded-md px-1.5 py-1 transition-colors hover:bg-muted/40"
                >
                    <div
                        class="relative z-10 mt-0.5 flex size-5 shrink-0 items-center justify-center rounded-full ring-1"
                        :class="[getStyle(activity.event).nodeClass]"
                    >
                        <component
                            :is="getStyle(activity.event).icon"
                            class="size-2.5"
                            :class="getStyle(activity.event).iconClass"
                        />
                    </div>

                    <div class="min-w-0 flex-1">
                        <span
                            class="text-xs leading-tight font-medium text-foreground"
                        >
                            {{ activity.description }}
                        </span>

                        <div
                            v-if="
                                activity.event === 'status_changed' &&
                                getStatusChange(activity.properties)
                            "
                            class="mt-1 flex items-center gap-1"
                        >
                            <span
                                class="rounded bg-muted px-1 py-px text-xs text-muted-foreground opacity-70"
                            >
                                {{
                                    getStatusChange(activity.properties)
                                        ?.oldStatus
                                }}
                            </span>
                            <ArrowRight
                                class="size-2.5 shrink-0 text-muted-foreground/50"
                            />
                            <span
                                class="rounded bg-muted px-1 py-px text-xs text-muted-foreground"
                            >
                                {{
                                    getStatusChange(activity.properties)
                                        ?.newStatus
                                }}
                            </span>
                        </div>

                        <div
                            class="mt-0.5 flex items-center gap-1 text-xs leading-tight text-muted-foreground/50 transition-colors group-hover:text-muted-foreground/70"
                        >
                            <Link
                                :href="show.url(activity.job_id)"
                                class="truncate hover:text-foreground hover:underline"
                            >
                                {{ activity.job_title }}
                                <span v-if="activity.job_company_name">
                                    &middot;
                                    {{ activity.job_company_name }}
                                </span>
                            </Link>
                            <span class="text-muted-foreground/20">
                                &middot;
                            </span>
                            <span class="shrink-0">
                                {{ formatRelativeDate(activity.created_at) }}
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
                class="flex size-8 items-center justify-center rounded-full bg-muted/60"
            >
                <Clock class="size-4 text-muted-foreground/40" />
            </div>
            <div class="text-center">
                <p class="text-xs font-medium text-muted-foreground/70">
                    Пока нет записей
                </p>
                <p class="mt-0.5 text-xs text-muted-foreground/50">
                    Действия с вакансиями появятся здесь
                </p>
            </div>
        </div>
    </div>
</template>
