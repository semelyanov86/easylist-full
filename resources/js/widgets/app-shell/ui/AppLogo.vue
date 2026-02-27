<script setup lang="ts">
import { useSidebar } from '@shared/ui/sidebar';
import { Briefcase } from 'lucide-vue-next';

const { open } = useSidebar();
</script>

<template>
    <template v-if="open">
        <img
            src="/images/easylist-logo.svg"
            alt="Easylist"
            class="h-12 w-auto"
        />
    </template>
    <template v-else>
        <div
            class="flex aspect-square size-8 items-center justify-center rounded-md bg-sidebar-primary text-sidebar-primary-foreground"
        >
            <Briefcase class="size-5" />
        </div>
    </template>
</template>
