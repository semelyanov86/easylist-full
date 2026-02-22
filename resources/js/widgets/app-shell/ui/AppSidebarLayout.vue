<script setup lang="ts">
import type { BreadcrumbItem } from '@shared/types';

import AppContent from './AppContent.vue';
import AppShell from './AppShell.vue';
import AppSidebar from './AppSidebar.vue';
import AppSidebarHeader from './AppSidebarHeader.vue';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AppShell variant="sidebar">
        <AppSidebar />
        <AppContent variant="sidebar" class="overflow-x-hidden">
            <AppSidebarHeader :breadcrumbs="breadcrumbs" />
            <slot />
        </AppContent>
    </AppShell>
</template>
