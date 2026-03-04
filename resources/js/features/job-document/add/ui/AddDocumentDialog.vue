<script setup lang="ts">
import { documentCategories } from '@entities/job-document';
import { Form } from '@inertiajs/vue3';
import InputError from '@shared/components/InputError.vue';
import { Button } from '@shared/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@shared/ui/dialog';
import { Input } from '@shared/ui/input';
import { Label } from '@shared/ui/label';
import { File, Upload, X } from 'lucide-vue-next';
import { ref } from 'vue';

import JobDocumentController from '@/actions/App/Http/Controllers/JobDocumentController';

type Props = {
    open: boolean;
    jobId: number;
};

defineProps<Props>();

const emit = defineEmits<{
    close: [];
}>();

const selectedFileName = ref<string | null>(null);
const isDragOver = ref(false);

const onFileChange = (event: Event): void => {
    const input = event.target as HTMLInputElement;
    selectedFileName.value = input.files?.[0]?.name ?? null;
};

const clearFile = (): void => {
    selectedFileName.value = null;
    const input = document.getElementById('doc-file') as HTMLInputElement;
    if (input) {
        input.value = '';
    }
};

const onFormSuccess = (): void => {
    selectedFileName.value = null;
    emit('close');
};
</script>

<template>
    <Dialog
        :open="open"
        @update:open="
            (val: boolean) => {
                if (!val) {
                    selectedFileName = null;
                    emit('close');
                }
            }
        "
    >
        <DialogContent class="max-h-[90dvh] overflow-y-auto sm:max-w-md">
            <Form
                :action="JobDocumentController.store.url(jobId)"
                method="post"
                reset-on-success
                :options="{ preserveScroll: true }"
                @success="onFormSuccess"
                class="space-y-5"
                v-slot="{ errors, processing }"
            >
                <DialogHeader>
                    <DialogTitle>Загрузить файл</DialogTitle>
                    <DialogDescription>
                        Прикрепите документ к вакансии. Максимум 10 МБ.
                    </DialogDescription>
                </DialogHeader>

                <div class="space-y-4">
                    <!-- Дропзона -->
                    <div class="grid gap-2">
                        <Label for="doc-file">Файл</Label>
                        <div
                            class="relative rounded-lg border-2 border-dashed transition-colors"
                            :class="
                                isDragOver
                                    ? 'border-primary bg-primary/5 dark:bg-primary/10'
                                    : selectedFileName
                                      ? 'border-status-green/40 bg-status-green/5 dark:bg-status-green/10'
                                      : 'border-border hover:border-muted-foreground/30'
                            "
                            @dragover.prevent="isDragOver = true"
                            @dragleave="isDragOver = false"
                            @drop="isDragOver = false"
                        >
                            <div
                                v-if="!selectedFileName"
                                class="flex flex-col items-center gap-2 px-4 py-6"
                            >
                                <div
                                    class="flex size-10 items-center justify-center rounded-full bg-muted"
                                >
                                    <Upload
                                        class="size-5 text-muted-foreground"
                                    />
                                </div>
                                <div class="text-center">
                                    <p class="text-sm text-foreground">
                                        Перетащите файл сюда или
                                        <span
                                            class="font-medium text-primary underline underline-offset-2"
                                        >
                                            выберите
                                        </span>
                                    </p>
                                    <p
                                        class="mt-1 text-xs text-muted-foreground"
                                    >
                                        PDF, DOC, JPG, PNG — до 10 МБ
                                    </p>
                                </div>
                            </div>

                            <div
                                v-else
                                class="flex items-center gap-3 px-4 py-3.5"
                            >
                                <div
                                    class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-status-green/10 dark:bg-status-green/15"
                                >
                                    <File class="size-4 text-status-green" />
                                </div>
                                <span
                                    class="min-w-0 flex-1 truncate text-sm font-medium text-foreground"
                                >
                                    {{ selectedFileName }}
                                </span>
                                <button
                                    type="button"
                                    class="flex size-7 shrink-0 items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                                    @click="clearFile"
                                >
                                    <X class="size-4" />
                                </button>
                            </div>

                            <input
                                id="doc-file"
                                name="file"
                                type="file"
                                accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx"
                                required
                                class="absolute inset-0 cursor-pointer opacity-0"
                                @change="onFileChange"
                            />
                        </div>
                        <InputError :message="errors.file" />
                    </div>

                    <!-- Название и Категория в одну строку -->
                    <div class="grid gap-2">
                        <Label for="doc-title">Название</Label>
                        <Input
                            id="doc-title"
                            name="title"
                            required
                            placeholder="Например: Резюме дизайнера"
                        />
                        <InputError :message="errors.title" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="doc-category">Категория</Label>
                        <select
                            id="doc-category"
                            name="category"
                            required
                            class="h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-base shadow-xs transition-[color,box-shadow] outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 md:text-sm dark:bg-input/30"
                        >
                            <option value="" disabled selected>
                                Выберите категорию
                            </option>
                            <option
                                v-for="cat in documentCategories"
                                :key="cat.value"
                                :value="cat.value"
                            >
                                {{ cat.label }}
                            </option>
                        </select>
                        <InputError :message="errors.category" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="doc-description">
                            Описание
                            <span class="text-muted-foreground">
                                (необязательно)
                            </span>
                        </Label>
                        <textarea
                            id="doc-description"
                            name="description"
                            rows="2"
                            placeholder="Краткое описание документа..."
                            class="w-full min-w-0 resize-none rounded-md border border-input bg-transparent px-3 py-2 text-base shadow-xs transition-[color,box-shadow] outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 md:text-sm dark:bg-input/30"
                        />
                        <InputError :message="errors.description" />
                    </div>
                </div>

                <DialogFooter class="gap-2">
                    <DialogClose as-child>
                        <Button variant="secondary" @click="emit('close')">
                            Отмена
                        </Button>
                    </DialogClose>
                    <Button type="submit" :disabled="processing">
                        <Upload class="size-3.5" />
                        <span>Загрузить</span>
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
