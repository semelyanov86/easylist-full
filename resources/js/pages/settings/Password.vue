<script setup lang="ts">
import { PasswordForm, SettingsLayout } from '@features/settings';
import { Head } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@shared/types';
import { AppLayout } from '@widgets/app-shell';

import { edit } from '@/routes/user-password';

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Настройки пароля',
        href: edit().url,
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Настройки пароля" />

        <h1 class="sr-only">Настройки пароля</h1>

        <SettingsLayout>
            <PasswordForm />
        </SettingsLayout>
    </AppLayout>
</template>
