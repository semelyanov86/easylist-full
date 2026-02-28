<script setup lang="ts">
import type { StatusTab } from '@entities/job';
import type { JobCategory } from '@entities/job-category';
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

import JobController from '@/actions/App/Http/Controllers/JobController';

type Props = {
    open: boolean;
    statuses: StatusTab[];
    categories: JobCategory[];
    defaultStatusId?: number | null;
    defaultCategoryId?: number | null;
};

const props = defineProps<Props>();

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
        <DialogContent class="max-h-[90dvh] overflow-y-auto sm:max-w-lg">
            <Form
                v-bind="JobController.store.form()"
                reset-on-success
                @success="emit('close')"
                :options="{ preserveScroll: true }"
                class="space-y-6"
                v-slot="{ errors, processing }"
            >
                <DialogHeader>
                    <DialogTitle>Новая вакансия</DialogTitle>
                    <DialogDescription>
                        Добавьте вакансию для отслеживания.
                    </DialogDescription>
                </DialogHeader>

                <div class="space-y-4">
                    <!-- Название -->
                    <div class="grid gap-2">
                        <Label for="create-job-title">Название</Label>
                        <Input
                            id="create-job-title"
                            name="title"
                            placeholder="Например: Frontend Developer"
                            required
                        />
                        <InputError :message="errors.title" />
                    </div>

                    <!-- Компания -->
                    <div class="grid gap-2">
                        <Label for="create-job-company">Компания</Label>
                        <Input
                            id="create-job-company"
                            name="company_name"
                            placeholder="Например: Яндекс"
                            required
                        />
                        <InputError :message="errors.company_name" />
                    </div>

                    <!-- Статус и Категория -->
                    <div class="grid grid-cols-2 gap-4">
                        <div class="grid gap-2">
                            <Label for="create-job-status">Статус</Label>
                            <select
                                id="create-job-status"
                                name="job_status_id"
                                required
                                class="h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-base shadow-xs transition-[color,box-shadow] outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 md:text-sm dark:bg-input/30"
                            >
                                <option value="" disabled>
                                    Выберите статус
                                </option>
                                <option
                                    v-for="status in props.statuses"
                                    :key="status.id"
                                    :value="status.id"
                                    :selected="
                                        status.id === props.defaultStatusId
                                    "
                                >
                                    {{ status.title }}
                                </option>
                            </select>
                            <InputError :message="errors.job_status_id" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="create-job-category">Категория</Label>
                            <select
                                id="create-job-category"
                                name="job_category_id"
                                required
                                class="h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-base shadow-xs transition-[color,box-shadow] outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 md:text-sm dark:bg-input/30"
                            >
                                <option value="" disabled>
                                    Выберите категорию
                                </option>
                                <option
                                    v-for="category in props.categories"
                                    :key="category.id"
                                    :value="category.id"
                                    :selected="
                                        category.id === props.defaultCategoryId
                                    "
                                >
                                    {{ category.title }}
                                </option>
                            </select>
                            <InputError :message="errors.job_category_id" />
                        </div>
                    </div>

                    <!-- Ссылка на вакансию -->
                    <div class="grid gap-2">
                        <Label for="create-job-url">Ссылка</Label>
                        <Input
                            id="create-job-url"
                            name="job_url"
                            type="url"
                            placeholder="https://example.com/vacancy"
                        />
                        <InputError :message="errors.job_url" />
                    </div>

                    <!-- Зарплата и Город -->
                    <div class="grid grid-cols-2 gap-4">
                        <div class="grid gap-2">
                            <Label for="create-job-salary">Зарплата</Label>
                            <Input
                                id="create-job-salary"
                                name="salary"
                                type="number"
                                min="0"
                                placeholder="100000"
                            />
                            <InputError :message="errors.salary" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="create-job-city">Город</Label>
                            <Input
                                id="create-job-city"
                                name="location_city"
                                placeholder="Москва"
                            />
                            <InputError :message="errors.location_city" />
                        </div>
                    </div>

                    <!-- Описание -->
                    <div class="grid gap-2">
                        <Label for="create-job-description">Описание</Label>
                        <textarea
                            id="create-job-description"
                            name="description"
                            placeholder="Заметки о вакансии"
                            rows="3"
                            class="w-full min-w-0 rounded-md border border-input bg-transparent px-3 py-2 text-base shadow-xs transition-[color,box-shadow] outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 md:text-sm dark:bg-input/30"
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
                        Создать
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
