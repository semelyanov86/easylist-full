<script setup lang="ts">
import { SettingsLayout } from '@features/settings';
import { TwoFactorManager } from '@features/two-factor';
import { Head } from '@inertiajs/vue3';
import type { BreadcrumbItem } from '@shared/types';
import { AppLayout } from '@widgets/app-shell';

import { show } from '@/routes/two-factor';

type Props = {
    requiresConfirmation?: boolean;
    twoFactorEnabled?: boolean;
};

defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Двухфакторная аутентификация',
        href: show.url(),
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Двухфакторная аутентификация" />

        <h1 class="sr-only">Двухфакторная аутентификация</h1>

        <SettingsLayout>
            <TwoFactorManager
                :requires-confirmation="requiresConfirmation"
                :two-factor-enabled="twoFactorEnabled"
            />
        </SettingsLayout>
    </AppLayout>
</template>
