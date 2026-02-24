<script setup lang="ts">
import type { BreadcrumbItem, NavItem } from '@shared/types';

import AppLayout from './AppSidebarLayout.vue';

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
    <AppLayout
        :breadcrumbs="breadcrumbs"
        :auxiliary-nav-items="auxiliaryNavItems"
    >
        <template #header-actions>
            <slot name="header-actions" />
        </template>
        <slot />
    </AppLayout>
</template>
