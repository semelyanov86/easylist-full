<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import TextLink from '@shared/components/TextLink.vue';
import { Button } from '@shared/ui/button';
import { Spinner } from '@shared/ui/spinner';
import { AuthLayout } from '@widgets/auth';

import { logout } from '@/routes';
import { send } from '@/routes/verification';

defineProps<{
    status?: string;
}>();
</script>

<template>
    <AuthLayout
        title="Подтверждение email"
        description="Мы отправили ссылку для подтверждения на ваш email. Проверьте почту и перейдите по ссылке."
    >
        <Head title="Подтверждение email" />

        <div
            v-if="status === 'verification-link-sent'"
            class="mb-4 rounded-lg bg-accent p-3 text-center text-sm font-medium text-chart-2"
        >
            Новая ссылка для подтверждения отправлена на указанный при
            регистрации email.
        </div>

        <Form
            v-bind="send.form()"
            class="space-y-6 text-center"
            v-slot="{ processing }"
        >
            <Button :disabled="processing" variant="secondary" class="w-full">
                <Spinner v-if="processing" />
                Отправить ссылку повторно
            </Button>

            <TextLink
                :href="logout()"
                as="button"
                class="mx-auto block text-sm"
            >
                Выйти
            </TextLink>
        </Form>
    </AuthLayout>
</template>
