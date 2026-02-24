<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { type NavItem } from '@shared/types';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@shared/ui/sidebar';
import { Briefcase, LayoutGrid, List } from 'lucide-vue-next';

import { dashboard } from '@/routes';

import AppLogo from './AppLogo.vue';
import NavMain from './NavMain.vue';
import NavUser from './NavUser.vue';

type Props = {
    auxiliaryNavItems?: NavItem[];
};

withDefaults(defineProps<Props>(), {
    auxiliaryNavItems: () => [],
});

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
</template>
