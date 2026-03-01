<script setup lang="ts">
import type { ActivityTimelineItem } from '@entities/activity';
import { Badge } from '@shared/ui/badge';
import {
    ArrowRight,
    ArrowRightLeft,
    Clock,
    MessageSquare,
    Pencil,
    Plus,
    Trash2,
} from 'lucide-vue-next';
import type { Component } from 'vue';

type Props = {
    activities: ActivityTimelineItem[];
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
};

const defaultStyle: EventStyle = {
    icon: Clock,
    nodeClass: 'bg-muted ring-border',
    iconClass: 'text-muted-foreground',
};

const getStyle = (event: string | null): EventStyle => {
    return eventStyles[event ?? ''] ?? defaultStyle;
};

const formatRelativeDate = (dateString: string): string => {
    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now.getTime() - date.getTime();
    const diffMin = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);

    if (diffMin < 1) {
        return 'только что';
    }
    if (diffMin < 60) {
        return `${diffMin} мин. назад`;
    }
    if (diffHours < 24) {
        return `${diffHours} ч. назад`;
    }
    if (diffDays < 7) {
        return `${diffDays} дн. назад`;
    }

    return new Date(dateString).toLocaleDateString('ru-RU', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
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

const getCommentText = (properties: Record<string, unknown>): string | null => {
    if (typeof properties.comment_body === 'string') {
        return properties.comment_body;
    }

    return null;
};

const getChangedFields = (
    changes: Record<string, unknown>,
): string[] | null => {
    const attributes = changes.attributes as
        | Record<string, unknown>
        | undefined;
    if (!attributes) {
        return null;
    }

    const fields = Object.keys(attributes);

    return fields.length > 0 ? fields : null;
};

const fieldLabels: Record<string, string> = {
    title: 'Название',
    company_name: 'Компания',
    description: 'Описание',
    location_city: 'Город',
    salary: 'Зарплата',
    job_url: 'Ссылка',
    job_status_id: 'Статус',
    job_category_id: 'Категория',
};

const getFieldLabel = (field: string): string => {
    return fieldLabels[field] ?? field;
};
</script>

<template>
    <div
        class="overflow-hidden rounded-xl border border-border bg-card shadow-sm"
    >
        <div
            class="flex items-center justify-between border-b border-border px-5 py-3"
        >
            <div class="flex items-center gap-2">
                <Clock class="size-4 text-muted-foreground" />
                <h3 class="text-sm font-semibold text-foreground">История</h3>
            </div>
            <span
                v-if="activities.length > 0"
                class="rounded-md bg-muted px-2 py-0.5 text-xs font-medium text-muted-foreground tabular-nums"
            >
                {{ activities.length }}
            </span>
        </div>

        <div v-if="activities.length > 0" class="p-4">
            <div class="relative">
                <div class="absolute top-4 bottom-4 left-3.5 w-px bg-border" />

                <div class="space-y-0.5">
                    <div
                        v-for="activity in activities"
                        :key="activity.id"
                        class="group relative flex gap-3 rounded-lg p-1.5 transition-colors hover:bg-muted/40"
                    >
                        <div
                            class="relative z-10 mt-0.5 flex size-7 shrink-0 items-center justify-center rounded-full ring-1"
                            :class="[getStyle(activity.event).nodeClass]"
                        >
                            <component
                                :is="getStyle(activity.event).icon"
                                class="size-3.5"
                                :class="getStyle(activity.event).iconClass"
                            />
                        </div>

                        <div class="min-w-0 flex-1 pt-0.5">
                            <div
                                class="flex items-center justify-between gap-2"
                            >
                                <span
                                    class="text-xs font-medium text-foreground"
                                >
                                    {{ activity.description }}
                                </span>
                            </div>

                            <div
                                v-if="
                                    activity.event === 'status_changed' &&
                                    getStatusChange(activity.properties)
                                "
                                class="mt-1.5 flex items-center gap-1.5"
                            >
                                <Badge
                                    variant="secondary"
                                    class="text-xs opacity-70"
                                >
                                    {{
                                        getStatusChange(activity.properties)
                                            ?.oldStatus
                                    }}
                                </Badge>
                                <ArrowRight
                                    class="size-3 shrink-0 text-muted-foreground/50"
                                />
                                <Badge variant="secondary" class="text-xs">
                                    {{
                                        getStatusChange(activity.properties)
                                            ?.newStatus
                                    }}
                                </Badge>
                            </div>

                            <div
                                v-if="
                                    activity.event === 'comment_added' &&
                                    getCommentText(activity.properties)
                                "
                                class="mt-1.5 truncate border-l-2 border-status-amber/30 pl-2.5 text-xs text-muted-foreground italic"
                            >
                                {{ getCommentText(activity.properties) }}
                            </div>

                            <div
                                v-if="
                                    activity.event === 'updated' &&
                                    getChangedFields(activity.changes)
                                "
                                class="mt-1.5 flex flex-wrap gap-1"
                            >
                                <span
                                    v-for="field in getChangedFields(
                                        activity.changes,
                                    )"
                                    :key="field"
                                    class="rounded bg-status-blue/10 px-1.5 py-px text-xs text-status-blue"
                                >
                                    {{ getFieldLabel(field) }}
                                </span>
                            </div>

                            <div
                                class="mt-1 flex items-center gap-1.5 text-xs text-muted-foreground/50 transition-colors group-hover:text-muted-foreground/70"
                            >
                                <span v-if="activity.causer_name">
                                    {{ activity.causer_name }}
                                </span>
                                <span
                                    v-if="activity.causer_name"
                                    class="text-muted-foreground/20"
                                >
                                    &middot;
                                </span>
                                <span>
                                    {{
                                        formatRelativeDate(activity.created_at)
                                    }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div
            v-else
            class="flex flex-col items-center gap-3 px-5 py-12 text-center"
        >
            <div
                class="flex size-12 items-center justify-center rounded-full bg-muted/60"
            >
                <Clock class="size-6 text-muted-foreground/40" />
            </div>
            <div class="text-center">
                <p class="text-sm font-medium text-muted-foreground/70">
                    Пока нет записей
                </p>
                <p class="mt-0.5 text-xs text-muted-foreground/50">
                    Действия с вакансией появятся здесь
                </p>
            </div>
        </div>
    </div>
</template>
