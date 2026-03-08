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

        <div class="p-2">
            <div class="space-y-px">
                <div v-for="i in 4" :key="i" class="flex gap-2 px-1.5 py-1.5">
                    <Skeleton class="mt-px size-5 shrink-0 rounded-full" />

                    <div class="min-w-0 flex-1">
                        <div class="flex items-start justify-between gap-2">
                            <Skeleton class="h-3 w-3/4 rounded" />
                            <Skeleton class="h-2.5 w-14 shrink-0 rounded" />
                        </div>
                        <Skeleton class="mt-1.5 h-2.5 w-20 rounded" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
