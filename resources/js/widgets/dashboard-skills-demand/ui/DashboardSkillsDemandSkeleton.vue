<script setup lang="ts">
import { Skeleton } from '@shared/ui/skeleton';
</script>

<template>
    <div
        class="overflow-hidden rounded-lg border border-border bg-card shadow-sm"
    >
        <div
            class="flex items-center justify-between border-b border-border px-3 py-2"
        >
            <div class="flex items-center gap-1.5">
                <Skeleton class="size-3.5 rounded" />
                <Skeleton class="h-3.5 w-24 rounded" />
            </div>
        </div>

        <div class="p-2">
            <div class="space-y-px">
                <div
                    v-for="i in 5"
                    :key="i"
                    class="flex items-center gap-2 px-1.5 py-1.5"
                >
                    <Skeleton class="size-5 shrink-0 rounded-full" />
                    <Skeleton class="h-3 flex-1 rounded" />
                    <Skeleton class="h-4 w-10 shrink-0 rounded-full" />
                </div>
            </div>
        </div>
    </div>
</template>
