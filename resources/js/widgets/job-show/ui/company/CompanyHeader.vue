<script setup lang="ts">
import type { CompanyInfoDetails } from '@entities/company-info';
import { ArrowUpRight, Building2, Calendar, MapPin } from 'lucide-vue-next';

type LinkItem = {
    label: string;
    url: string;
};

type Props = {
    companyName: string;
    locationCity: string | null;
    info: CompanyInfoDetails | null;
    linkItems: LinkItem[];
};

defineProps<Props>();
</script>

<template>
    <div
        class="overflow-hidden rounded-xl border border-border bg-card shadow-sm"
    >
        <div class="flex items-center gap-4 p-5 sm:p-6">
            <div
                class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-muted"
            >
                <Building2 class="size-6 text-muted-foreground" />
            </div>
            <div class="min-w-0 flex-1">
                <h2
                    class="truncate text-xl font-bold tracking-tight text-foreground"
                >
                    {{ companyName }}
                </h2>
                <div
                    class="mt-0.5 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-muted-foreground"
                >
                    <span
                        v-if="locationCity"
                        class="inline-flex items-center gap-1"
                    >
                        <MapPin class="size-3.5 opacity-60" />
                        {{ locationCity }}
                    </span>
                    <span
                        v-if="info?.industry"
                        class="inline-flex items-center gap-1"
                    >
                        <Building2 class="size-3.5 opacity-60" />
                        {{ info.industry }}
                    </span>
                    <span
                        v-if="info?.founded"
                        class="inline-flex items-center gap-1"
                    >
                        <Calendar class="size-3.5 opacity-60" />
                        с {{ info.founded }}
                    </span>
                </div>
            </div>
            <div
                v-if="linkItems.length > 0"
                class="hidden shrink-0 items-center gap-1.5 sm:flex"
            >
                <a
                    v-for="link in linkItems"
                    :key="link.url"
                    :href="link.url"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-border px-3 py-1.5 text-xs font-medium text-muted-foreground transition-colors hover:bg-accent hover:text-foreground"
                >
                    {{ link.label }}
                    <ArrowUpRight class="size-3 opacity-50" />
                </a>
            </div>
        </div>
    </div>
</template>
