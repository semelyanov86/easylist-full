<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import Heading from '@shared/components/Heading.vue';
import InputError from '@shared/components/InputError.vue';
import { Button } from '@shared/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@shared/ui/dialog';
import { Input } from '@shared/ui/input';
import { Label } from '@shared/ui/label';
import { useTemplateRef } from 'vue';

import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';

const passwordInput = useTemplateRef('passwordInput');
</script>

<template>
    <div class="space-y-6">
        <Heading
            variant="small"
            title="Удаление аккаунта"
            description="Удаление аккаунта и всех связанных данных"
        />
        <div
            class="space-y-4 rounded-lg border border-red-100 bg-red-50 p-4 dark:border-red-200/10 dark:bg-red-700/10"
        >
            <div class="relative space-y-0.5 text-red-600 dark:text-red-100">
                <p class="font-medium">Внимание</p>
                <p class="text-sm">
                    Пожалуйста, будьте осторожны — это действие необратимо.
                </p>
            </div>
            <Dialog>
                <DialogTrigger as-child>
                    <Button variant="destructive" data-test="delete-user-button"
                        >Удалить аккаунт</Button
                    >
                </DialogTrigger>
                <DialogContent>
                    <Form
                        v-bind="ProfileController.destroy.form()"
                        reset-on-success
                        @error="() => passwordInput?.$el?.focus()"
                        :options="{
                            preserveScroll: true,
                        }"
                        class="space-y-6"
                        v-slot="{ errors, processing, reset, clearErrors }"
                    >
                        <DialogHeader class="space-y-3">
                            <DialogTitle
                                >Вы уверены, что хотите удалить
                                аккаунт?</DialogTitle
                            >
                            <DialogDescription>
                                После удаления аккаунта все его данные и ресурсы
                                будут безвозвратно удалены. Пожалуйста, введите
                                пароль для подтверждения удаления аккаунта.
                            </DialogDescription>
                        </DialogHeader>

                        <div class="grid gap-2">
                            <Label for="password" class="sr-only">Пароль</Label>
                            <Input
                                id="password"
                                type="password"
                                name="password"
                                ref="passwordInput"
                                placeholder="Пароль"
                            />
                            <InputError :message="errors.password" />
                        </div>

                        <DialogFooter class="gap-2">
                            <DialogClose as-child>
                                <Button
                                    variant="secondary"
                                    @click="
                                        () => {
                                            clearErrors();
                                            reset();
                                        }
                                    "
                                >
                                    Отмена
                                </Button>
                            </DialogClose>

                            <Button
                                type="submit"
                                variant="destructive"
                                :disabled="processing"
                                data-test="confirm-delete-user-button"
                            >
                                Удалить аккаунт
                            </Button>
                        </DialogFooter>
                    </Form>
                </DialogContent>
            </Dialog>
        </div>
    </div>
</template>
