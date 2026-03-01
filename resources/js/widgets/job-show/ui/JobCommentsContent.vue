<script setup lang="ts">
import type { JobDetail } from '@entities/job';
import { AddCommentForm } from '@features/job-comment/add';
import { Avatar, AvatarFallback } from '@shared/ui/avatar';
import { MessageSquare } from 'lucide-vue-next';

type Props = {
    job: JobDetail;
};

defineProps<Props>();

const avatarColors: string[] = [
    'bg-primary/15 text-primary',
    'bg-status-blue/15 text-status-blue',
    'bg-status-green/15 text-status-green',
    'bg-status-purple/15 text-status-purple',
    'bg-status-amber/15 text-status-amber',
    'bg-status-pink/15 text-status-pink',
    'bg-status-cyan/15 text-status-cyan',
    'bg-status-indigo/15 text-status-indigo',
];

const getAvatarColor = (userId: number): string => {
    return avatarColors[userId % avatarColors.length] as string;
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

const getInitials = (name: string): string => {
    return name
        .split(' ')
        .slice(0, 2)
        .map((w) => w.charAt(0))
        .join('')
        .toUpperCase();
};
</script>

<template>
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <div
                class="flex flex-col overflow-hidden rounded-xl border border-border bg-card shadow-sm"
            >
                <div
                    class="flex items-center justify-between border-b border-border px-5 py-3"
                >
                    <div class="flex items-center gap-2">
                        <MessageSquare class="size-4 text-muted-foreground" />
                        <h3 class="text-sm font-semibold text-foreground">
                            Обсуждение
                        </h3>
                    </div>
                    <span
                        v-if="job.comments.length > 0"
                        class="rounded-md bg-muted px-2 py-0.5 text-xs font-medium text-muted-foreground tabular-nums"
                    >
                        {{ job.comments.length }}
                        {{
                            job.comments.length === 1
                                ? 'сообщение'
                                : job.comments.length < 5
                                  ? 'сообщения'
                                  : 'сообщений'
                        }}
                    </span>
                </div>

                <div
                    v-if="job.comments.length > 0"
                    class="flex-1 divide-y divide-border/50"
                >
                    <div
                        v-for="(comment, index) in job.comments"
                        :key="comment.id"
                        class="group relative px-5 py-4 transition-colors hover:bg-muted/30"
                        :class="{ 'animate-in fade-in': index === 0 }"
                    >
                        <div class="flex gap-3.5">
                            <Avatar class="size-9 shrink-0">
                                <AvatarFallback
                                    :class="getAvatarColor(comment.user_id)"
                                    class="text-xs font-semibold"
                                >
                                    {{ getInitials(comment.author_name) }}
                                </AvatarFallback>
                            </Avatar>

                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="text-sm font-semibold text-foreground"
                                    >
                                        {{ comment.author_name }}
                                    </span>
                                    <span
                                        class="text-xs text-muted-foreground/60 transition-colors group-hover:text-muted-foreground"
                                    >
                                        {{
                                            formatRelativeDate(
                                                comment.created_at,
                                            )
                                        }}
                                    </span>
                                </div>
                                <p
                                    class="mt-1.5 text-sm leading-relaxed whitespace-pre-line text-foreground/80"
                                >
                                    {{ comment.body }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    v-else
                    class="flex flex-1 flex-col items-center justify-center gap-3 px-5 py-16"
                >
                    <div
                        class="flex size-12 items-center justify-center rounded-full bg-muted/60"
                    >
                        <MessageSquare
                            class="size-6 text-muted-foreground/40"
                        />
                    </div>
                    <div class="text-center">
                        <p class="text-sm font-medium text-muted-foreground/70">
                            Комментариев пока нет
                        </p>
                        <p class="mt-0.5 text-xs text-muted-foreground/50">
                            Напишите первый комментарий ниже
                        </p>
                    </div>
                </div>

                <div class="border-t border-border bg-muted/20 p-4">
                    <AddCommentForm :job-id="job.id" />
                </div>
            </div>
        </div>
    </div>
</template>
