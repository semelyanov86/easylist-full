<script setup lang="ts">
import type { DashboardActivityItem } from '@entities/activity';
import { CreateListDialog } from '@features/shopping-list/create';
import { Deferred, Head } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@shared/types';
import { Button } from '@shared/ui/button';
import { AppLayout } from '@widgets/app-shell';
import {
    DashboardActivitySkeleton,
    DashboardActivityWidget,
} from '@widgets/dashboard-activity';
import { Plus } from 'lucide-vue-next';
import { ref } from 'vue';

import { dashboard } from '@/routes';

defineProps<{
    recentActivities: DashboardActivityItem[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Панель управления',
        href: dashboard().url,
    },
];

const showCreateListDialog = ref(false);
</script>

<template>
    <Head title="Панель управления" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <template #header-actions>
            <Button size="sm">
                <Plus />
                Добавить вакансию
            </Button>
            <Button
                size="sm"
                variant="outline"
                @click="showCreateListDialog = true"
            >
                <Plus />
                Добавить список
            </Button>
        </template>

        <div class="p-4">
            <div class="w-full max-w-sm">
                <Deferred data="recentActivities">
                    <template #fallback>
                        <DashboardActivitySkeleton />
                    </template>

                    <DashboardActivityWidget :activities="recentActivities" />
                </Deferred>
            </div>
        </div>
    </AppLayout>

    <CreateListDialog
        :open="showCreateListDialog"
        @close="showCreateListDialog = false"
    />
</template>
