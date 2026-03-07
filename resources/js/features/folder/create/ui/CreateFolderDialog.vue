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

import FolderController from '@/actions/App/Http/Controllers/Shopping/FolderController';

type Props = {
    open: boolean;
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
        <DialogContent>
            <Form
                :action="FolderController.store.url()"
                method="post"
                reset-on-success
                @success="emit('close')"
                :options="{ preserveScroll: true }"
                class="space-y-6"
                v-slot="{ errors, processing }"
            >
                <DialogHeader>
                    <DialogTitle>Новая папка</DialogTitle>
                    <DialogDescription
                        >Создайте папку для группировки списков
                        покупок.</DialogDescription
                    >
                </DialogHeader>
                <div class="space-y-4">
                    <div class="grid gap-2">
                        <Label for="create-folder-name">Название</Label>
                        <Input
                            id="create-folder-name"
                            name="name"
                            placeholder="Например: Продукты"
                            required
                        />
                        <InputError :message="errors.name" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="create-folder-icon">Иконка</Label>
                        <Input
                            id="create-folder-icon"
                            name="icon"
                            placeholder="Эмодзи, например: 🛒"
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
                        >Создать</Button
                    >
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
