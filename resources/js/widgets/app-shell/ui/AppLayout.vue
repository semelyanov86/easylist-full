<script setup lang="ts">
import type { BreadcrumbItem, NavItem } from '@shared/types';

import AppLayout from './AppSidebarLayout.vue';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
    auxiliaryNavItems?: NavItem[];
    showJobCategories?: boolean;
    showShoppingFolders?: boolean;
};

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
    auxiliaryNavItems: () => [],
    showJobCategories: false,
    showShoppingFolders: false,
});
</script>

<template>
    <AppLayout
        :breadcrumbs="breadcrumbs"
        :auxiliary-nav-items="auxiliaryNavItems"
        :show-job-categories="showJobCategories"
        :show-shopping-folders="showShoppingFolders"
    >
        <template #header-actions>
            <slot name="header-actions" />
        </template>
        <slot />
    </AppLayout>
</template>
