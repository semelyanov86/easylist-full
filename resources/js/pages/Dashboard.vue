<script setup lang="ts">
import { CreateListDialog } from '@features/shopping-list/create';
import { Head } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@shared/types';
import { Button } from '@shared/ui/button';
import { AppLayout } from '@widgets/app-shell';
import { Plus } from 'lucide-vue-next';
import { ref } from 'vue';

import { dashboard } from '@/routes';

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
    </AppLayout>

    <CreateListDialog
        :open="showCreateListDialog"
        @close="showCreateListDialog = false"
    />
</template>
