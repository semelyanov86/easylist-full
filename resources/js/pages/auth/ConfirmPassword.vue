<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@shared/components/InputError.vue';
import { Button } from '@shared/ui/button';
import { Input } from '@shared/ui/input';
import { Label } from '@shared/ui/label';
import { Spinner } from '@shared/ui/spinner';
import { AuthLayout } from '@widgets/auth';

import { store } from '@/routes/password/confirm';
</script>

<template>
    <AuthLayout
        title="Подтверждение пароля"
        description="Это защищённая область приложения. Пожалуйста, подтвердите пароль для продолжения."
    >
        <Head title="Подтверждение пароля" />

        <Form
            v-bind="store.form()"
            reset-on-success
            v-slot="{ errors, processing }"
        >
            <div class="grid gap-5">
                <div class="grid gap-2">
                    <Label for="password">Пароль</Label>
                    <Input
                        id="password"
                        type="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        autofocus
                        placeholder="Введите пароль"
                    />
                    <InputError :message="errors.password" />
                </div>

                <Button
                    class="w-full"
                    :disabled="processing"
                    data-test="confirm-password-button"
                >
                    <Spinner v-if="processing" />
                    Подтвердить
                </Button>
            </div>
        </Form>
    </AuthLayout>
</template>
