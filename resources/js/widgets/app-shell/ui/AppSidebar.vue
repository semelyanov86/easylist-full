<script setup lang="ts">
import type { JobCategory } from '@entities/job-category';
import { CreateCategoryDialog } from '@features/job-category/create';
import { DeleteCategoryDialog } from '@features/job-category/delete';
import { EditCategoryDialog } from '@features/job-category/edit';
import { Link, router, usePage } from '@inertiajs/vue3';
import { type NavItem } from '@shared/types';
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
    LayoutGrid,
    List,
    Pencil,
    Plus,
    Trash2,
} from 'lucide-vue-next';
import Sortable from 'sortablejs';
import { computed, nextTick, onMounted, ref, watch } from 'vue';

import { reorder } from '@/actions/App/Http/Controllers/Settings/JobCategoryController';
import { dashboard } from '@/routes';

import AppLogo from './AppLogo.vue';
import NavMain from './NavMain.vue';
import NavUser from './NavUser.vue';

type Props = {
    auxiliaryNavItems?: NavItem[];
    showJobCategories?: boolean;
};

withDefaults(defineProps<Props>(), {
    auxiliaryNavItems: () => [],
    showJobCategories: false,
});

const page = usePage();

const jobCategories = computed<JobCategory[]>(
    () => (page.props.jobCategories as JobCategory[]) ?? [],
);

const localCategories = ref<JobCategory[]>([]);
const categoryListRef = ref<HTMLElement | null>(null);
let sortableInstance: Sortable | null = null;

const showCreateDialog = ref(false);
const categoryToEdit = ref<JobCategory | null>(null);
const categoryToDelete = ref<JobCategory | null>(null);

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

    if (!categoryListRef.value) {
        return;
    }

    sortableInstance = Sortable.create(categoryListRef.value, {
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
    () => categoryListRef.value,
    () => {
        nextTick(() => initSortable());
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
        href: '#',
        icon: Briefcase,
    },
    {
        title: 'Список',
        href: '#',
        icon: List,
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
                        <SidebarMenuButton as-child :tooltip="category.title">
                            <a href="#" class="flex items-center gap-2">
                                <GripVertical
                                    data-category-handle
                                    class="size-4 shrink-0 cursor-grab text-muted-foreground opacity-0 transition-opacity group-hover/category:opacity-100"
                                />
                                <span>{{ category.title }}</span>
                            </a>
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
</template>
