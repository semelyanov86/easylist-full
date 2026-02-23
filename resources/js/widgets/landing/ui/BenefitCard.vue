<script setup lang="ts">
import type { Component } from 'vue';

interface Props {
    icon: Component;
    iconBgClass: string;
    iconTextClass: string;
    title: string;
    description: string;
}

defineProps<Props>();
</script>

<template>
    <div
        class="rounded-2xl border border-border bg-card p-8 transition hover:shadow-lg"
    >
        <div class="mb-5 inline-flex rounded-xl p-3" :class="iconBgClass">
            <component :is="icon" class="size-6" :class="iconTextClass" />
        </div>
        <h3 class="mb-2 text-lg font-semibold">{{ title }}</h3>
        <p class="text-sm leading-relaxed text-muted-foreground">
            {{ description }}
        </p>
    </div>
</template>
