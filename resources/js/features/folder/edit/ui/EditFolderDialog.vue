<script setup lang="ts">
import type { Folder } from '@entities/folder';
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

import FolderController from '@/actions/App/Http/Controllers/Shopping/FolderController';

type Props = {
    folder: Folder | null;
};

defineProps<Props>();

const emit = defineEmits<{
    close: [];
}>();
</script>

<template>
    <Dialog
        :open="folder !== null"
        @update:open="
            (open: boolean) => {
                if (!open) emit('close');
            }
        "
    >
        <DialogContent>
            <Form
                v-if="folder"
                :action="FolderController.update.url(folder.id)"
                method="patch"
                reset-on-success
                @success="emit('close')"
                :options="{ preserveScroll: true }"
                class="space-y-6"
                v-slot="{ errors, processing }"
            >
                <DialogHeader>
                    <DialogTitle>Редактировать папку</DialogTitle>
                    <DialogDescription
                        >Измените название или иконку папки.</DialogDescription
                    >
                </DialogHeader>
                <div class="space-y-4">
                    <div class="grid gap-2">
                        <Label for="edit-folder-name">Название</Label>
                        <Input
                            id="edit-folder-name"
                            name="name"
                            :default-value="folder.name"
                            required
                        />
                        <InputError :message="errors.name" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="edit-folder-icon">Иконка</Label>
                        <Input
                            id="edit-folder-icon"
                            name="icon"
                            :default-value="folder.icon ?? ''"
                        />
                        <InputError :message="errors.icon" />
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
