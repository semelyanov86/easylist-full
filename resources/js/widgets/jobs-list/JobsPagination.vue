<script setup lang="ts">
import type { PaginationLink } from '@entities/job';
import { Link } from '@inertiajs/vue3';

type Props = {
    links: PaginationLink[];
    lastPage: number;
};

const props = defineProps<Props>();

const visibleLinks = (): PaginationLink[] => {
    return props.links.slice(1, -1);
};
</script>

<template>
    <nav v-if="lastPage > 1" class="flex items-center justify-center gap-1">
        <Link
            v-if="links[0]?.url"
            :href="links[0].url"
            preserve-state
            preserve-scroll
            class="inline-flex h-8 items-center justify-center rounded-md px-3 text-sm text-muted-foreground transition-colors hover:bg-accent hover:text-accent-foreground"
        >
            &laquo;
        </Link>
        <span
            v-else
            class="inline-flex h-8 items-center justify-center rounded-md px-3 text-sm text-muted-foreground opacity-50"
        >
            &laquo;
        </span>

        <template v-for="link in visibleLinks()" :key="link.label">
            <Link
                v-if="link.url && !link.active"
                :href="link.url"
                preserve-state
                preserve-scroll
                class="inline-flex h-8 min-w-8 items-center justify-center rounded-md px-2 text-sm text-muted-foreground transition-colors hover:bg-accent hover:text-accent-foreground"
            >
                {{ link.label }}
            </Link>
            <span
                v-else
                class="inline-flex h-8 min-w-8 items-center justify-center rounded-md px-2 text-sm font-medium"
                :class="
                    link.active
                        ? 'bg-primary text-primary-foreground'
                        : 'text-muted-foreground opacity-50'
                "
            >
                {{ link.label }}
            </span>
        </template>

        <Link
            v-if="links[links.length - 1]?.url"
            :href="links[links.length - 1]!.url!"
            preserve-state
            preserve-scroll
            class="inline-flex h-8 items-center justify-center rounded-md px-3 text-sm text-muted-foreground transition-colors hover:bg-accent hover:text-accent-foreground"
        >
            &raquo;
        </Link>
        <span
            v-else
            class="inline-flex h-8 items-center justify-center rounded-md px-3 text-sm text-muted-foreground opacity-50"
        >
            &raquo;
        </span>
    </nav>
</template>
