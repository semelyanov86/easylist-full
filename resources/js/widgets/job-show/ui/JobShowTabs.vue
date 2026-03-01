<script setup lang="ts">
import { Lock } from 'lucide-vue-next';

const tabs = [
    { title: 'Общий обзор', active: true },
    { title: 'Документы', active: false },
    { title: 'Компания', active: false },
    { title: 'Контакты', active: false },
    { title: 'Задачи', active: false },
];
</script>

<template>
    <div class="flex items-center gap-1 border-b border-border">
        <button
            v-for="tab in tabs"
            :key="tab.title"
            type="button"
            class="relative flex items-center gap-1.5 px-4 py-2.5 text-sm font-medium transition-colors"
            :class="
                tab.active
                    ? 'text-foreground'
                    : 'cursor-not-allowed text-muted-foreground/40 hover:text-muted-foreground/60'
            "
            :disabled="!tab.active"
        >
            <Lock v-if="!tab.active" class="size-3 opacity-40" />
            {{ tab.title }}
            <span
                v-if="tab.active"
                class="absolute inset-x-0 -bottom-px h-0.5 rounded-t-full bg-foreground"
            />
        </button>
    </div>
</template>
