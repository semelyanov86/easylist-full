<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import InputError from '@shared/components/InputError.vue';
import { Button } from '@shared/ui/button';
import { Input } from '@shared/ui/input';
import { Label } from '@shared/ui/label';

import JobStatusController from '@/actions/App/Http/Controllers/Settings/JobStatusController';
</script>

<template>
    <Form
        v-bind="JobStatusController.store.form()"
        reset-on-success
        class="space-y-4"
        v-slot="{ errors, processing, recentlySuccessful }"
    >
        <div class="grid gap-2">
            <Label for="status-title">Название</Label>
            <Input
                id="status-title"
                name="title"
                placeholder="Например: Собеседование с HR"
                required
            />
            <InputError :message="errors.title" />
        </div>
        <div class="grid gap-2">
            <Label for="status-description">Описание</Label>
            <textarea
                id="status-description"
                name="description"
                placeholder="Необязательное описание статуса"
                rows="2"
                class="w-full min-w-0 rounded-md border border-input bg-transparent px-3 py-2 text-base shadow-xs transition-[color,box-shadow] outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 md:text-sm dark:bg-input/30"
            />
            <InputError :message="errors.description" />
        </div>
        <div class="flex items-center gap-4">
            <Button :disabled="processing">Создать статус</Button>
            <Transition
                enter-active-class="transition ease-in-out"
                enter-from-class="opacity-0"
                leave-active-class="transition ease-in-out"
                leave-to-class="opacity-0"
            >
                <p
                    v-show="recentlySuccessful"
                    class="text-sm text-muted-foreground"
                >
                    Статус создан.
                </p>
            </Transition>
        </div>
    </Form>
</template>
