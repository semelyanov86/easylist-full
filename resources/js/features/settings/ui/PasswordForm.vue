<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import Heading from '@shared/components/Heading.vue';
import InputError from '@shared/components/InputError.vue';
import { Button } from '@shared/ui/button';
import { Input } from '@shared/ui/input';
import { Label } from '@shared/ui/label';

import PasswordController from '@/actions/App/Http/Controllers/Settings/PasswordController';
</script>

<template>
    <div class="space-y-6">
        <Heading
            variant="small"
            title="Обновление пароля"
            description="Используйте длинный случайный пароль для безопасности вашего аккаунта"
        />

        <Form
            v-bind="PasswordController.update.form()"
            :options="{
                preserveScroll: true,
            }"
            reset-on-success
            :reset-on-error="[
                'password',
                'password_confirmation',
                'current_password',
            ]"
            class="space-y-6"
            v-slot="{ errors, processing, recentlySuccessful }"
        >
            <div class="grid gap-2">
                <Label for="current_password">Текущий пароль</Label>
                <Input
                    id="current_password"
                    name="current_password"
                    type="password"
                    class="mt-1 block w-full"
                    autocomplete="current-password"
                    placeholder="Текущий пароль"
                />
                <InputError :message="errors.current_password" />
            </div>

            <div class="grid gap-2">
                <Label for="password">Новый пароль</Label>
                <Input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-full"
                    autocomplete="new-password"
                    placeholder="Новый пароль"
                />
                <InputError :message="errors.password" />
            </div>

            <div class="grid gap-2">
                <Label for="password_confirmation">Подтверждение пароля</Label>
                <Input
                    id="password_confirmation"
                    name="password_confirmation"
                    type="password"
                    class="mt-1 block w-full"
                    autocomplete="new-password"
                    placeholder="Подтверждение пароля"
                />
                <InputError :message="errors.password_confirmation" />
            </div>

            <div class="flex items-center gap-4">
                <Button
                    :disabled="processing"
                    data-test="update-password-button"
                    >Сохранить пароль</Button
                >

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p
                        v-show="recentlySuccessful"
                        class="text-sm text-neutral-600"
                    >
                        Сохранено.
                    </p>
                </Transition>
            </div>
        </Form>
    </div>
</template>
