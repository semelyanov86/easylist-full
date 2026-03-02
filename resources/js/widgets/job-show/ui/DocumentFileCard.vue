<script setup lang="ts">
import {
    getCategoryStyle,
    getDocumentFileIcon,
    type JobDocument,
} from '@entities/job-document';
import { formatFileSize, formatRelativeDate } from '@shared/lib/format';
import { Download, Trash2 } from 'lucide-vue-next';

import JobDocumentController from '@/actions/App/Http/Controllers/JobDocumentController';

type Props = {
    document: JobDocument;
};

defineProps<Props>();

const emit = defineEmits<{
    delete: [document: JobDocument];
}>();
</script>

<template>
    <div
        class="group flex items-center gap-3 rounded-lg border border-border/60 bg-background p-3 transition-all hover:border-border hover:shadow-sm dark:bg-card"
    >
        <div
            class="flex size-10 shrink-0 items-center justify-center rounded-lg"
            :class="[getCategoryStyle(document.category).bg]"
        >
            <component
                :is="getDocumentFileIcon(document.mime_type)"
                class="size-5"
                :class="[getCategoryStyle(document.category).text]"
            />
        </div>

        <div class="min-w-0 flex-1">
            <div class="flex items-center gap-2">
                <span class="truncate text-sm font-medium text-foreground">
                    {{ document.title }}
                </span>
                <span
                    class="shrink-0 rounded-md border px-1.5 py-0.5 text-xs"
                    :class="[
                        getCategoryStyle(document.category).bg,
                        getCategoryStyle(document.category).text,
                        getCategoryStyle(document.category).border,
                    ]"
                >
                    {{ document.category_label }}
                </span>
            </div>
            <div
                class="mt-0.5 flex items-center gap-1.5 text-xs text-muted-foreground"
            >
                <span
                    v-if="document.original_filename"
                    class="max-w-32 truncate"
                >
                    {{ document.original_filename }}
                </span>
                <template v-if="document.file_size">
                    <span class="text-border">·</span>
                    <span>{{ formatFileSize(document.file_size) }}</span>
                </template>
                <span class="text-border">·</span>
                <span>{{ formatRelativeDate(document.created_at) }}</span>
            </div>
        </div>

        <div
            class="flex shrink-0 items-center gap-0.5 opacity-0 transition-opacity group-hover:opacity-100"
        >
            <a
                :href="JobDocumentController.download.url(document.id)"
                class="inline-flex size-8 items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                title="Скачать"
            >
                <Download class="size-4" />
            </a>
            <button
                type="button"
                class="inline-flex size-8 items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-destructive/10 hover:text-destructive"
                title="Удалить"
                @click="emit('delete', document)"
            >
                <Trash2 class="size-4" />
            </button>
        </div>
    </div>
</template>
