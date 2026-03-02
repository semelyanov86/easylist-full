<script setup lang="ts">
import type { JobDetail } from '@entities/job';
import type { JobDocument } from '@entities/job-document';
import {
    AddDocumentDialog,
    LinkDocumentDialog,
} from '@features/job-document/add';
import { router } from '@inertiajs/vue3';
import { Button } from '@shared/ui/button';
import { FileText, Globe, Link2, Upload } from 'lucide-vue-next';
import { computed, ref } from 'vue';

import JobDocumentController from '@/actions/App/Http/Controllers/JobDocumentController';

import DocumentFileCard from './DocumentFileCard.vue';
import DocumentLinkCard from './DocumentLinkCard.vue';

type Props = {
    job: JobDetail;
};

const props = defineProps<Props>();

const showAddDialog = ref(false);
const showLinkDialog = ref(false);

const fileDocuments = computed(() =>
    props.job.documents.filter((d) => d.file_path !== null),
);
const linkDocuments = computed(() =>
    props.job.documents.filter((d) => d.external_url !== null),
);

const deleteDocument = (document: JobDocument): void => {
    router.delete(JobDocumentController.destroy.url(document.id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <!-- Файлы -->
            <div
                class="overflow-hidden rounded-xl border border-border bg-card shadow-sm"
            >
                <div
                    class="flex items-center justify-between border-b border-border px-5 py-3"
                >
                    <div class="flex items-center gap-2.5">
                        <div
                            class="flex size-7 items-center justify-center rounded-lg bg-status-blue/10 dark:bg-status-blue/15"
                        >
                            <FileText class="size-3.5 text-status-blue" />
                        </div>
                        <h3 class="text-sm font-semibold text-foreground">
                            Файлы
                        </h3>
                        <span
                            v-if="fileDocuments.length > 0"
                            class="rounded-md bg-muted px-1.5 py-0.5 text-xs font-medium text-muted-foreground tabular-nums"
                        >
                            {{ fileDocuments.length }}
                        </span>
                    </div>
                    <Button size="sm" @click="showAddDialog = true">
                        <Upload class="size-3.5" />
                        <span>Загрузить</span>
                    </Button>
                </div>

                <div v-if="fileDocuments.length > 0" class="space-y-2 p-3">
                    <DocumentFileCard
                        v-for="doc in fileDocuments"
                        :key="doc.id"
                        :document="doc"
                        @delete="deleteDocument"
                    />
                </div>

                <div
                    v-else
                    class="flex flex-col items-center justify-center gap-2 px-5 py-12"
                >
                    <div
                        class="flex size-10 items-center justify-center rounded-full bg-muted/80"
                    >
                        <Upload class="size-5 text-muted-foreground/40" />
                    </div>
                    <p class="text-sm text-muted-foreground/60">
                        Нет загруженных файлов
                    </p>
                </div>
            </div>

            <!-- Ссылки -->
            <div
                class="overflow-hidden rounded-xl border border-border bg-card shadow-sm"
            >
                <div
                    class="flex items-center justify-between border-b border-border px-5 py-3"
                >
                    <div class="flex items-center gap-2.5">
                        <div
                            class="flex size-7 items-center justify-center rounded-lg bg-status-purple/10 dark:bg-status-purple/15"
                        >
                            <Globe class="size-3.5 text-status-purple" />
                        </div>
                        <h3 class="text-sm font-semibold text-foreground">
                            Ссылки
                        </h3>
                        <span
                            v-if="linkDocuments.length > 0"
                            class="rounded-md bg-muted px-1.5 py-0.5 text-xs font-medium text-muted-foreground tabular-nums"
                        >
                            {{ linkDocuments.length }}
                        </span>
                    </div>
                    <Button
                        size="sm"
                        variant="outline"
                        @click="showLinkDialog = true"
                    >
                        <Link2 class="size-3.5" />
                        <span>Добавить</span>
                    </Button>
                </div>

                <div v-if="linkDocuments.length > 0" class="space-y-2 p-3">
                    <DocumentLinkCard
                        v-for="doc in linkDocuments"
                        :key="doc.id"
                        :document="doc"
                        @delete="deleteDocument"
                    />
                </div>

                <div
                    v-else
                    class="flex flex-col items-center justify-center gap-2 px-5 py-12"
                >
                    <div
                        class="flex size-10 items-center justify-center rounded-full bg-muted/80"
                    >
                        <Link2 class="size-5 text-muted-foreground/40" />
                    </div>
                    <p class="text-sm text-muted-foreground/60">
                        Нет внешних ссылок
                    </p>
                </div>
            </div>
        </div>
    </div>

    <AddDocumentDialog
        :open="showAddDialog"
        :job-id="job.id"
        @close="showAddDialog = false"
    />
    <LinkDocumentDialog
        :open="showLinkDialog"
        :job-id="job.id"
        @close="showLinkDialog = false"
    />
</template>
