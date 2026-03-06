<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import Heading from '@shared/components/Heading.vue';
import InputError from '@shared/components/InputError.vue';
import { Button } from '@shared/ui/button';
import { Label } from '@shared/ui/label';

import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';

type Props = {
    aboutMe: string | null;
};

defineProps<Props>();
</script>

<template>
    <div class="flex flex-col space-y-6">
        <Heading
            variant="small"
            title="О себе"
            description="Расскажите о себе, своём опыте и навыках"
        />

        <Form
            :action="ProfileController.update.url()"
            method="patch"
            class="space-y-6"
            v-slot="{ errors, processing, recentlySuccessful }"
        >
            <p class="text-sm text-muted-foreground">
                Эта информация будет использоваться для генерации
                сопроводительного письма к вакансиям.
            </p>

            <div class="grid gap-2">
                <Label for="about_me">О себе</Label>
                <textarea
                    id="about_me"
                    name="about_me"
                    rows="5"
                    class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                    :defaultValue="aboutMe ?? ''"
                    placeholder="Расскажите о своём опыте, навыках и карьерных целях..."
                ></textarea>
                <InputError class="mt-2" :message="errors.about_me" />
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
