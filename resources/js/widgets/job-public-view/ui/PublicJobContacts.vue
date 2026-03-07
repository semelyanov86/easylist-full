<script setup lang="ts">
import type { PublicContact } from '@entities/job';
import { Linkedin, Mail, Phone, User } from 'lucide-vue-next';

type Props = {
    contacts: PublicContact[];
};

defineProps<Props>();
</script>

<template>
    <div
        class="overflow-hidden rounded-xl border border-border bg-card shadow-sm"
    >
        <div class="flex items-center gap-2 border-b border-border px-6 py-3">
            <h2 class="text-sm font-semibold text-foreground">
                Контактные лица
            </h2>
            <span class="text-xs text-muted-foreground">
                {{ contacts.length }}
            </span>
        </div>
        <div class="divide-y divide-border">
            <div
                v-for="(contact, index) in contacts"
                :key="index"
                class="flex flex-col gap-3 p-6 sm:flex-row sm:items-start sm:gap-6"
            >
                <div class="flex items-center gap-3">
                    <div
                        class="flex size-10 shrink-0 items-center justify-center rounded-full bg-muted"
                    >
                        <User class="size-5 text-muted-foreground" />
                    </div>
                    <div class="sm:min-w-40">
                        <p class="font-medium text-foreground">
                            {{ contact.first_name }}
                            {{ contact.last_name }}
                        </p>
                        <p
                            v-if="contact.position"
                            class="text-sm text-muted-foreground"
                        >
                            {{ contact.position }}
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3 text-sm">
                    <a
                        v-if="contact.email"
                        :href="'mailto:' + contact.email"
                        class="inline-flex items-center gap-1.5 rounded-md border border-border px-3 py-1.5 text-xs text-muted-foreground transition hover:bg-muted hover:text-foreground"
                    >
                        <Mail class="size-3.5 shrink-0" />
                        {{ contact.email }}
                    </a>
                    <a
                        v-if="contact.phone"
                        :href="'tel:' + contact.phone"
                        class="inline-flex items-center gap-1.5 rounded-md border border-border px-3 py-1.5 text-xs text-muted-foreground transition hover:bg-muted hover:text-foreground"
                    >
                        <Phone class="size-3.5 shrink-0" />
                        {{ contact.phone }}
                    </a>
                    <a
                        v-if="contact.linkedin_url"
                        :href="contact.linkedin_url"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center gap-1.5 rounded-md border border-border px-3 py-1.5 text-xs text-muted-foreground transition hover:bg-muted hover:text-foreground"
                    >
                        <Linkedin class="size-3.5 shrink-0" />
                        LinkedIn
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>
