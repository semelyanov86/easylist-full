<script setup lang="ts">
import type { JobDetail } from '@entities/job';
import { AddCommentForm } from '@features/job-comment/add';
import { Badge } from '@shared/ui/badge';
import DOMPurify from 'dompurify';
import { FileText, MessageSquare, Tag } from 'lucide-vue-next';
import { marked } from 'marked';
import { computed } from 'vue';

type Props = {
    job: JobDetail;
};

const props = defineProps<Props>();

const descriptionHtml = computed((): string => {
    if (!props.job.description) {
        return '';
    }

    const raw = marked.parse(props.job.description);
    const html = typeof raw === 'string' ? raw : '';

    return DOMPurify.sanitize(html);
});

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
        <div class="space-y-6 lg:col-span-2">
            <div
                v-if="job.description"
                class="overflow-hidden rounded-xl border border-border bg-card shadow-sm"
            >
                <div
                    class="flex items-center gap-2 border-b border-border px-5 py-3"
                >
                    <FileText class="size-4 text-muted-foreground" />
                    <h3 class="text-sm font-semibold text-foreground">
                        Описание
                    </h3>
                </div>
                <div
                    class="job-description p-5 text-sm leading-relaxed text-muted-foreground"
                    v-html="descriptionHtml"
                />
            </div>

            <div
                v-if="job.skills.length > 0"
                class="overflow-hidden rounded-xl border border-border bg-card shadow-sm"
            >
                <div
                    class="flex items-center gap-2 border-b border-border px-5 py-3"
                >
                    <Tag class="size-4 text-muted-foreground" />
                    <h3 class="text-sm font-semibold text-foreground">
                        Навыки
                    </h3>
                    <span class="text-xs text-muted-foreground">
                        {{ job.skills.length }}
                    </span>
                </div>
                <div class="flex flex-wrap gap-2 p-5">
                    <Badge
                        v-for="skill in job.skills"
                        :key="skill.id"
                        variant="outline"
                        class="transition-colors hover:bg-accent"
                    >
                        {{ skill.title }}
                    </Badge>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div
                class="overflow-hidden rounded-xl border border-border bg-card shadow-sm"
            >
                <div
                    class="flex items-center gap-2 border-b border-border px-5 py-3"
                >
                    <MessageSquare class="size-4 text-muted-foreground" />
                    <h3 class="text-sm font-semibold text-foreground">
                        Комментарии
                    </h3>
                    <span
                        v-if="job.comments.length > 0"
                        class="flex size-5 items-center justify-center rounded-full bg-muted text-xs font-medium text-muted-foreground"
                    >
                        {{ job.comments.length }}
                    </span>
                </div>

                <div class="p-5">
                    <AddCommentForm :job-id="job.id" />

                    <div v-if="job.comments.length > 0" class="mt-5 space-y-4">
                        <div
                            v-for="comment in job.comments"
                            :key="comment.id"
                            class="flex gap-3"
                        >
                            <div
                                class="flex size-8 shrink-0 items-center justify-center rounded-full bg-accent text-xs font-semibold text-accent-foreground"
                            >
                                {{ getInitials(comment.author_name) }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-baseline gap-2">
                                    <span
                                        class="text-xs font-semibold text-foreground"
                                    >
                                        {{ comment.author_name }}
                                    </span>
                                    <span
                                        class="text-xs text-muted-foreground/70"
                                    >
                                        {{
                                            formatRelativeDate(
                                                comment.created_at,
                                            )
                                        }}
                                    </span>
                                </div>
                                <div
                                    class="mt-1.5 rounded-lg rounded-tl-none bg-muted/50 px-3 py-2 text-sm text-foreground/80"
                                >
                                    <p class="whitespace-pre-line">
                                        {{ comment.body }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        v-else
                        class="mt-5 flex flex-col items-center gap-2 py-6 text-center"
                    >
                        <div
                            class="flex size-10 items-center justify-center rounded-full bg-muted"
                        >
                            <MessageSquare
                                class="size-5 text-muted-foreground/50"
                            />
                        </div>
                        <p class="text-xs text-muted-foreground">
                            Комментариев пока нет
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.job-description :deep(h1),
.job-description :deep(h2),
.job-description :deep(h3),
.job-description :deep(h4),
.job-description :deep(h5),
.job-description :deep(h6) {
    font-weight: 600;
    color: var(--foreground);
    margin-top: 1.25em;
    margin-bottom: 0.5em;
}

.job-description :deep(h1) {
    font-size: 1.25rem;
}

.job-description :deep(h2) {
    font-size: 1.125rem;
}

.job-description :deep(h3) {
    font-size: 1rem;
}

.job-description :deep(h1:first-child),
.job-description :deep(h2:first-child),
.job-description :deep(h3:first-child) {
    margin-top: 0;
}

.job-description :deep(p) {
    margin-top: 0.5em;
    margin-bottom: 0.5em;
}

.job-description :deep(ul),
.job-description :deep(ol) {
    margin-top: 0.5em;
    margin-bottom: 0.5em;
    padding-left: 1.5em;
}

.job-description :deep(ul) {
    list-style-type: disc;
}

.job-description :deep(ol) {
    list-style-type: decimal;
}

.job-description :deep(li) {
    margin-top: 0.25em;
    margin-bottom: 0.25em;
}

.job-description :deep(a) {
    color: var(--primary);
    text-decoration: underline;
    text-underline-offset: 2px;
}

.job-description :deep(a:hover) {
    opacity: 0.8;
}

.job-description :deep(strong) {
    font-weight: 600;
    color: var(--foreground);
}

.job-description :deep(em) {
    font-style: italic;
}

.job-description :deep(code) {
    font-size: 0.85em;
    padding: 0.15em 0.35em;
    border-radius: 0.25rem;
    background-color: var(--muted);
    color: var(--foreground);
}

.job-description :deep(pre) {
    margin-top: 0.75em;
    margin-bottom: 0.75em;
    padding: 0.75em 1em;
    border-radius: 0.375rem;
    background-color: var(--muted);
    overflow-x: auto;
}

.job-description :deep(pre code) {
    padding: 0;
    background-color: transparent;
}

.job-description :deep(blockquote) {
    margin-top: 0.75em;
    margin-bottom: 0.75em;
    padding-left: 1em;
    border-left: 3px solid var(--border);
    color: var(--muted-foreground);
    font-style: italic;
}

.job-description :deep(hr) {
    margin-top: 1em;
    margin-bottom: 1em;
    border: none;
    border-top: 1px solid var(--border);
}
</style>
