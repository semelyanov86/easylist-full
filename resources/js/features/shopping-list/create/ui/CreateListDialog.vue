<script setup lang="ts">
import type { Folder } from '@entities/folder';
import { Form, usePage } from '@inertiajs/vue3';
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
import { computed } from 'vue';

import ShoppingListController from '@/actions/App/Http/Controllers/Shopping/ShoppingListController';

type Props = {
    open: boolean;
    folders?: Folder[];
    defaultFolderId?: number | null;
};

const props = withDefaults(defineProps<Props>(), {
    folders: undefined,
    defaultFolderId: null,
});

const emit = defineEmits<{
    close: [];
}>();

const page = usePage();

const availableFolders = computed<Folder[]>(() => {
    if (props.folders) {
        return props.folders;
    }

    return (page.props.shoppingFolders as Folder[]) ?? [];
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
        <DialogContent>
            <Form
                :action="ShoppingListController.store.url()"
                method="post"
                reset-on-success
                @success="emit('close')"
                :options="{ preserveScroll: true }"
                class="space-y-6"
                v-slot="{ errors, processing }"
            >
                <DialogHeader>
                    <DialogTitle>Новый список</DialogTitle>
                    <DialogDescription
                        >Создайте список покупок.</DialogDescription
                    >
                </DialogHeader>
                <div class="space-y-4">
                    <div class="grid gap-2">
                        <Label for="create-list-name">Название</Label>
                        <Input
                            id="create-list-name"
                            name="name"
                            placeholder="Например: Продукты на неделю"
                            required
                        />
                        <InputError :message="errors.name" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="create-list-folder">Папка</Label>
                        <select
                            id="create-list-folder"
                            name="folder_id"
                            class="w-full min-w-0 rounded-md border border-input bg-transparent px-3 py-2 text-base shadow-xs transition-[color,box-shadow] outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 md:text-sm dark:bg-input/30"
                        >
                            <option value="">Без папки</option>
                            <option
                                v-for="f in availableFolders"
                                :key="f.id"
                                :value="f.id"
                                :selected="f.id === defaultFolderId"
                            >
                                {{ f.icon ? f.icon + ' ' : '' }}{{ f.name }}
                            </option>
                        </select>
                        <InputError :message="errors.folder_id" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="create-list-icon">Иконка</Label>
                        <Input
                            id="create-list-icon"
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
