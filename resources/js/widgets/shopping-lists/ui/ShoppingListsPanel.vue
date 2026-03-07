<script setup lang="ts">
import type { Folder } from '@entities/folder';
import type { ShoppingList } from '@entities/shopping-list';
import { CreateListDialog } from '@features/shopping-list/create';
import { DeleteListDialog } from '@features/shopping-list/delete';
import { EditListDialog } from '@features/shopping-list/edit';
import { Link, router } from '@inertiajs/vue3';
import { Button } from '@shared/ui/button';
import {
    GripVertical,
    List,
    Pencil,
    Plus,
    ShoppingCart,
    Trash2,
} from 'lucide-vue-next';
import Sortable from 'sortablejs';
import { nextTick, onMounted, ref, watch } from 'vue';

import { index as shoppingIndex } from '@/actions/App/Http/Controllers/Shopping/ShoppingController';
import { reorder } from '@/actions/App/Http/Controllers/Shopping/ShoppingListController';

type Props = {
    lists: ShoppingList[];
    folders: Folder[];
    selectedListId: number | null;
    selectedFolderId: number | null;
};

const props = defineProps<Props>();

const localLists = ref<ShoppingList[]>([]);
const listRef = ref<HTMLElement | null>(null);
let sortableInstance: Sortable | null = null;

const showCreateDialog = ref(false);
const listToEdit = ref<ShoppingList | null>(null);
const listToDelete = ref<ShoppingList | null>(null);

watch(
    () => props.lists,
    (value) => {
        localLists.value = [...value];
    },
    { immediate: true },
);

const initSortable = (): void => {
    if (sortableInstance) {
        sortableInstance.destroy();
        sortableInstance = null;
    }

    if (!listRef.value) {
        return;
    }

    sortableInstance = Sortable.create(listRef.value, {
        handle: '[data-list-handle]',
        animation: 150,
        onEnd: (event) => {
            if (event.oldIndex === undefined || event.newIndex === undefined) {
                return;
            }

            const [moved] = localLists.value.splice(event.oldIndex, 1);
            if (!moved) {
                return;
            }
            localLists.value.splice(event.newIndex, 0, moved);

            const ids = localLists.value.map((l) => l.id);
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
    () => listRef.value,
    () => {
        nextTick(() => initSortable());
    },
);

const buildListHref = (listId: number): string => {
    const query: Record<string, number> = { list_id: listId };

    if (props.selectedFolderId !== null) {
        query.folder_id = props.selectedFolderId;
    }

    return shoppingIndex({ query }).url;
};
</script>

<template>
    <div class="flex h-full flex-col">
        <div class="flex items-center justify-between px-4 py-3">
            <h2
                class="text-xs font-semibold tracking-wider text-muted-foreground uppercase"
            >
                Списки
            </h2>
            <Button
                size="sm"
                variant="ghost"
                class="size-7"
                @click="showCreateDialog = true"
            >
                <Plus class="size-4" />
            </Button>
        </div>

        <div
            v-if="localLists.length === 0"
            class="flex flex-1 flex-col items-center justify-center gap-3 p-6"
        >
            <div
                class="flex size-12 items-center justify-center rounded-full bg-muted"
            >
                <ShoppingCart class="size-5 text-muted-foreground" />
            </div>
            <div class="text-center">
                <p class="text-sm font-medium text-foreground">Нет списков</p>
                <p class="mt-1 text-xs text-muted-foreground">
                    Создайте первый список покупок
                </p>
            </div>
            <Button
                size="sm"
                variant="outline"
                class="mt-1"
                @click="showCreateDialog = true"
            >
                <Plus class="size-3.5" />
                Создать список
            </Button>
        </div>

        <div
            v-else
            ref="listRef"
            class="flex-1 space-y-0.5 overflow-y-auto px-2 pb-2"
        >
            <div v-for="list in localLists" :key="list.id" class="group/list">
                <Link
                    :href="buildListHref(list.id)"
                    class="flex items-center gap-2 rounded-lg px-2.5 py-2 text-sm transition-colors hover:bg-accent/50"
                    :class="{
                        'bg-accent text-accent-foreground shadow-sm':
                            selectedListId === list.id,
                    }"
                >
                    <GripVertical
                        data-list-handle
                        class="size-4 shrink-0 cursor-grab text-muted-foreground opacity-0 transition-opacity group-hover/list:opacity-100"
                    />
                    <span
                        v-if="list.icon"
                        class="flex size-7 shrink-0 items-center justify-center rounded-md bg-accent/50 text-sm"
                    >
                        {{ list.icon }}
                    </span>
                    <div
                        v-else
                        class="flex size-7 shrink-0 items-center justify-center rounded-md bg-accent/50"
                    >
                        <List class="size-3.5 text-muted-foreground" />
                    </div>
                    <span class="min-w-0 flex-1 truncate">{{ list.name }}</span>
                    <div
                        class="flex shrink-0 items-center gap-0.5 opacity-0 transition-opacity group-hover/list:opacity-100"
                    >
                        <button
                            type="button"
                            class="flex size-6 items-center justify-center rounded-md text-muted-foreground hover:bg-background hover:text-foreground"
                            @click.prevent="listToEdit = list"
                        >
                            <Pencil class="size-3" />
                        </button>
                        <button
                            type="button"
                            class="flex size-6 items-center justify-center rounded-md text-muted-foreground hover:bg-destructive/10 hover:text-destructive"
                            @click.prevent="listToDelete = list"
                        >
                            <Trash2 class="size-3" />
                        </button>
                    </div>
                </Link>
            </div>
        </div>
    </div>

    <CreateListDialog
        :open="showCreateDialog"
        :folders="folders"
        :default-folder-id="selectedFolderId"
        @close="showCreateDialog = false"
    />
    <EditListDialog
        :list="listToEdit"
        :folders="folders"
        @close="listToEdit = null"
    />
    <DeleteListDialog :list="listToDelete" @close="listToDelete = null" />
</template>
