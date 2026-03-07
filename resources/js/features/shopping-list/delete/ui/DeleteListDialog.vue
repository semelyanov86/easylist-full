<script setup lang="ts">
import type { ShoppingList } from '@entities/shopping-list';
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

import ShoppingListController from '@/actions/App/Http/Controllers/Shopping/ShoppingListController';

type Props = {
    list: ShoppingList | null;
};

defineProps<Props>();

const emit = defineEmits<{
    close: [];
}>();
</script>

<template>
    <Dialog
        :open="list !== null"
        @update:open="
            (open: boolean) => {
                if (!open) emit('close');
            }
        "
    >
        <DialogContent>
            <Form
                v-if="list"
                :action="ShoppingListController.destroy.url(list.id)"
                method="delete"
                reset-on-success
                @success="emit('close')"
                :options="{ preserveScroll: true }"
                class="space-y-6"
                v-slot="{ processing }"
            >
                <DialogHeader>
                    <DialogTitle>Удалить список?</DialogTitle>
                    <DialogDescription>
                        Список «{{ list.name }}» и все его товары будут удалены
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
