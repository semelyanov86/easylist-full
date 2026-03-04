<script setup lang="ts">
import type { Contact } from '@entities/contact';
import { formatRelativeDate } from '@shared/lib/format';
import {
    Briefcase,
    Facebook,
    Linkedin,
    Mail,
    MapPin,
    MessageCircle,
    MoreHorizontal,
    Pencil,
    Phone,
    Trash2,
} from 'lucide-vue-next';
import { ref } from 'vue';

type Props = {
    contact: Contact;
};

defineProps<Props>();

const emit = defineEmits<{
    edit: [contact: Contact];
    delete: [contact: Contact];
}>();

const showMenu = ref(false);

const getInitials = (firstName: string, lastName: string): string => {
    return (firstName.charAt(0) + lastName.charAt(0)).toUpperCase();
};

const avatarColors: string[] = [
    'bg-status-blue/12 text-status-blue ring-status-blue/20 dark:bg-status-blue/18',
    'bg-status-purple/12 text-status-purple ring-status-purple/20 dark:bg-status-purple/18',
    'bg-status-cyan/12 text-status-cyan ring-status-cyan/20 dark:bg-status-cyan/18',
    'bg-status-amber/12 text-status-amber ring-status-amber/20 dark:bg-status-amber/18',
    'bg-status-green/12 text-status-green ring-status-green/20 dark:bg-status-green/18',
    'bg-status-pink/12 text-status-pink ring-status-pink/20 dark:bg-status-pink/18',
    'bg-status-indigo/12 text-status-indigo ring-status-indigo/20 dark:bg-status-indigo/18',
    'bg-status-teal/12 text-status-teal ring-status-teal/20 dark:bg-status-teal/18',
];

const getAvatarColor = (contactId: number): string => {
    return avatarColors[contactId % avatarColors.length] as string;
};

const hasSocials = (contact: Contact): boolean => {
    return !!(
        contact.linkedin_url ||
        contact.facebook_url ||
        contact.whatsapp_url
    );
};

const hasContactInfo = (contact: Contact): boolean => {
    return !!(contact.email || contact.phone);
};
</script>

