<script setup lang="ts">
import type { BreadcrumbItem, NavItem } from '@shared/types';

import AppContent from './AppContent.vue';
import AppShell from './AppShell.vue';
import AppSidebar from './AppSidebar.vue';
import AppSidebarHeader from './AppSidebarHeader.vue';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
    auxiliaryNavItems?: NavItem[];
};

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
    auxiliaryNavItems: () => [],
});
</script>

<template>
    <AppShell variant="sidebar">
        <AppSidebar :auxiliary-nav-items="auxiliaryNavItems" />
        <AppContent variant="sidebar" class="overflow-x-hidden">
            <AppSidebarHeader :breadcrumbs="breadcrumbs">
                <template #header-actions>
                    <slot name="header-actions" />
                </template>
            </AppSidebarHeader>
            <slot />
        </AppContent>
    </AppShell>
</template>
