<script setup lang="ts">
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
import { ListPlus } from 'lucide-vue-next';

import JobTaskController from '@/actions/App/Http/Controllers/JobTaskController';

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
        <DialogContent class="sm:max-w-md">
            <Form
                :action="JobTaskController.store.url(jobId)"
                method="post"
                reset-on-success
                :options="{ preserveScroll: true }"
                @success="emit('close')"
                class="space-y-5"
                v-slot="{ errors, processing }"
            >
                <DialogHeader>
                    <DialogTitle>Создать задачу</DialogTitle>
                    <DialogDescription>
                        Добавьте задачу к этой вакансии.
                    </DialogDescription>
                </DialogHeader>

                <div class="space-y-4">
                    <div class="grid gap-2">
                        <Label for="add-task-title">Название</Label>
                        <Input
                            id="add-task-title"
                            name="title"
                            required
                            placeholder="Подготовить резюме"
                        />
                        <InputError :message="errors.title" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="add-task-deadline">
                            Дедлайн
                            <span class="text-muted-foreground">
                                (необязательно)
                            </span>
                        </Label>
                        <Input
                            id="add-task-deadline"
                            name="deadline"
                            type="date"
                        />
                        <InputError :message="errors.deadline" />
                    </div>
                </div>

                <DialogFooter class="gap-2">
                    <DialogClose as-child>
                        <Button variant="secondary" @click="emit('close')">
                            Отмена
                        </Button>
                    </DialogClose>
                    <Button type="submit" :disabled="processing">
                        <ListPlus class="size-3.5" />
                        <span>Создать</span>
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
