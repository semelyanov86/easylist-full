<script setup lang="ts">
import type { Folder } from '@entities/folder';
import { Form } from '@inertiajs/vue3';
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
                :action="FolderController.destroy.url(folder.id)"
                method="delete"
                reset-on-success
                @success="emit('close')"
                :options="{ preserveScroll: true }"
                class="space-y-6"
                v-slot="{ processing }"
            >
                <DialogHeader>
                    <DialogTitle>Удалить папку?</DialogTitle>
                    <DialogDescription>
                        Папка «{{ folder.name }}» и все её списки будут удалены
                        безвозвратно.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter class="gap-2">
                    <DialogClose as-child>
                        <Button variant="secondary" @click="emit('close')"
                            >Отмена</Button
                        >
                    </DialogClose>
                    <Button
                        type="submit"
                        variant="destructive"
                        :disabled="processing"
                        >Удалить</Button
                    >
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
