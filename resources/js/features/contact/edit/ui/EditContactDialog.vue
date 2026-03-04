<script setup lang="ts">
import type { Contact } from '@entities/contact';
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
import { Save } from 'lucide-vue-next';

import ContactController from '@/actions/App/Http/Controllers/ContactController';

type Props = {
    open: boolean;
    contact: Contact;
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
                :action="ContactController.update.url(contact.id)"
                method="patch"
                :options="{ preserveScroll: true }"
                @success="emit('close')"
                class="space-y-5"
                v-slot="{ errors, processing }"
            >
                <DialogHeader>
                    <DialogTitle>Редактировать контакт</DialogTitle>
                    <DialogDescription>
                        Измените данные контактного лица.
                    </DialogDescription>
                </DialogHeader>

                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="grid gap-2">
                            <Label for="edit-first-name">Имя</Label>
                            <Input
                                id="edit-first-name"
                                name="first_name"
                                required
                                :default-value="contact.first_name"
                            />
                            <InputError :message="errors.first_name" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="edit-last-name">Фамилия</Label>
                            <Input
                                id="edit-last-name"
                                name="last_name"
                                required
                                :default-value="contact.last_name"
                            />
                            <InputError :message="errors.last_name" />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="grid gap-2">
                            <Label for="edit-position">
                                Должность
                                <span class="text-muted-foreground">
                                    (необязательно)
                                </span>
                            </Label>
                            <Input
                                id="edit-position"
                                name="position"
                                :default-value="contact.position ?? ''"
                            />
                            <InputError :message="errors.position" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="edit-city">
                                Город
                                <span class="text-muted-foreground">
                                    (необязательно)
                                </span>
                            </Label>
                            <Input
                                id="edit-city"
                                name="city"
                                :default-value="contact.city ?? ''"
                            />
                            <InputError :message="errors.city" />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="grid gap-2">
                            <Label for="edit-email">
                                Email
                                <span class="text-muted-foreground">
                                    (необязательно)
                                </span>
                            </Label>
                            <Input
                                id="edit-email"
                                name="email"
                                type="email"
                                :default-value="contact.email ?? ''"
                            />
                            <InputError :message="errors.email" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="edit-phone">
                                Телефон
                                <span class="text-muted-foreground">
                                    (необязательно)
                                </span>
                            </Label>
                            <Input
                                id="edit-phone"
                                name="phone"
                                :default-value="contact.phone ?? ''"
                            />
                            <InputError :message="errors.phone" />
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label for="edit-description">
                            Описание
                            <span class="text-muted-foreground">
                                (необязательно)
                            </span>
                        </Label>
                        <textarea
                            id="edit-description"
                            name="description"
                            rows="2"
                            class="w-full min-w-0 resize-none rounded-md border border-input bg-transparent px-3 py-2 text-base shadow-xs transition-[color,box-shadow] outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 md:text-sm dark:bg-input/30"
                            :value="contact.description ?? ''"
                        />
                        <InputError :message="errors.description" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="edit-linkedin">
                            LinkedIn
                            <span class="text-muted-foreground">
                                (необязательно)
                            </span>
                        </Label>
                        <Input
                            id="edit-linkedin"
                            name="linkedin_url"
                            type="url"
                            :default-value="contact.linkedin_url ?? ''"
                        />
                        <InputError :message="errors.linkedin_url" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="grid gap-2">
                            <Label for="edit-facebook">
                                Facebook
                                <span class="text-muted-foreground">
                                    (необязательно)
                                </span>
                            </Label>
                            <Input
                                id="edit-facebook"
                                name="facebook_url"
                                type="url"
                                :default-value="contact.facebook_url ?? ''"
                            />
                            <InputError :message="errors.facebook_url" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="edit-whatsapp">
                                WhatsApp
                                <span class="text-muted-foreground">
                                    (необязательно)
                                </span>
                            </Label>
                            <Input
                                id="edit-whatsapp"
                                name="whatsapp_url"
                                type="url"
                                :default-value="contact.whatsapp_url ?? ''"
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
                        <Save class="size-3.5" />
                        <span>Сохранить</span>
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
