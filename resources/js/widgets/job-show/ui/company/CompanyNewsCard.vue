<script setup lang="ts">
import type { CompanyNewsItem } from '@entities/company-info';
import { ExternalLink, Newspaper } from 'lucide-vue-next';

type Props = {
    news: CompanyNewsItem[];
};

defineProps<Props>();
</script>

<template>
    <div
        class="overflow-hidden rounded-xl border border-border bg-card shadow-sm"
    >
        <div class="flex items-center gap-2 border-b border-border px-5 py-3">
            <Newspaper class="size-4 text-muted-foreground" />
            <h3 class="text-sm font-semibold text-foreground">
                Последние новости
            </h3>
        </div>
        <div class="divide-y divide-border">
            <a
                v-for="(item, index) in news"
                :key="item.title ?? item.url ?? index"
                :href="item.url ?? undefined"
                :target="item.url ? '_blank' : undefined"
                rel="noopener noreferrer"
                class="group flex items-start gap-3 px-5 py-3 transition-colors"
                :class="item.url ? 'cursor-pointer hover:bg-accent/50' : ''"
            >
                <div class="min-w-0 flex-1">
                    <p
                        class="text-sm font-medium text-foreground"
                        :class="item.url ? 'group-hover:text-primary' : ''"
                    >
                        {{ item.title ?? item.url }}
                    </p>
                    <p
                        v-if="item.date"
                        class="mt-0.5 text-xs text-muted-foreground"
                    >
                        {{ item.date }}
                    </p>
                </div>
                <ExternalLink
                    v-if="item.url"
                    class="mt-0.5 size-3.5 shrink-0 text-muted-foreground/40 transition-colors group-hover:text-primary"
                />
            </a>
        </div>
    </div>
</template>
