<script setup lang="ts">
import type { JobDetail } from '@entities/job';
import { useAiTagExtractor } from '@features/job/model/useAiTagExtractor';
import { Badge } from '@shared/ui/badge';
import DOMPurify from 'dompurify';
import {
    ExternalLink,
    FileText,
    Loader2,
    ScrollText,
    Sparkles,
    Tag,
} from 'lucide-vue-next';
import { marked } from 'marked';
import { computed } from 'vue';

import ActivityTimeline from './ActivityTimeline.vue';

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

const { loading, error, extractTags } = useAiTagExtractor();

function handleExtractTags(): void {
    extractTags(props.job.id);
}
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
                class="overflow-hidden rounded-xl border border-border bg-card shadow-sm"
            >
                <div
                    class="flex items-center gap-2 border-b border-border px-5 py-3"
                >
                    <Tag class="size-4 text-muted-foreground" />
                    <h3 class="text-sm font-semibold text-foreground">
                        Навыки
                    </h3>
                    <span
                        v-if="job.skills.length > 0"
                        class="text-xs text-muted-foreground"
                    >
                        {{ job.skills.length }}
                    </span>
                </div>
                <div
                    v-if="job.skills.length > 0"
                    class="flex flex-wrap gap-2 p-5"
                >
                    <Badge
                        v-for="skill in job.skills"
                        :key="skill.id"
                        variant="outline"
                        class="transition-colors hover:bg-accent"
                    >
                        {{ skill.title }}
                    </Badge>
                </div>
                <div v-else class="p-5">
                    <div
                        class="flex flex-col items-center gap-3 py-4 text-center"
                    >
                        <p class="text-sm text-muted-foreground">
                            Навыки не указаны
                        </p>
                        <button
                            type="button"
                            :disabled="loading"
                            class="inline-flex items-center gap-2 rounded-lg border border-border bg-card px-4 py-2 text-sm font-medium text-foreground shadow-sm transition-colors hover:bg-accent disabled:cursor-not-allowed disabled:opacity-50"
                            @click="handleExtractTags"
                        >
                            <Loader2
                                v-if="loading"
                                class="size-4 animate-spin"
                            />
                            <Sparkles v-else class="size-4 text-primary" />
                            {{ loading ? 'Распознаём...' : 'Распознать с ИИ' }}
                        </button>
                        <p v-if="error" class="text-xs text-destructive">
                            {{ error }}
                        </p>
                    </div>
                </div>
            </div>

            <div
                v-if="job.resume_version_url"
                class="overflow-hidden rounded-xl border border-border bg-card shadow-sm"
            >
                <div
                    class="flex items-center gap-2 border-b border-border px-5 py-3"
                >
                    <ScrollText class="size-4 text-muted-foreground" />
                    <h3 class="text-sm font-semibold text-foreground">
                        Версия резюме
                    </h3>
                </div>
                <div class="p-5">
                    <a
                        v-if="job.resume_version_url.startsWith('http')"
                        :href="job.resume_version_url"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center gap-1.5 text-sm text-primary underline underline-offset-2 hover:opacity-80"
                    >
                        <ExternalLink class="size-3.5 shrink-0" />
                        {{ job.resume_version_url }}
                    </a>
                    <span v-else class="text-sm text-muted-foreground">
                        {{ job.resume_version_url }}
                    </span>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <ActivityTimeline :activities="job.activities" />
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
