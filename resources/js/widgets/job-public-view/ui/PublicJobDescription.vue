<script setup lang="ts">
import DOMPurify from 'dompurify';
import { marked } from 'marked';
import { computed } from 'vue';

type Props = {
    description: string;
};

const props = defineProps<Props>();

const descriptionHtml = computed((): string => {
    const raw = marked.parse(props.description);
    const html = typeof raw === 'string' ? raw : '';

    return DOMPurify.sanitize(html);
});
</script>

<template>
    <div
        class="overflow-hidden rounded-xl border border-border bg-card shadow-sm"
    >
        <div class="flex items-center gap-2 border-b border-border px-6 py-3">
            <h2 class="text-sm font-semibold text-foreground">
                Описание вакансии
            </h2>
        </div>
        <div
            class="job-description p-6 text-sm leading-relaxed text-muted-foreground"
            v-html="descriptionHtml"
        />
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

.job-description :deep(table) {
    width: 100%;
    border-collapse: collapse;
    margin-top: 0.75em;
    margin-bottom: 0.75em;
}

.job-description :deep(th),
.job-description :deep(td) {
    padding: 0.5em 0.75em;
    border: 1px solid var(--border);
    text-align: left;
}

.job-description :deep(th) {
    font-weight: 600;
    color: var(--foreground);
    background-color: var(--muted);
}
</style>
