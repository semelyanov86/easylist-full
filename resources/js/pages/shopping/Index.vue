<script setup lang="ts">
import type { Folder } from '@entities/folder';
import type { ShoppingList } from '@entities/shopping-list';
import { Head, router } from '@inertiajs/vue3';
import type { BreadcrumbItem } from '@shared/types';
import { AppLayout } from '@widgets/app-shell';
import { ShoppingItemsPanel } from '@widgets/shopping-items';
import { ShoppingListsPanel } from '@widgets/shopping-lists';
import { computed } from 'vue';

import { index as shoppingIndex } from '@/actions/App/Http/Controllers/Shopping/ShoppingController';

type Props = {
    folders: Folder[];
    lists: ShoppingList[];
    selectedList: ShoppingList | null;
    selectedFolderId: number | null;
};

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Списки покупок',
        href: shoppingIndex().url,
    },
];

const selectedListId = computed<number | null>(
    () => props.selectedList?.id ?? null,
);

const handleBack = (): void => {
    const query: Record<string, number> = {};

    if (props.selectedFolderId !== null) {
        query.folder_id = props.selectedFolderId;
    }

    router.visit(shoppingIndex({ query }).url);
};
</script>

<template>
    <Head title="Списки покупок" />

    <AppLayout :breadcrumbs="breadcrumbs" show-shopping-folders>
        <div class="flex h-[calc(100vh-4rem)] p-4">
            <div
                class="flex min-h-0 w-full overflow-hidden rounded-xl border border-border bg-background shadow-sm"
            >
                <div
                    class="w-full shrink-0 border-r border-border bg-muted/30 md:w-72"
                    :class="{ 'hidden md:block': selectedList }"
                >
                    <ShoppingListsPanel
                        :lists="lists"
                        :folders="folders"
                        :selected-list-id="selectedListId"
                        :selected-folder-id="selectedFolderId"
                    />
                </div>
                <div
                    class="min-w-0 flex-1"
                    :class="{ 'hidden md:block': !selectedList }"
                >
                    <ShoppingItemsPanel
                        :list="selectedList"
                        :on-back="handleBack"
                    />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
