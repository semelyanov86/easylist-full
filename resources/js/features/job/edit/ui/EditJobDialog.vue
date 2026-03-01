<script setup lang="ts">
import type { Job, StatusTab } from '@entities/job';
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
    job: Job | null;
    statuses: StatusTab[];
    categories: JobCategory[];
};

defineProps<Props>();

const emit = defineEmits<{
    close: [];
}>();
</script>

<template>
    <Dialog
        :open="job !== null"
        @update:open="
            (open: boolean) => {
                if (!open) emit('close');
            }
        "
    >
        <DialogContent class="max-h-[90dvh] overflow-y-auto sm:max-w-lg">
            <Form
                v-if="job"
                v-bind="JobController.update.form(job.id)"
                reset-on-success
                @success="emit('close')"
                :options="{ preserveScroll: true }"
                class="space-y-6"
                v-slot="{ errors, processing }"
            >
                <DialogHeader>
                    <DialogTitle>Редактировать вакансию</DialogTitle>
                    <DialogDescription>
                        Измените данные вакансии.
                    </DialogDescription>
                </DialogHeader>

                <div class="space-y-4">
                    <!-- Название -->
                    <div class="grid gap-2">
                        <Label for="edit-job-title">Название</Label>
                        <Input
                            id="edit-job-title"
                            name="title"
                            :default-value="job.title"
                            required
                        />
                        <InputError :message="errors.title" />
                    </div>

                    <!-- Компания -->
                    <div class="grid gap-2">
                        <Label for="edit-job-company">Компания</Label>
                        <Input
                            id="edit-job-company"
                            name="company_name"
                            :default-value="job.company_name"
                            required
                        />
                        <InputError :message="errors.company_name" />
                    </div>

                    <!-- Статус и Категория -->
                    <div class="grid grid-cols-2 gap-4">
                        <div class="grid gap-2">
                            <Label for="edit-job-status">Статус</Label>
                            <select
                                id="edit-job-status"
                                name="job_status_id"
                                required
                                class="h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-base shadow-xs transition-[color,box-shadow] outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 md:text-sm dark:bg-input/30"
                            >
                                <option value="" disabled>
                                    Выберите статус
                                </option>
                                <option
                                    v-for="status in statuses"
                                    :key="status.id"
                                    :value="status.id"
                                    :selected="status.id === job.job_status_id"
                                >
                                    {{ status.title }}
                                </option>
                            </select>
                            <InputError :message="errors.job_status_id" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="edit-job-category">Категория</Label>
                            <select
                                id="edit-job-category"
                                name="job_category_id"
                                required
                                class="h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-base shadow-xs transition-[color,box-shadow] outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 md:text-sm dark:bg-input/30"
                            >
                                <option value="" disabled>
                                    Выберите категорию
                                </option>
                                <option
                                    v-for="category in categories"
                                    :key="category.id"
                                    :value="category.id"
                                    :selected="
                                        category.id === job.job_category_id
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
                        <Label for="edit-job-url">Ссылка</Label>
                        <Input
                            id="edit-job-url"
                            name="job_url"
                            type="url"
                            :default-value="job.job_url ?? ''"
                        />
                        <InputError :message="errors.job_url" />
                    </div>

                    <!-- Зарплата и Город -->
                    <div class="grid grid-cols-2 gap-4">
                        <div class="grid gap-2">
                            <Label for="edit-job-salary">Зарплата</Label>
                            <Input
                                id="edit-job-salary"
                                name="salary"
                                type="number"
                                min="0"
                                :default-value="
                                    job.salary !== null
                                        ? String(job.salary)
                                        : ''
                                "
                            />
                            <InputError :message="errors.salary" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="edit-job-city">Город</Label>
                            <Input
                                id="edit-job-city"
                                name="location_city"
                                :default-value="job.location_city ?? ''"
                            />
                            <InputError :message="errors.location_city" />
                        </div>
                    </div>

                    <!-- Описание -->
                    <div class="grid gap-2">
                        <Label for="edit-job-description">Описание</Label>
                        <textarea
                            id="edit-job-description"
                            name="description"
                            :value="job.description ?? ''"
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
                        Сохранить
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
