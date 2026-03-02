<script setup lang="ts">
import { getCategoryStyle, type JobDocument } from '@entities/job-document';
import { formatRelativeDate } from '@shared/lib/format';
import { ExternalLink, Trash2 } from 'lucide-vue-next';

type Props = {
    document: JobDocument;
};

defineProps<Props>();

const emit = defineEmits<{
    delete: [document: JobDocument];
}>();
</script>

<template>
    <a
        :href="document.external_url ?? '#'"
        target="_blank"
        rel="noopener noreferrer"
        class="group flex items-center gap-3 rounded-lg border border-border/60 bg-background p-3 transition-all hover:border-status-purple/30 hover:shadow-sm dark:bg-card"
    >
        <div
            class="flex size-10 shrink-0 items-center justify-center rounded-lg"
            :class="[getCategoryStyle(document.category).bg]"
        >
            <ExternalLink
                class="size-5"
                :class="[getCategoryStyle(document.category).text]"
            />
        </div>

        <div class="min-w-0 flex-1">
            <div class="flex items-center gap-2">
                <span
                    class="truncate text-sm font-medium text-foreground group-hover:text-status-purple"
                >
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
                <span class="max-w-56 truncate">
                    {{ document.external_url }}
                </span>
                <span class="text-border">·</span>
                <span>{{ formatRelativeDate(document.created_at) }}</span>
            </div>
        </div>

        <div class="flex shrink-0 items-center gap-0.5" @click.prevent.stop>
            <button
                type="button"
                class="inline-flex size-8 items-center justify-center rounded-md text-muted-foreground opacity-0 transition-all group-hover:opacity-100 hover:bg-destructive/10 hover:text-destructive"
                title="Удалить"
                @click="emit('delete', document)"
            >
                <Trash2 class="size-4" />
            </button>
        </div>
    </a>
</template>
