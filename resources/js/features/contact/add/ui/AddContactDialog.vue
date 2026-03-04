<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
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
} from '@shared/ui/dialog';
import { Input } from '@shared/ui/input';
import { Label } from '@shared/ui/label';
import { UserPlus } from 'lucide-vue-next';

import ContactController from '@/actions/App/Http/Controllers/ContactController';

type Props = {
    open: boolean;
    jobId: number;
};

defineProps<Props>();

const emit = defineEmits<{
    close: [];
}>();
</script>

<template>
    <Dialog
        :open="open"
        @update:open="
            (val: boolean) => {
                if (!val) emit('close');
            }
        "
    >
        <DialogContent class="max-h-[90dvh] overflow-y-auto sm:max-w-lg">
            <Form
                :action="ContactController.store.url(jobId)"
                method="post"
                reset-on-success
                :options="{ preserveScroll: true }"
                @success="emit('close')"
                class="space-y-5"
                v-slot="{ errors, processing }"
            >
                <DialogHeader>
                    <DialogTitle>Создать контакт</DialogTitle>
                    <DialogDescription>
                        Добавьте контактное лицо к вакансии.
                    </DialogDescription>
                </DialogHeader>

                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="grid gap-2">
                            <Label for="add-first-name">Имя</Label>
                            <Input
                                id="add-first-name"
                                name="first_name"
                                required
                                placeholder="Иван"
                            />
                            <InputError :message="errors.first_name" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="add-last-name">Фамилия</Label>
                            <Input
                                id="add-last-name"
                                name="last_name"
                                required
                                placeholder="Иванов"
                            />
                            <InputError :message="errors.last_name" />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="grid gap-2">
                            <Label for="add-position">
                                Должность
                                <span class="text-muted-foreground">
                                    (необязательно)
                                </span>
                            </Label>
                            <Input
                                id="add-position"
                                name="position"
                                placeholder="HR-менеджер"
                            />
                            <InputError :message="errors.position" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="add-city">
                                Город
                                <span class="text-muted-foreground">
                                    (необязательно)
                                </span>
                            </Label>
                            <Input
                                id="add-city"
                                name="city"
                                placeholder="Москва"
                            />
                            <InputError :message="errors.city" />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="grid gap-2">
                            <Label for="add-email">
                                Email
                                <span class="text-muted-foreground">
                                    (необязательно)
                                </span>
                            </Label>
                            <Input
                                id="add-email"
                                name="email"
                                type="email"
                                placeholder="ivan@example.com"
                            />
                            <InputError :message="errors.email" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="add-phone">
                                Телефон
                                <span class="text-muted-foreground">
                                    (необязательно)
                                </span>
                            </Label>
                            <Input
                                id="add-phone"
                                name="phone"
                                placeholder="+7 999 123-45-67"
                            />
                            <InputError :message="errors.phone" />
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label for="add-description">
                            Описание
                            <span class="text-muted-foreground">
                                (необязательно)
                            </span>
                        </Label>
                        <textarea
                            id="add-description"
                            name="description"
                            rows="2"
                            placeholder="Заметки о контакте..."
                            class="w-full min-w-0 resize-none rounded-md border border-input bg-transparent px-3 py-2 text-base shadow-xs transition-[color,box-shadow] outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 md:text-sm dark:bg-input/30"
                        />
                        <InputError :message="errors.description" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="add-linkedin">
                            LinkedIn
                            <span class="text-muted-foreground">
                                (необязательно)
                            </span>
                        </Label>
                        <Input
                            id="add-linkedin"
                            name="linkedin_url"
                            type="url"
                            placeholder="https://linkedin.com/in/..."
                        />
                        <InputError :message="errors.linkedin_url" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="grid gap-2">
                            <Label for="add-facebook">
                                Facebook
                                <span class="text-muted-foreground">
                                    (необязательно)
                                </span>
                            </Label>
                            <Input
                                id="add-facebook"
                                name="facebook_url"
                                type="url"
                                placeholder="https://facebook.com/..."
                            />
                            <InputError :message="errors.facebook_url" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="add-whatsapp">
                                WhatsApp
                                <span class="text-muted-foreground">
                                    (необязательно)
                                </span>
                            </Label>
                            <Input
                                id="add-whatsapp"
                                name="whatsapp_url"
                                type="url"
                                placeholder="https://wa.me/..."
                            />
                            <InputError :message="errors.whatsapp_url" />
                        </div>
                    </div>
                </div>

                <DialogFooter class="gap-2">
                    <DialogClose as-child>
                        <Button variant="secondary" @click="emit('close')">
                            Отмена
                        </Button>
                    </DialogClose>
                    <Button type="submit" :disabled="processing">
                        <UserPlus class="size-3.5" />
                        <span>Создать</span>
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
