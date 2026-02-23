<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@shared/components/InputError.vue';
import TextLink from '@shared/components/TextLink.vue';
import { Button } from '@shared/ui/button';
import { Checkbox } from '@shared/ui/checkbox';
import { Input } from '@shared/ui/input';
import { Label } from '@shared/ui/label';
import { Spinner } from '@shared/ui/spinner';
import { AuthLayout as AuthBase } from '@widgets/auth';

import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';

defineProps<{
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
}>();
</script>

<template>
    <AuthBase
        title="Вход в аккаунт"
        description="Введите email и пароль для входа"
    >
        <Head title="Вход" />

        <div
            v-if="status"
            class="mb-4 rounded-lg bg-accent p-3 text-center text-sm font-medium text-chart-2"
        >
            {{ status }}
        </div>

        <Form
            v-bind="store.form()"
            :reset-on-success="['password']"
            v-slot="{ errors, processing }"
            class="flex flex-col gap-6"
        >
            <div class="grid gap-5">
                <div class="grid gap-2">
                    <Label for="email">Email</Label>
                    <Input
                        id="email"
                        type="email"
                        name="email"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="email"
                        placeholder="name@example.com"
                    />
                    <InputError :message="errors.email" />
                </div>

                <div class="grid gap-2">
                    <div class="flex items-center justify-between">
                        <Label for="password">Пароль</Label>
                        <TextLink
                            v-if="canResetPassword"
                            :href="request()"
                            class="text-xs"
                            :tabindex="5"
                        >
                            Забыли пароль?
                        </TextLink>
                    </div>
                    <Input
                        id="password"
                        type="password"
                        name="password"
                        required
                        :tabindex="2"
                        autocomplete="current-password"
                        placeholder="Введите пароль"
                    />
                    <InputError :message="errors.password" />
                </div>

                <Label for="remember" class="flex items-center gap-3">
                    <Checkbox id="remember" name="remember" :tabindex="3" />
                    <span class="text-sm text-muted-foreground"
                        >Запомнить меня</span
                    >
                </Label>

                <Button
                    type="submit"
                    class="mt-2 w-full"
                    :tabindex="4"
                    :disabled="processing"
                    data-test="login-button"
                >
                    <Spinner v-if="processing" />
                    Войти
                </Button>
            </div>

            <div
                v-if="canRegister"
                class="text-center text-sm text-muted-foreground"
            >
                Нет аккаунта?
                <TextLink :href="register()" :tabindex="5">
                    Зарегистрироваться
                </TextLink>
            </div>
        </Form>
    </AuthBase>
</template>
