<script setup lang="ts">
import type { ShoppingItem } from '@entities/shopping-item';
import type { ShoppingList } from '@entities/shopping-list';
import { CreateItemDialog } from '@features/shopping-item/create';
import { router } from '@inertiajs/vue3';
import { Badge } from '@shared/ui/badge';
import { Button } from '@shared/ui/button';
import {
    ArrowLeft,
    ChevronDown,
    GripVertical,
    PackageOpen,
    Plus,
    Star,
    Trash2,
    Undo2,
    X,
} from 'lucide-vue-next';
import Sortable from 'sortablejs';
import { computed, nextTick, onMounted, ref, watch } from 'vue';

import ShoppingItemController, {
    reorder,
} from '@/actions/App/Http/Controllers/Shopping/ShoppingItemController';

type Props = {
    list: ShoppingList | null;
    onBack?: () => void;
};

const props = defineProps<Props>();

const items = computed<ShoppingItem[]>(() => props.list?.items ?? []);

const activeItems = computed<ShoppingItem[]>(() =>
    items.value.filter((i) => !i.is_done),
);

const doneItems = computed<ShoppingItem[]>(() =>
    items.value.filter((i) => i.is_done),
);

const localActiveItems = ref<ShoppingItem[]>([]);
const itemsListRef = ref<HTMLElement | null>(null);
let sortableInstance: Sortable | null = null;

const showCreateDialog = ref(false);
const showDone = ref(true);

watch(
    activeItems,
    (value) => {
        localActiveItems.value = [...value];
    },
    { immediate: true },
);

const initSortable = (): void => {
    if (sortableInstance) {
        sortableInstance.destroy();
        sortableInstance = null;
    }

    if (!itemsListRef.value) {
        return;
    }

    sortableInstance = Sortable.create(itemsListRef.value, {
        handle: '[data-item-handle]',
        animation: 150,
        onEnd: (event) => {
            if (event.oldIndex === undefined || event.newIndex === undefined) {
                return;
            }

            const [moved] = localActiveItems.value.splice(event.oldIndex, 1);
            if (!moved) {
                return;
            }
            localActiveItems.value.splice(event.newIndex, 0, moved);

            const ids = localActiveItems.value.map((i) => i.id);
            router.post(
                reorder().url,
                { ids },
                { preserveScroll: true, preserveState: true },
            );
        },
    });
};

onMounted(() => {
    nextTick(() => initSortable());
});

watch(
    () => itemsListRef.value,
    () => {
        nextTick(() => initSortable());
    },
);

watch(
    () => props.list?.id,
    () => {
        nextTick(() => initSortable());
    },
);

const toggleDone = (item: ShoppingItem): void => {
    router.patch(
        ShoppingItemController.toggleDone.url(item.id),
        {},
        { preserveScroll: true },
    );
};

const deleteItem = (item: ShoppingItem): void => {
    router.delete(ShoppingItemController.destroy.url(item.id), {
        preserveScroll: true,
    });
};

const toggleStar = (item: ShoppingItem): void => {
    router.patch(
        ShoppingItemController.update.url(item.id),
        { is_starred: !item.is_starred },
        { preserveScroll: true },
    );
};

const uncrossAll = (): void => {
    if (!props.list) {
        return;
    }

    router.patch(
        ShoppingItemController.uncrossAll.url(props.list.id),
        {},
        { preserveScroll: true },
    );
};

const destroyAll = (): void => {
    if (!props.list) {
        return;
    }

    router.delete(ShoppingItemController.destroyAll.url(props.list.id), {
        preserveScroll: true,
    });
};

const formatPrice = (price: number | null): string => {
    if (price === null) {
        return '';
    }

    return `${price} ₽`;
};

const fileUrl = (file: string | null): string | null => {
    if (!file) {
        return null;
    }

    return `/storage/${file}`;
};

const isImage = (file: string | null): boolean => {
    if (!file) {
        return false;
    }

    return /\.(jpe?g|png|gif|webp|svg|bmp|avif)$/i.test(file);
};
</script>

