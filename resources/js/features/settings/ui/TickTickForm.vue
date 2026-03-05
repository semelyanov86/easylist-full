<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import Heading from '@shared/components/Heading.vue';
import InputError from '@shared/components/InputError.vue';
import { Button } from '@shared/ui/button';
import { Input } from '@shared/ui/input';
import { Label } from '@shared/ui/label';

import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';

type Props = {
    ticktickToken: string | null;
    ticktickListId: string | null;
};

defineProps<Props>();
</script>

<template>
    <div class="flex flex-col space-y-6">
        <Heading
            variant="small"
            title="Интеграция с TickTick"
            description="Настройте синхронизацию задач из вакансий с TickTick"
        />

        <Form
            :action="ProfileController.update.url()"
            method="patch"
            class="space-y-6"
            v-slot="{ errors, processing, recentlySuccessful }"
        >
            <p class="text-sm text-muted-foreground">
                Если заполнены оба поля, задачи из ваших вакансий будут
                автоматически отправляться в указанный список TickTick.
            </p>

            <div class="grid gap-2">
                <Label for="ticktick_token">Токен TickTick</Label>
                <Input
                    id="ticktick_token"
                    type="password"
                    class="mt-1 block w-full"
                    name="ticktick_token"
                    :default-value="ticktickToken ?? ''"
                    autocomplete="off"
                    placeholder="Ваш API-токен TickTick"
                />
                <InputError class="mt-2" :message="errors.ticktick_token" />
            </div>

            <div class="grid gap-2">
                <Label for="ticktick_list_id">Идентификатор списка</Label>
                <Input
                    id="ticktick_list_id"
                    class="mt-1 block w-full"
                    name="ticktick_list_id"
                    :default-value="ticktickListId ?? ''"
                    autocomplete="off"
                    placeholder="ID списка в TickTick"
                />
                <InputError class="mt-2" :message="errors.ticktick_list_id" />
            </div>

            <div class="flex items-center gap-4">
                <Button :disabled="processing">Сохранить</Button>

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
