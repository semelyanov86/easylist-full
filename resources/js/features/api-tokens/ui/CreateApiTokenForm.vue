<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import InputError from '@shared/components/InputError.vue';
import { Button } from '@shared/ui/button';
import { Input } from '@shared/ui/input';
import { Label } from '@shared/ui/label';

import ApiTokenController from '@/actions/App/Http/Controllers/Settings/ApiTokenController';
</script>

<template>
    <Form
        v-bind="ApiTokenController.store.form()"
        reset-on-success
        class="space-y-4"
        v-slot="{ errors, processing, recentlySuccessful }"
    >
        <div class="grid gap-2">
            <Label for="token-name">Название токена</Label>
            <Input
                id="token-name"
                name="name"
                placeholder="Например: CI/CD, мобильное приложение"
                required
            />
            <InputError :message="errors.name" />
        </div>

        <div class="flex items-center gap-4">
            <Button :disabled="processing" data-test="create-token-button">
                Создать токен
            </Button>

            <Transition
                enter-active-class="transition ease-in-out"
                enter-from-class="opacity-0"
                leave-active-class="transition ease-in-out"
                leave-to-class="opacity-0"
            >
                <p v-show="recentlySuccessful" class="text-sm text-neutral-600">
                    Токен создан.
                </p>
            </Transition>
        </div>
    </Form>
</template>
