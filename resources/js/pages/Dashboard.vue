<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@shared/types';
import { Button } from '@shared/ui/button';
import { AppLayout } from '@widgets/app-shell';
import { Plus } from 'lucide-vue-next';

import { dashboard } from '@/routes';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Панель управления',
        href: dashboard().url,
    },
];
</script>

<template>
    <Head title="Панель управления" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <template #header-actions>
            <Button size="sm">
                <Plus />
                Добавить вакансию
            </Button>
            <Button size="sm" variant="outline">
                <Plus />
                Добавить список
            </Button>
        </template>
    </AppLayout>
</template>
