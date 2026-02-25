<script setup lang="ts">
import { SettingsLayout } from '@features/settings';
import { TwoFactorManager } from '@features/two-factor';
import { WebAuthnManager } from '@features/webauthn';
import { Head } from '@inertiajs/vue3';
import type { BreadcrumbItem, WebAuthnCredential } from '@shared/types';
import { Separator } from '@shared/ui/separator';
import { AppLayout } from '@widgets/app-shell';

import { show } from '@/routes/two-factor';

type Props = {
    requiresConfirmation?: boolean;
    twoFactorEnabled?: boolean;
    webauthnCredentials?: WebAuthnCredential[];
};

withDefaults(defineProps<Props>(), {
    webauthnCredentials: () => [],
});

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

            <Separator />

            <WebAuthnManager :credentials="webauthnCredentials" />
        </SettingsLayout>
    </AppLayout>
</template>
