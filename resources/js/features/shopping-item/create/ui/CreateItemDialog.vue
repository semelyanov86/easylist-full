<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import InputError from '@shared/components/InputError.vue';
import { Button } from '@shared/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@shared/ui/dialog';
import { Input } from '@shared/ui/input';
import { Label } from '@shared/ui/label';
import { FileUp, Hash, Ruler, ShoppingBag, Tag, Wallet } from 'lucide-vue-next';
import { ref } from 'vue';

import ShoppingItemController from '@/actions/App/Http/Controllers/Shopping/ShoppingItemController';

type Props = {
    open: boolean;
    listId: number;
};

defineProps<Props>();

const emit = defineEmits<{
    close: [];
}>();

const fileName = ref<string | null>(null);

const onFileChange = (event: Event): void => {
    const target = event.target as HTMLInputElement;
    fileName.value = target.files?.[0]?.name ?? null;
};

const onFormSuccess = (): void => {
    fileName.value = null;
    emit('close');
};
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
                :action="ShoppingItemController.store.url()"
                method="post"
                reset-on-success
                @success="onFormSuccess"
                :options="{ preserveScroll: true }"
                class="space-y-5"
                v-slot="{ errors, processing }"
            >
                <DialogHeader>
                    <DialogTitle class="flex items-center gap-2">
                        <div
                            class="flex size-8 items-center justify-center rounded-lg bg-primary/10"
                        >
                            <ShoppingBag class="size-4 text-primary" />
                        </div>
                        Добавить товар
                    </DialogTitle>
                </DialogHeader>

                <input type="hidden" name="shopping_list_id" :value="listId" />

                <div class="space-y-4">
                    <div class="grid gap-2">
                        <Label
                            for="create-item-name"
                            class="flex items-center gap-1.5"
                        >
                            <Tag class="size-3.5 text-muted-foreground" />
                            Название
                        </Label>
                        <Input
                            id="create-item-name"
                            name="name"
                            placeholder="Например: Молоко"
                            required
                        />
                        <InputError :message="errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label
                            for="create-item-description"
                            class="flex items-center gap-1.5"
                        >
                            Описание
                        </Label>
                        <Input
                            id="create-item-description"
                            name="description"
                            placeholder="Бренд, заметки..."
                        />
                        <InputError :message="errors.description" />
                    </div>

                    <div
                        class="rounded-lg border border-border bg-muted/30 p-3"
                    >
                        <div class="grid grid-cols-3 gap-3">
                            <div class="grid gap-1.5">
                                <Label
                                    for="create-item-quantity"
                                    class="flex items-center gap-1 text-xs"
                                >
                                    <Hash
                                        class="size-3 text-muted-foreground"
                                    />
                                    Кол-во
                                </Label>
                                <Input
                                    id="create-item-quantity"
                                    name="quantity"
                                    type="number"
                                    min="1"
                                    placeholder="1"
                                    class="h-8 text-sm"
                                />
                                <InputError :message="errors.quantity" />
                            </div>
                            <div class="grid gap-1.5">
                                <Label
                                    for="create-item-quantity-type"
                                    class="flex items-center gap-1 text-xs"
                                >
                                    <Ruler
                                        class="size-3 text-muted-foreground"
                                    />
                                    Единица
                                </Label>
                                <Input
                                    id="create-item-quantity-type"
                                    name="quantity_type"
                                    placeholder="шт, кг, л"
                                    class="h-8 text-sm"
                                />
                                <InputError :message="errors.quantity_type" />
                            </div>
                            <div class="grid gap-1.5">
                                <Label
                                    for="create-item-price"
                                    class="flex items-center gap-1 text-xs"
                                >
                                    <Wallet
                                        class="size-3 text-muted-foreground"
                                    />
                                    Цена
                                </Label>
                                <Input
                                    id="create-item-price"
                                    name="price"
                                    type="number"
                                    min="0"
                                    placeholder="0 ₽"
                                    class="h-8 text-sm"
                                />
                                <InputError :message="errors.price" />
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label class="flex items-center gap-1.5">
                            <FileUp class="size-3.5 text-muted-foreground" />
                            Файл
                        </Label>
                        <label
                            for="create-item-file"
                            class="flex cursor-pointer items-center gap-3 rounded-lg border border-dashed border-border px-3 py-2.5 transition-colors hover:border-ring hover:bg-accent/50"
                        >
                            <div
                                class="flex size-8 shrink-0 items-center justify-center rounded-md bg-muted"
                            >
                                <FileUp class="size-4 text-muted-foreground" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <p
                                    v-if="fileName"
                                    class="truncate text-sm text-foreground"
                                >
                                    {{ fileName }}
                                </p>
                                <p v-else class="text-sm text-muted-foreground">
                                    Нажмите для выбора файла
                                </p>
                                <p class="text-xs text-muted-foreground/70">
                                    До 10 МБ
                                </p>
                            </div>
                        </label>
                        <input
                            id="create-item-file"
                            name="file"
                            type="file"
                            class="sr-only"
                            @change="onFileChange"
                        />
                        <InputError :message="errors.file" />
                    </div>
                </div>

                <DialogFooter class="gap-2">
                    <DialogClose as-child>
                        <Button variant="secondary" @click="emit('close')">
                            Отмена
                        </Button>
                    </DialogClose>
                    <Button type="submit" :disabled="processing">
                        Добавить
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
