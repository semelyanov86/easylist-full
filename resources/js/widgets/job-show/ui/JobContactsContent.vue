<script setup lang="ts">
import type { Contact } from '@entities/contact';
import type { JobDetail } from '@entities/job';
import { AddContactDialog } from '@features/contact/add';
import { EditContactDialog } from '@features/contact/edit';
import { router } from '@inertiajs/vue3';
import { Button } from '@shared/ui/button';
import { Plus, Search, UserPlus, Users } from 'lucide-vue-next';
import { computed, ref } from 'vue';

import ContactController from '@/actions/App/Http/Controllers/ContactController';

import ContactCard from './ContactCard.vue';

type Props = {
    job: JobDetail;
};

const props = defineProps<Props>();

const showAddDialog = ref(false);
const editingContact = ref<Contact | null>(null);
const searchQuery = ref('');

const filteredContacts = computed(() => {
    if (!searchQuery.value.trim()) {
        return props.job.contacts;
    }
    const q = searchQuery.value.toLowerCase();

    return props.job.contacts.filter(
        (c) =>
            c.first_name.toLowerCase().includes(q) ||
            c.last_name.toLowerCase().includes(q) ||
            (c.position?.toLowerCase().includes(q) ?? false) ||
            (c.city?.toLowerCase().includes(q) ?? false) ||
            (c.email?.toLowerCase().includes(q) ?? false),
    );
});

const deleteContact = (contact: Contact): void => {
    router.delete(ContactController.destroy.url(contact.id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-4 lg:col-span-2">
            <!-- Заголовок и действия -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div
                        class="flex size-9 items-center justify-center rounded-lg bg-status-cyan/10 dark:bg-status-cyan/15"
                    >
                        <Users class="size-4 text-status-cyan" />
                    </div>
                    <div>
                        <h3
                            class="text-sm leading-tight font-semibold text-foreground"
                        >
                            Контакты
                        </h3>
                        <p
                            v-if="job.contacts.length > 0"
                            class="text-xs text-muted-foreground"
                        >
                            {{
                                job.contacts.length === 1
                                    ? '1 контакт'
                                    : job.contacts.length < 5
                                      ? `${job.contacts.length} контакта`
                                      : `${job.contacts.length} контактов`
                            }}
                        </p>
                    </div>
                </div>
                <Button size="sm" @click="showAddDialog = true">
                    <Plus class="size-3.5" />
                    <span>Добавить</span>
                </Button>
            </div>

            <!-- Поиск (при 3+ контактах) -->
            <div v-if="job.contacts.length >= 3" class="relative">
                <div
                    class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"
                >
                    <Search class="size-4 text-muted-foreground/50" />
                </div>
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Поиск по имени, должности, городу..."
                    class="h-9 w-full rounded-lg border border-border/60 bg-background pr-3 pl-9 text-sm shadow-xs transition-colors outline-none placeholder:text-muted-foreground/50 focus:border-ring focus:ring-2 focus:ring-ring/20 dark:bg-card"
                />
            </div>

            <!-- Сетка карточек -->
            <div
                v-if="filteredContacts.length > 0"
                class="grid grid-cols-1 gap-3 md:grid-cols-2"
            >
                <ContactCard
                    v-for="contact in filteredContacts"
                    :key="contact.id"
                    :contact="contact"
                    @edit="editingContact = $event"
                    @delete="deleteContact"
                />
            </div>

            <!-- Пустой результат поиска -->
            <div
                v-else-if="searchQuery && job.contacts.length > 0"
                class="flex flex-col items-center justify-center gap-2 rounded-xl border border-dashed border-border/60 px-5 py-12"
            >
                <Search class="size-8 text-muted-foreground/25" />
                <p class="text-sm text-muted-foreground/60">
                    Ничего не найдено
                </p>
                <button
                    type="button"
                    class="text-xs text-primary underline underline-offset-2 transition-colors hover:text-primary/80"
                    @click="searchQuery = ''"
                >
                    Сбросить поиск
                </button>
            </div>

            <!-- Пустое состояние -->
            <div
                v-else
                class="flex flex-col items-center justify-center gap-4 rounded-xl border border-dashed border-border/60 px-6 py-16"
            >
                <div
                    class="flex size-14 items-center justify-center rounded-full bg-muted/60"
                >
                    <Users class="size-7 text-muted-foreground/30" />
                </div>
                <div class="text-center">
                    <p class="text-sm font-medium text-muted-foreground/70">
                        Контактов пока нет
                    </p>
                    <p class="mt-1 max-w-xs text-xs text-muted-foreground/45">
                        Добавьте контактные лица — рекрутеров, HR-менеджеров или
                        руководителей, связанных с этой вакансией
                    </p>
                </div>
                <Button
                    size="sm"
                    variant="outline"
                    @click="showAddDialog = true"
                >
                    <UserPlus class="size-3.5" />
                    <span>Создать первый контакт</span>
                </Button>
            </div>
        </div>
    </div>

    <AddContactDialog
        :open="showAddDialog"
        :job-id="job.id"
        @close="showAddDialog = false"
    />
    <EditContactDialog
        v-if="editingContact"
        :open="editingContact !== null"
        :contact="editingContact"
        @close="editingContact = null"
    />
</template>