<template>
    <div class="flex h-full flex-col">
        <div
            v-if="!list"
            class="flex flex-1 flex-col items-center justify-center gap-3 p-8"
        >
            <div
                class="flex size-14 items-center justify-center rounded-full bg-muted"
            >
                <PackageOpen class="size-6 text-muted-foreground" />
            </div>
            <div class="text-center">
                <p class="text-sm font-medium text-foreground">
                    Выберите список
                </p>
                <p class="mt-1 text-xs text-muted-foreground">
                    Выберите список слева, чтобы увидеть товары
                </p>
            </div>
        </div>

        <template v-else>
            <div
                class="flex items-center justify-between border-b border-border px-5 py-3"
            >
                <div class="flex items-center gap-2.5">
                    <button
                        v-if="onBack"
                        type="button"
                        class="flex size-8 items-center justify-center rounded-md text-muted-foreground hover:bg-accent hover:text-foreground md:hidden"
                        @click="onBack"
                    >
                        <ArrowLeft class="size-4" />
                    </button>
                    <span
                        v-if="list.icon"
                        class="flex size-8 items-center justify-center rounded-lg bg-accent text-lg"
                    >
                        {{ list.icon }}
                    </span>
                    <div>
                        <h2 class="text-sm font-semibold text-foreground">
                            {{ list.name }}
                        </h2>
                        <p class="text-xs text-muted-foreground">
                            {{ activeItems.length }} товаров
                            <span v-if="doneItems.length > 0">
                                · {{ doneItems.length }} куплено
                            </span>
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-1">
                    <Button
                        size="sm"
                        variant="outline"
                        @click="showCreateDialog = true"
                    >
                        <Plus class="size-3.5" />
                        Добавить
                    </Button>
                    <Button
                        v-if="doneItems.length > 0"
                        size="sm"
                        variant="ghost"
                        title="Снять все отметки"
                        @click="uncrossAll"
                    >
                        <Undo2 class="size-4" />
                    </Button>
                    <Button
                        v-if="items.length > 0"
                        size="sm"
                        variant="ghost"
                        title="Удалить все"
                        class="text-destructive hover:text-destructive"
                        @click="destroyAll"
                    >
                        <X class="size-4" />
                    </Button>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto">
                <div ref="itemsListRef" class="p-2">
                    <div
                        v-for="item in localActiveItems"
                        :key="item.id"
                        class="group/item flex items-start gap-2.5 rounded-lg px-3 py-2.5 transition-colors hover:bg-accent/30"
                    >
                        <GripVertical
                            data-item-handle
                            class="mt-0.5 size-4 shrink-0 cursor-grab text-muted-foreground opacity-0 transition-opacity group-hover/item:opacity-100"
                        />
                        <input
                            type="checkbox"
                            :checked="item.is_done"
                            class="mt-0.5 size-4 shrink-0 rounded border-input accent-primary"
                            @change="toggleDone(item)"
                        />
                        <img
                            v-if="isImage(item.file)"
                            :src="fileUrl(item.file)!"
                            :alt="item.name"
                            class="size-10 shrink-0 rounded-lg border border-border object-cover"
                        />
                        <div class="min-w-0 flex-1">
                            <div class="flex items-baseline gap-1.5">
                                <span class="text-sm font-medium">{{
                                    item.name
                                }}</span>
                                <span
                                    v-if="
                                        item.quantity > 1 || item.quantity_type
                                    "
                                    class="text-xs text-muted-foreground"
                                >
                                    {{ item.quantity
                                    }}{{
                                        item.quantity_type
                                            ? ' ' + item.quantity_type
                                            : ''
                                    }}
                                </span>
                            </div>
                            <p
                                v-if="item.description"
                                class="mt-0.5 text-xs leading-relaxed text-muted-foreground"
                            >
                                {{ item.description }}
                            </p>
                        </div>
                        <Badge
                            v-if="item.price"
                            variant="secondary"
                            class="mt-0.5 shrink-0 text-xs"
                        >
                            {{ formatPrice(item.price) }}
                        </Badge>
                        <button
                            type="button"
                            class="mt-0.5 flex size-6 shrink-0 items-center justify-center rounded-md transition-colors"
                            :class="
                                item.is_starred
                                    ? 'text-amber-500'
                                    : 'text-muted-foreground opacity-0 group-hover/item:opacity-100 hover:text-amber-500'
                            "
                            @click="toggleStar(item)"
                        >
                            <Star
                                class="size-3.5"
                                :fill="
                                    item.is_starred ? 'currentColor' : 'none'
                                "
                            />
                        </button>
                        <button
                            type="button"
                            class="mt-0.5 flex size-6 shrink-0 items-center justify-center rounded-md text-muted-foreground opacity-0 transition-opacity group-hover/item:opacity-100 hover:text-destructive"
                            @click="deleteItem(item)"
                        >
                            <Trash2 class="size-3.5" />
                        </button>
                    </div>
                </div>

                <div v-if="doneItems.length > 0" class="px-2 pb-2">
                    <button
                        type="button"
                        class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-xs font-medium text-muted-foreground transition-colors hover:bg-accent/30"
                        @click="showDone = !showDone"
                    >
                        <ChevronDown
                            class="size-3.5 transition-transform"
                            :class="{ '-rotate-90': !showDone }"
                        />
                        Куплено ({{ doneItems.length }})
                    </button>
                    <template v-if="showDone">
                        <div
                            v-for="item in doneItems"
                            :key="item.id"
                            class="group/item flex items-start gap-2.5 rounded-lg px-3 py-2 opacity-50 transition-colors hover:bg-accent/30 hover:opacity-70"
                        >
                            <div class="mt-0.5 size-4 shrink-0" />
                            <input
                                type="checkbox"
                                :checked="item.is_done"
                                class="mt-0.5 size-4 shrink-0 rounded border-input accent-primary"
                                @change="toggleDone(item)"
                            />
                            <img
                                v-if="isImage(item.file)"
                                :src="fileUrl(item.file)!"
                                :alt="item.name"
                                class="size-10 shrink-0 rounded-lg border border-border object-cover"
                            />
                            <div class="min-w-0 flex-1">
                                <div class="flex items-baseline gap-1.5">
                                    <span
                                        class="text-sm font-medium line-through"
                                    >
                                        {{ item.name }}
                                    </span>
                                    <span
                                        v-if="
                                            item.quantity > 1 ||
                                            item.quantity_type
                                        "
                                        class="text-xs text-muted-foreground"
                                    >
                                        {{ item.quantity
                                        }}{{
                                            item.quantity_type
                                                ? ' ' + item.quantity_type
                                                : ''
                                        }}
                                    </span>
                                </div>
                                <p
                                    v-if="item.description"
                                    class="mt-0.5 text-xs leading-relaxed text-muted-foreground line-through"
                                >
                                    {{ item.description }}
                                </p>
                            </div>
                            <Badge
                                v-if="item.price"
                                variant="secondary"
                                class="mt-0.5 shrink-0 text-xs"
                            >
                                {{ formatPrice(item.price) }}
                            </Badge>
                            <button
                                type="button"
                                class="mt-0.5 flex size-6 shrink-0 items-center justify-center rounded-md text-muted-foreground opacity-0 transition-opacity group-hover/item:opacity-100 hover:text-destructive"
                                @click="deleteItem(item)"
                            >
                                <Trash2 class="size-3.5" />
                            </button>
                        </div>
                    </template>
                </div>

                <div
                    v-if="items.length === 0"
                    class="flex flex-col items-center justify-center gap-3 p-12"
                >
                    <div
                        class="flex size-12 items-center justify-center rounded-full bg-muted"
                    >
                        <PackageOpen class="size-5 text-muted-foreground" />
                    </div>
                    <div class="text-center">
                        <p class="text-sm font-medium text-foreground">
                            Список пуст
                        </p>
                        <p class="mt-1 text-xs text-muted-foreground">
                            Добавьте первый товар в список
                        </p>
                    </div>
                    <Button
                        size="sm"
                        variant="outline"
                        class="mt-1"
                        @click="showCreateDialog = true"
                    >
                        <Plus class="size-3.5" />
                        Добавить товар
                    </Button>
                </div>
            </div>
        </template>
    </div>

    <CreateItemDialog
        v-if="list"
        :open="showCreateDialog"
        :list-id="list.id"
        @close="showCreateDialog = false"
    />
</template>
