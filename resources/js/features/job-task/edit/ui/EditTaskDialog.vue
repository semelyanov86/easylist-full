<script setup lang="ts">
import type { JobTask } from '@entities/job-task';
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
import { Save } from 'lucide-vue-next';
import { computed } from 'vue';

import JobTaskController from '@/actions/App/Http/Controllers/JobTaskController';

type Props = {
    open: boolean;
    task: JobTask;
};

const props = defineProps<Props>();

const emit = defineEmits<{
    close: [];
}>();

const deadlineDate = computed(() => {
    if (!props.task.deadline) {
        return '';
    }
    return props.task.deadline.slice(0, 10);
});
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
                :action="JobTaskController.update.url(task.id)"
                method="patch"
                :options="{ preserveScroll: true }"
                @success="emit('close')"
                class="space-y-5"
                v-slot="{ errors, processing }"
            >
                <DialogHeader>
                    <DialogTitle>Редактировать задачу</DialogTitle>
                    <DialogDescription>
                        Измените данные задачи.
                    </DialogDescription>
                </DialogHeader>

                <div class="space-y-4">
                    <div class="grid gap-2">
                        <Label for="edit-task-title">Название</Label>
                        <Input
                            id="edit-task-title"
                            name="title"
                            required
                            :default-value="task.title"
                        />
                        <InputError :message="errors.title" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="edit-task-deadline">
                            Дедлайн
                            <span class="text-muted-foreground">
                                (необязательно)
                            </span>
                        </Label>
                        <Input
                            id="edit-task-deadline"
                            name="deadline"
                            type="date"
                            :default-value="deadlineDate"
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
                        <Save class="size-3.5" />
                        <span>Сохранить</span>
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
