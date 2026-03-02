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
import { Globe, Link2 } from 'lucide-vue-next';

import JobDocumentController from '@/actions/App/Http/Controllers/JobDocumentController';

type Props = {
    open: boolean;
    jobId: number;
};

defineProps<Props>();

const emit = defineEmits<{
    close: [];
}>();
</script>

<template>
    <Dialog
        :open="open"
        @update:open="
            (val: boolean) => {
                if (!val) emit('close');
            }
        "
    >
        <DialogContent class="max-h-[90dvh] overflow-y-auto sm:max-w-md">
            <Form
                v-bind="JobDocumentController.store.form(jobId)"
                reset-on-success
                :options="{ preserveScroll: true }"
                @success="emit('close')"
                class="space-y-5"
                v-slot="{ errors, processing }"
            >
                <DialogHeader>
                    <DialogTitle>Добавить ссылку</DialogTitle>
                    <DialogDescription>
                        Укажите ссылку на внешний ресурс.
                    </DialogDescription>
                </DialogHeader>

                <div class="space-y-4">
                    <!-- URL с иконкой -->
                    <div class="grid gap-2">
                        <Label for="link-url">Ссылка</Label>
                        <div class="relative">
                            <div
                                class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"
                            >
                                <Globe
                                    class="size-4 text-muted-foreground/60"
                                />
                            </div>
                            <input
                                id="link-url"
                                name="external_url"
                                type="url"
                                required
                                placeholder="https://..."
                                class="h-9 w-full rounded-md border border-input bg-transparent py-1 pr-3 pl-9 text-base shadow-xs transition-[color,box-shadow] outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 md:text-sm dark:bg-input/30"
                            />
                        </div>
                        <InputError :message="errors.external_url" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="link-title">Название</Label>
                        <Input
                            id="link-title"
                            name="title"
                            required
                            placeholder="Например: Портфолио на Behance"
                        />
                        <InputError :message="errors.title" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="link-category">Категория</Label>
                        <select
                            id="link-category"
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
                        <Label for="link-description">
                            Описание
                            <span class="text-muted-foreground">
                                (необязательно)
                            </span>
                        </Label>
                        <textarea
                            id="link-description"
                            name="description"
                            rows="2"
                            placeholder="Краткое описание ресурса..."
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
                        <Link2 class="size-3.5" />
                        <span>Сохранить</span>
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
