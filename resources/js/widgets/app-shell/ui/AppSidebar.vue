<script setup lang="ts">
import type { Folder } from '@entities/folder';
import type { JobCategory } from '@entities/job-category';
import { CreateFolderDialog } from '@features/folder/create';
import { DeleteFolderDialog } from '@features/folder/delete';
import { EditFolderDialog } from '@features/folder/edit';
import { CreateCategoryDialog } from '@features/job-category/create';
import { DeleteCategoryDialog } from '@features/job-category/delete';
import { EditCategoryDialog } from '@features/job-category/edit';
import { Link, router, usePage } from '@inertiajs/vue3';
import { type NavItem } from '@shared/types';
import { Badge } from '@shared/ui/badge';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarGroup,
    SidebarGroupAction,
    SidebarGroupLabel,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@shared/ui/sidebar';
import {
    Briefcase,
    GripVertical,
    Heart,
    LayoutGrid,
    Pencil,
    Plus,
    ShoppingCart,
    Trash2,
} from 'lucide-vue-next';
import Sortable from 'sortablejs';
import { computed, nextTick, onMounted, ref, watch } from 'vue';

import { reorder } from '@/actions/App/Http/Controllers/Settings/JobCategoryController';
import { reorder as reorderFolders } from '@/actions/App/Http/Controllers/Shopping/FolderController';
import { index as shoppingIndex } from '@/actions/App/Http/Controllers/Shopping/ShoppingController';
import { dashboard } from '@/routes';
import { index as jobsIndex } from '@/routes/jobs';

import AppLogo from './AppLogo.vue';
import NavMain from './NavMain.vue';
import NavUser from './NavUser.vue';

type Props = {
    auxiliaryNavItems?: NavItem[];
    showJobCategories?: boolean;
    showShoppingFolders?: boolean;
};

withDefaults(defineProps<Props>(), {
    auxiliaryNavItems: () => [],
    showJobCategories: false,
    showShoppingFolders: false,
});

const page = usePage();

const jobCategories = computed<JobCategory[]>(
    () => (page.props.jobCategories as JobCategory[]) ?? [],
);

const activeCategoryId = computed<number | null>(() => {
    const url = new URL(page.url, window.location.origin);
    const param = url.searchParams.get('job_category_id');

    return param !== null ? Number(param) : null;
});

const favoritesCount = computed<number>(
    () => (page.props.favoritesCount as number) ?? 0,
);

const isCurrentFavorites = computed<boolean>(() => {
    const url = new URL(page.url, window.location.origin);

    return url.searchParams.get('is_favorite') === '1';
});

const localCategories = ref<JobCategory[]>([]);
const categoryListRef = ref<InstanceType<typeof SidebarMenu> | null>(null);
let sortableInstance: Sortable | null = null;

const showCreateDialog = ref(false);
const categoryToEdit = ref<JobCategory | null>(null);
const categoryToDelete = ref<JobCategory | null>(null);

// Папки списков покупок
const shoppingFolders = computed<Folder[]>(
    () => (page.props.shoppingFolders as Folder[]) ?? [],
);

const activeFolderId = computed<number | null>(() => {
    const url = new URL(page.url, window.location.origin);
    const param = url.searchParams.get('folder_id');

    return param !== null ? Number(param) : null;
});

const localFolders = ref<Folder[]>([]);
const folderListRef = ref<InstanceType<typeof SidebarMenu> | null>(null);
let folderSortableInstance: Sortable | null = null;

const showCreateFolderDialog = ref(false);
const folderToEdit = ref<Folder | null>(null);
const folderToDelete = ref<Folder | null>(null);

watch(
    jobCategories,
    (value) => {
        localCategories.value = [...value];
    },
    { immediate: true },
);

