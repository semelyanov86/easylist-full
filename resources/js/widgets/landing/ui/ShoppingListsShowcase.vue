<script setup lang="ts">
import {
    CheckCircle,
    FolderOpen,
    ListChecks,
    ShoppingCart,
} from 'lucide-vue-next';
</script>

<template>
    <div class="rounded-2xl border border-border bg-card p-6 shadow-lg lg:p-8">
        <div class="mb-4 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <FolderOpen class="size-5 text-chart-4" />
                <span class="font-semibold">Мои списки</span>
            </div>
        </div>
        <div class="space-y-2">
            <div
                class="flex items-center justify-between rounded-xl bg-muted p-4 dark:bg-accent"
            >
                <div class="flex items-center gap-3">
                    <ShoppingCart class="size-4 text-chart-2" />
                    <span class="text-sm font-medium">Продукты на неделю</span>
                </div>
                <span class="text-xs text-muted-foreground">12 товаров</span>
            </div>
            <div
                class="flex items-center justify-between rounded-xl bg-muted p-4 dark:bg-accent"
            >
                <div class="flex items-center gap-3">
                    <ListChecks class="size-4 text-chart-1" />
                    <span class="text-sm font-medium">Бытовая химия</span>
                </div>
                <span class="text-xs text-muted-foreground">5 товаров</span>
            </div>
            <div
                class="flex items-center justify-between rounded-xl bg-muted p-4 dark:bg-accent"
            >
                <div class="flex items-center gap-3">
                    <ListChecks class="size-4 text-chart-5" />
                    <span class="text-sm font-medium">Для ремонта</span>
                </div>
                <span class="text-xs text-muted-foreground">8 товаров</span>
            </div>
        </div>
        <div class="mt-4 border-t border-border pt-4">
            <div class="space-y-2">
                <div class="flex items-center gap-3">
                    <div
                        class="flex size-5 items-center justify-center rounded-md border border-chart-2 bg-chart-2/10"
                    >
                        <CheckCircle class="size-3.5 text-chart-2" />
                    </div>
                    <span class="text-sm text-muted-foreground line-through"
                        >Молоко 2.5%</span
                    >
                </div>
                <div class="flex items-center gap-3">
                    <div class="size-5 rounded-md border border-border"></div>
                    <span class="text-sm">Хлеб белый</span>
                </div>
                <div class="flex items-center gap-3">
                    <div class="size-5 rounded-md border border-border"></div>
                    <span class="text-sm">Яблоки Гала 1 кг</span>
                </div>
            </div>
        </div>
    </div>
</template>