<template>
    <div
        class="group relative flex flex-col rounded-xl border border-border/50 bg-background transition-all hover:border-border hover:shadow-md dark:bg-card dark:hover:border-border"
        @mouseleave="showMenu = false"
    >
        <!-- Верхняя часть: аватар + имя + меню -->
        <div class="flex items-start gap-3.5 p-4 pb-0">
            <div
                class="flex size-12 shrink-0 items-center justify-center rounded-full text-sm font-bold tracking-wide ring-2"
                :class="getAvatarColor(contact.id)"
            >
                {{ getInitials(contact.first_name, contact.last_name) }}
            </div>

            <div class="min-w-0 flex-1">
                <h4
                    class="truncate text-sm leading-tight font-semibold text-foreground"
                >
                    {{ contact.first_name }} {{ contact.last_name }}
                </h4>
                <div
                    v-if="contact.position || contact.city"
                    class="mt-1 flex flex-wrap items-center gap-x-2.5 gap-y-0.5"
                >
                    <span
                        v-if="contact.position"
                        class="flex items-center gap-1 text-xs text-muted-foreground"
                    >
                        <Briefcase class="size-3 shrink-0 opacity-60" />
                        <span class="truncate">{{ contact.position }}</span>
                    </span>
                    <span
                        v-if="contact.position && contact.city"
                        class="text-xs text-border"
                    >
                        ·
                    </span>
                    <span
                        v-if="contact.city"
                        class="flex items-center gap-1 text-xs text-muted-foreground"
                    >
                        <MapPin class="size-3 shrink-0 opacity-60" />
                        <span class="truncate">{{ contact.city }}</span>
                    </span>
                </div>
            </div>

            <!-- Меню действий -->
            <div class="relative shrink-0">
                <button
                    type="button"
                    class="inline-flex size-7 items-center justify-center rounded-md text-muted-foreground/50 transition-colors hover:bg-muted hover:text-foreground"
                    :class="
                        showMenu
                            ? 'bg-muted text-foreground'
                            : 'opacity-0 group-hover:opacity-100'
                    "
                    @click="showMenu = !showMenu"
                >
                    <MoreHorizontal class="size-4" />
                </button>
                <Transition
                    enter-active-class="transition duration-100 ease-out"
                    enter-from-class="scale-95 opacity-0"
                    enter-to-class="scale-100 opacity-100"
                    leave-active-class="transition duration-75 ease-in"
                    leave-from-class="scale-100 opacity-100"
                    leave-to-class="scale-95 opacity-0"
                >
                    <div
                        v-if="showMenu"
                        class="absolute top-8 right-0 z-10 w-36 overflow-hidden rounded-lg border border-border bg-card py-1 shadow-lg"
                    >
                        <button
                            type="button"
                            class="flex w-full items-center gap-2 px-3 py-1.5 text-xs text-foreground transition-colors hover:bg-muted"
                            @click="
                                showMenu = false;
                                emit('edit', contact);
                            "
                        >
                            <Pencil class="size-3.5 text-muted-foreground" />
                            Редактировать
                        </button>
                        <button
                            type="button"
                            class="flex w-full items-center gap-2 px-3 py-1.5 text-xs text-destructive transition-colors hover:bg-destructive/8"
                            @click="
                                showMenu = false;
                                emit('delete', contact);
                            "
                        >
                            <Trash2 class="size-3.5" />
                            Удалить
                        </button>
                    </div>
                </Transition>
            </div>
        </div>

        <!-- Описание -->
        <p
            v-if="contact.description"
            class="mx-4 mt-3 line-clamp-2 text-xs leading-relaxed text-muted-foreground"
        >
            {{ contact.description }}
        </p>

        <!-- Разделитель + контактная информация -->
        <div
            v-if="hasContactInfo(contact) || hasSocials(contact)"
            class="mx-4 mt-3 border-t border-border/50 pt-3"
        >
            <!-- Email / Телефон -->
            <div v-if="hasContactInfo(contact)" class="flex flex-col gap-1.5">
                <a
                    v-if="contact.email"
                    :href="'mailto:' + contact.email"
                    class="group/link -mx-2 flex items-center gap-2 rounded-md px-2 py-1 text-xs transition-colors hover:bg-muted"
                >
                    <div
                        class="flex size-6 shrink-0 items-center justify-center rounded-md bg-status-blue/10 dark:bg-status-blue/15"
                    >
                        <Mail class="size-3 text-status-blue" />
                    </div>
                    <span
                        class="truncate text-muted-foreground transition-colors group-hover/link:text-foreground"
                    >
                        {{ contact.email }}
                    </span>
                </a>
                <a
                    v-if="contact.phone"
                    :href="'tel:' + contact.phone"
                    class="group/link -mx-2 flex items-center gap-2 rounded-md px-2 py-1 text-xs transition-colors hover:bg-muted"
                >
                    <div
                        class="flex size-6 shrink-0 items-center justify-center rounded-md bg-status-green/10 dark:bg-status-green/15"
                    >
                        <Phone class="size-3 text-status-green" />
                    </div>
                    <span
                        class="truncate text-muted-foreground transition-colors group-hover/link:text-foreground"
                    >
                        {{ contact.phone }}
                    </span>
                </a>
            </div>

            <!-- Социальные ссылки -->
            <div
                v-if="hasSocials(contact)"
                class="flex items-center gap-1"
                :class="{ 'mt-2': hasContactInfo(contact) }"
            >
                <a
                    v-if="contact.linkedin_url"
                    :href="contact.linkedin_url"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex size-7 items-center justify-center rounded-md text-muted-foreground/60 transition-colors hover:bg-status-blue/10 hover:text-status-blue dark:hover:bg-status-blue/15"
                    title="LinkedIn"
                >
                    <Linkedin class="size-3.5" />
                </a>
                <a
                    v-if="contact.facebook_url"
                    :href="contact.facebook_url"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex size-7 items-center justify-center rounded-md text-muted-foreground/60 transition-colors hover:bg-status-indigo/10 hover:text-status-indigo dark:hover:bg-status-indigo/15"
                    title="Facebook"
                >
                    <Facebook class="size-3.5" />
                </a>
                <a
                    v-if="contact.whatsapp_url"
                    :href="contact.whatsapp_url"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex size-7 items-center justify-center rounded-md text-muted-foreground/60 transition-colors hover:bg-status-green/10 hover:text-status-green dark:hover:bg-status-green/15"
                    title="WhatsApp"
                >
                    <MessageCircle class="size-3.5" />
                </a>
            </div>
        </div>

        <!-- Время добавления -->
        <div class="mt-auto px-4 pt-3 pb-3">
            <span class="text-xs text-muted-foreground/40">
                {{ formatRelativeDate(contact.created_at) }}
            </span>
        </div>
    </div>
</template>
