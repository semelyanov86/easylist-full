<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@shared/components/InputError.vue';
import TextLink from '@shared/components/TextLink.vue';
import { Button } from '@shared/ui/button';
import { Input } from '@shared/ui/input';
import { Label } from '@shared/ui/label';
import { Spinner } from '@shared/ui/spinner';
import { AuthLayout } from '@widgets/auth';

import { login } from '@/routes';
import { email } from '@/routes/password';

defineProps<{
    status?: string;
}>();
</script>

<template>
    <AuthLayout
        title="Восстановление пароля"
        description="Введите email для получения ссылки на сброс пароля"
    >
        <Head title="Восстановление пароля" />

        <div
            v-if="status"
            class="mb-4 rounded-lg bg-accent p-3 text-center text-sm font-medium text-chart-2"
        >
            {{ status }}
        </div>

        <div class="space-y-6">
            <Form v-bind="email.form()" v-slot="{ errors, processing }">
                <div class="grid gap-5">
                    <div class="grid gap-2">
                        <Label for="email">Email</Label>
                        <Input
                            id="email"
                            type="email"
                            name="email"
                            autocomplete="off"
                            autofocus
                            placeholder="name@example.com"
                        />
                        <InputError :message="errors.email" />
                    </div>

                    <Button
                        class="w-full"
                        :disabled="processing"
                        data-test="email-password-reset-link-button"
                    >
                        <Spinner v-if="processing" />
                        Отправить ссылку для сброса
                    </Button>
                </div>
            </Form>

            <div class="text-center text-sm text-muted-foreground">
                <span>Вспомнили пароль?</span>
                {{ ' ' }}
                <TextLink :href="login()">Войти</TextLink>
            </div>
        </div>
    </AuthLayout>
</template>
