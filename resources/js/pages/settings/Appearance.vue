<script setup lang="ts">
import { AppearanceTabs, SettingsLayout } from '@features/settings';
import { Head } from '@inertiajs/vue3';
import Heading from '@shared/components/Heading.vue';
import { type BreadcrumbItem } from '@shared/types';
import { AppLayout } from '@widgets/app-shell';

import { edit } from '@/routes/appearance';

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Appearance settings',
        href: edit().url,
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Appearance settings" />

        <h1 class="sr-only">Appearance Settings</h1>

        <SettingsLayout>
            <div class="space-y-6">
                <Heading
                    variant="small"
                    title="Appearance settings"
                    description="Update your account's appearance settings"
                />
                <AppearanceTabs />
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
