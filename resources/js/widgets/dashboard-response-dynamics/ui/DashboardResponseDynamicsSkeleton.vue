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
                <Skeleton class="h-3.5 w-28 rounded" />
            </div>
        </div>

        <div class="p-3">
            <Skeleton class="h-16 w-full rounded" />
            <div class="mt-1 flex items-center justify-between">
                <Skeleton class="h-3 w-8 rounded" />
                <Skeleton class="h-3 w-8 rounded" />
            </div>
        </div>
    </div>
</template>
