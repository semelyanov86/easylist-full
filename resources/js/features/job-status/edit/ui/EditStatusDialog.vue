<script setup lang="ts">
import { type JobStatus, STATUS_COLORS } from '@entities/job-status';
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
import { ref, watch } from 'vue';

import JobStatusController from '@/actions/App/Http/Controllers/Settings/JobStatusController';

type Props = {
    status: JobStatus | null;
};

const props = defineProps<Props>();

const emit = defineEmits<{
    close: [];
}>();

const editColor = ref('gray');

watch(
    () => props.status,
    (status) => {
        if (status) {
            editColor.value = status.color;
        }
    },
);
</script>

<template>
    <Dialog
        :open="status !== null"
        @update:open="
            (open: boolean) => {
                if (!open) emit('close');
            }
        "
    >
        <DialogContent>
            <Form
                v-if="status"
                v-bind="JobStatusController.update.form(status.id)"
                reset-on-success
                @success="emit('close')"
                :options="{ preserveScroll: true }"
                class="space-y-6"
                v-slot="{ errors, processing }"
            >
                <DialogHeader>
                    <DialogTitle>Редактировать статус</DialogTitle>
                    <DialogDescription
                        >Измените название или описание статуса
                        отклика.</DialogDescription
                    >
                </DialogHeader>
                <div class="space-y-4">
                    <div class="grid gap-2">
                        <Label for="edit-status-title">Название</Label>
                        <Input
                            id="edit-status-title"
                            name="title"
                            :default-value="status.title"
                            required
                        />
                        <InputError :message="errors.title" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="edit-status-description">Описание</Label>
                        <textarea
                            id="edit-status-description"
                            name="description"
                            :value="status.description ?? ''"
                            rows="2"
                            class="w-full min-w-0 rounded-md border border-input bg-transparent px-3 py-2 text-base shadow-xs transition-[color,box-shadow] outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 md:text-sm dark:bg-input/30"
                        />
                        <InputError :message="errors.description" />
                    </div>
                    <div class="grid gap-2">
                        <Label>Цвет</Label>
                        <div class="flex flex-wrap gap-2">
                            <button
                                v-for="color in STATUS_COLORS"
                                :key="color.value"
                                type="button"
                                class="flex size-8 items-center justify-center rounded-full border-2 transition-all"
                                :class="[
                                    editColor === color.value
                                        ? 'scale-110 border-ring'
                                        : 'border-transparent hover:border-border',
                                ]"
                                :title="color.label"
                                @click="editColor = color.value"
                            >
                                <span
                                    class="block size-5 rounded-full"
                                    :style="{
                                        backgroundColor: `var(--status-${color.value})`,
                                    }"
                                />
                            </button>
                        </div>
                        <input type="hidden" name="color" :value="editColor" />
                        <InputError :message="errors.color" />
                    </div>
                </div>
                <DialogFooter class="gap-2">
                    <DialogClose as-child>
                        <Button variant="secondary" @click="emit('close')"
                            >Отмена</Button
                        >
                    </DialogClose>
                    <Button type="submit" :disabled="processing"
                        >Сохранить</Button
                    >
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
