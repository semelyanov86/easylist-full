<script setup lang="ts">
import { AppearanceTabs, SettingsLayout } from '@features/settings';
import { Head } from '@inertiajs/vue3';
import Heading from '@shared/components/Heading.vue';
import { type BreadcrumbItem } from '@shared/types';
import { AppLayout } from '@widgets/app-shell';

import { edit } from '@/routes/appearance';

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Настройки оформления',
        href: edit().url,
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Настройки оформления" />

        <h1 class="sr-only">Настройки оформления</h1>

        <SettingsLayout>
            <div class="space-y-6">
                <Heading
                    variant="small"
                    title="Оформление"
                    description="Настройте внешний вид приложения"
                />
                <AppearanceTabs />
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
