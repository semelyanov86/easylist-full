<script setup lang="ts">
import type { BreadcrumbItem, NavItem } from '@shared/types';

import AppLayout from './AppSidebarLayout.vue';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
    auxiliaryNavItems?: NavItem[];
    showJobCategories?: boolean;
};

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
    auxiliaryNavItems: () => [],
    showJobCategories: false,
});
</script>

<template>
    <AppLayout
        :breadcrumbs="breadcrumbs"
        :auxiliary-nav-items="auxiliaryNavItems"
        :show-job-categories="showJobCategories"
    >
        <template #header-actions>
            <slot name="header-actions" />
        </template>
        <slot />
    </AppLayout>
</template>
