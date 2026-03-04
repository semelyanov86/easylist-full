<script setup lang="ts">
import { ArrowUpRight, Globe } from 'lucide-vue-next';

type LinkItem = {
    label: string;
    url: string;
};

type Props = {
    linkItems: LinkItem[];
};

defineProps<Props>();
</script>

<template>
    <div
        class="overflow-hidden rounded-xl border border-border bg-card shadow-sm sm:hidden"
    >
        <div class="flex items-center gap-2 border-b border-border px-5 py-3">
            <Globe class="size-4 text-muted-foreground" />
            <h3 class="text-sm font-semibold text-foreground">Ссылки</h3>
        </div>
        <div class="flex flex-wrap gap-2 p-4">
            <a
                v-for="link in linkItems"
                :key="link.url"
                :href="link.url"
                target="_blank"
                rel="noopener noreferrer"
                class="inline-flex items-center gap-1.5 rounded-lg border border-border px-3 py-2 text-sm font-medium text-muted-foreground transition-colors hover:bg-accent hover:text-foreground"
            >
                {{ link.label }}
                <ArrowUpRight class="size-3 opacity-50" />
            </a>
        </div>
    </div>
</template>
