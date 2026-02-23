<script setup lang="ts">
import { BarChart3, Target } from 'lucide-vue-next';
</script>

<template>
    <div class="rounded-2xl border border-border bg-card p-6 shadow-lg lg:p-8">
        <div class="mb-4 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <BarChart3 class="size-5 text-chart-1" />
                <span class="font-semibold">Воронка откликов</span>
            </div>
            <span
                class="rounded-full bg-chart-2/10 px-3 py-1 text-xs font-medium text-chart-2"
                >Этот месяц</span
            >
        </div>
        <div class="space-y-3">
            <div>
                <div class="mb-1 flex justify-between text-sm">
                    <span class="text-muted-foreground">Отправлено</span>
                    <span class="font-medium">45</span>
                </div>
                <div class="h-2 overflow-hidden rounded-full bg-secondary">
                    <div class="h-full w-full rounded-full bg-chart-1"></div>
                </div>
            </div>
            <div>
                <div class="mb-1 flex justify-between text-sm">
                    <span class="text-muted-foreground">Просмотрено</span>
                    <span class="font-medium">28</span>
                </div>
                <div class="h-2 overflow-hidden rounded-full bg-secondary">
                    <div class="h-full w-3/5 rounded-full bg-chart-2"></div>
                </div>
            </div>
            <div>
                <div class="mb-1 flex justify-between text-sm">
                    <span class="text-muted-foreground">Интервью</span>
                    <span class="font-medium">12</span>
                </div>
                <div class="h-2 overflow-hidden rounded-full bg-secondary">
                    <div class="h-full w-1/4 rounded-full bg-chart-4"></div>
                </div>
            </div>
            <div>
                <div class="mb-1 flex justify-between text-sm">
                    <span class="text-muted-foreground">Оффер</span>
                    <span class="font-medium">3</span>
                </div>
                <div class="h-2 overflow-hidden rounded-full bg-secondary">
                    <div class="h-full w-1/12 rounded-full bg-chart-5"></div>
                </div>
            </div>
        </div>
        <div class="mt-6 border-t border-border pt-4">
            <div class="flex items-center gap-3">
                <Target class="size-5 text-chart-4" />
                <div>
                    <div class="text-sm font-medium">Конверсия в оффер</div>
                    <div class="text-xs text-muted-foreground">
                        6.7% — выше среднего
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
