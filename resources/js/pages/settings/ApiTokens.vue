<script setup lang="ts">
import { ApiTokenManager } from '@features/api-tokens';
import { SettingsLayout } from '@features/settings';
import { Head } from '@inertiajs/vue3';
import { type ApiToken, type BreadcrumbItem } from '@shared/types';
import { AppLayout } from '@widgets/app-shell';

import { index } from '@/routes/api-tokens';

type Props = {
    tokens: ApiToken[];
    newToken: string | null;
};

defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'API-токены',
        href: index().url,
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="API-токены" />

        <h1 class="sr-only">API-токены</h1>

        <SettingsLayout>
            <ApiTokenManager :tokens="tokens" :new-token="newToken" />
        </SettingsLayout>
    </AppLayout>
</template>