const initSortable = (): void => {
    if (sortableInstance) {
        sortableInstance.destroy();
        sortableInstance = null;
    }

    const el = categoryListRef.value?.$el as HTMLElement | undefined;
    if (!el) {
        return;
    }

    sortableInstance = Sortable.create(el, {
        handle: '[data-category-handle]',
        animation: 150,
        onEnd: (event) => {
            if (event.oldIndex === undefined || event.newIndex === undefined) {
                return;
            }

            const [moved] = localCategories.value.splice(event.oldIndex, 1);
            if (!moved) {
                return;
            }
            localCategories.value.splice(event.newIndex, 0, moved);

            const ids = localCategories.value.map((c) => c.id);
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
    () => categoryListRef.value?.$el,
    () => {
        nextTick(() => initSortable());
    },
);

watch(
    shoppingFolders,
    (value) => {
        localFolders.value = [...value];
    },
    { immediate: true },
);

const initFolderSortable = (): void => {
    if (folderSortableInstance) {
        folderSortableInstance.destroy();
        folderSortableInstance = null;
    }

    const el = folderListRef.value?.$el as HTMLElement | undefined;
    if (!el) {
        return;
    }

    folderSortableInstance = Sortable.create(el, {
        handle: '[data-folder-handle]',
        animation: 150,
        onEnd: (event) => {
            if (event.oldIndex === undefined || event.newIndex === undefined) {
                return;
            }

            const [moved] = localFolders.value.splice(event.oldIndex, 1);
            if (!moved) {
                return;
            }
            localFolders.value.splice(event.newIndex, 0, moved);

            const ids = localFolders.value.map((f) => f.id);
            router.post(
                reorderFolders().url,
                { ids },
                { preserveScroll: true, preserveState: true },
            );
        },
    });
};

onMounted(() => {
    nextTick(() => initFolderSortable());
});

watch(
    () => folderListRef.value?.$el,
    () => {
        nextTick(() => initFolderSortable());
    },
);

const mainNavItems: NavItem[] = [
    {
        title: 'Панель управления',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Job Tracker',
        href: jobsIndex(),
        icon: Briefcase,
    },
    {
        title: 'Списки',
        href: shoppingIndex(),
        icon: ShoppingCart,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" label="Основное" />

            <SidebarGroup v-if="showJobCategories" class="px-2 py-0">
                <SidebarGroupLabel>Мои категории</SidebarGroupLabel>
                <SidebarGroupAction @click="showCreateDialog = true">
                    <Plus />
                    <span class="sr-only">Создать категорию</span>
                </SidebarGroupAction>
                <SidebarMenu ref="categoryListRef">
                    <SidebarMenuItem
                        v-for="category in localCategories"
                        :key="category.id"
                        class="group/category"
                    >
                        <SidebarMenuButton
                            as-child
                            :is-active="activeCategoryId === category.id"
                            :tooltip="category.title"
                        >
                            <Link
                                :href="
                                    jobsIndex({
                                        query: {
                                            job_category_id: category.id,
                                        },
                                    })
                                "
                                class="flex items-center gap-2"
                            >
                                <GripVertical
                                    data-category-handle
                                    class="size-4 shrink-0 cursor-grab text-muted-foreground opacity-0 transition-opacity group-hover/category:opacity-100"
                                />
                                <span>{{ category.title }}</span>
                            </Link>
                        </SidebarMenuButton>
                        <div
                            class="absolute top-1 right-1 flex items-center gap-0.5 opacity-0 transition-opacity group-hover/category:opacity-100 group-data-[collapsible=icon]:hidden"
                        >
                            <button
                                type="button"
                                class="flex size-5 items-center justify-center rounded-md text-sidebar-foreground ring-sidebar-ring outline-hidden hover:bg-sidebar-accent hover:text-sidebar-accent-foreground focus-visible:ring-2"
                                @click.stop="categoryToEdit = category"
                            >
                                <Pencil class="size-3" />
                            </button>
                            <button
                                type="button"
                                class="flex size-5 items-center justify-center rounded-md text-sidebar-foreground ring-sidebar-ring outline-hidden hover:bg-sidebar-accent hover:text-sidebar-accent-foreground focus-visible:ring-2"
                                @click.stop="categoryToDelete = category"
                            >
                                <Trash2 class="size-3" />
                            </button>
                        </div>
                    </SidebarMenuItem>
                </SidebarMenu>

                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton
                            as-child
                            :is-active="isCurrentFavorites"
                            tooltip="Избранное"
                        >
                            <Link
                                :href="
                                    jobsIndex({
                                        query: { is_favorite: 1 },
                                    })
                                "
                                class="flex items-center gap-2"
                            >
                                <Heart class="size-4 shrink-0" />
                                <span>Избранное</span>
                                <Badge
                                    v-if="favoritesCount > 0"
                                    variant="secondary"
                                    class="ml-auto px-1.5 py-0 text-xs leading-5"
                                >
                                    {{ favoritesCount }}
                                </Badge>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarGroup>

            <SidebarGroup v-if="showShoppingFolders" class="px-2 py-0">
                <SidebarGroupLabel>Мои папки</SidebarGroupLabel>
                <SidebarGroupAction @click="showCreateFolderDialog = true">
                    <Plus />
                    <span class="sr-only">Создать папку</span>
                </SidebarGroupAction>
                <SidebarMenu ref="folderListRef">
                    <SidebarMenuItem
                        v-for="folder in localFolders"
                        :key="folder.id"
                        class="group/folder"
                    >
                        <SidebarMenuButton
                            as-child
                            :is-active="activeFolderId === folder.id"
                            :tooltip="folder.name"
                        >
                            <Link
                                :href="
                                    shoppingIndex({
                                        query: {
                                            folder_id: folder.id,
                                        },
                                    }).url
                                "
                                class="flex items-center gap-2"
                            >
                                <GripVertical
                                    data-folder-handle
                                    class="size-4 shrink-0 cursor-grab text-muted-foreground opacity-0 transition-opacity group-hover/folder:opacity-100"
                                />
                                <span>{{
                                    folder.icon
                                        ? folder.icon + ' ' + folder.name
                                        : folder.name
                                }}</span>
                            </Link>
                        </SidebarMenuButton>
                        <div
                            class="absolute top-1 right-1 flex items-center gap-0.5 opacity-0 transition-opacity group-hover/folder:opacity-100 group-data-[collapsible=icon]:hidden"
                        >
                            <button
                                type="button"
                                class="flex size-5 items-center justify-center rounded-md text-sidebar-foreground ring-sidebar-ring outline-hidden hover:bg-sidebar-accent hover:text-sidebar-accent-foreground focus-visible:ring-2"
                                @click.stop="folderToEdit = folder"
                            >
                                <Pencil class="size-3" />
                            </button>
                            <button
                                type="button"
                                class="flex size-5 items-center justify-center rounded-md text-sidebar-foreground ring-sidebar-ring outline-hidden hover:bg-sidebar-accent hover:text-sidebar-accent-foreground focus-visible:ring-2"
                                @click.stop="folderToDelete = folder"
                            >
                                <Trash2 class="size-3" />
                            </button>
                        </div>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarGroup>

            <NavMain
                v-if="auxiliaryNavItems.length > 0"
                :items="auxiliaryNavItems"
                label="Вспомогательное"
            />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />

    <CreateCategoryDialog
        :open="showCreateDialog"
        @close="showCreateDialog = false"
    />
    <EditCategoryDialog
        :category="categoryToEdit"
        @close="categoryToEdit = null"
    />
    <DeleteCategoryDialog
        :category="categoryToDelete"
        @close="categoryToDelete = null"
    />

    <CreateFolderDialog
        :open="showCreateFolderDialog"
        @close="showCreateFolderDialog = false"
    />
    <EditFolderDialog :folder="folderToEdit" @close="folderToEdit = null" />
    <DeleteFolderDialog
        :folder="folderToDelete"
        @close="folderToDelete = null"
    />
</template>
