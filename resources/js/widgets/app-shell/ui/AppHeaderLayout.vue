<script setup lang="ts">
import type { BreadcrumbItem } from '@shared/types';

import AppContent from './AppContent.vue';
import AppHeader from './AppHeader.vue';
import AppShell from './AppShell.vue';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AppShell class="flex-col">
        <AppHeader :breadcrumbs="breadcrumbs" />
        <AppContent>
            <slot />
        </AppContent>
    </AppShell>
</template>
