<script setup lang="ts">
import { DeleteUser, ProfileForm, SettingsLayout } from '@features/settings';
import { Head } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@shared/types';
import { AppLayout } from '@widgets/app-shell';

import { edit } from '@/routes/profile';

type Props = {
    mustVerifyEmail: boolean;
    status?: string;
};

defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Настройки профиля',
        href: edit().url,
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Настройки профиля" />

        <h1 class="sr-only">Настройки профиля</h1>

        <SettingsLayout>
            <ProfileForm
                :must-verify-email="mustVerifyEmail"
                :status="status"
            />

            <DeleteUser />
        </SettingsLayout>
    </AppLayout>
</template>
