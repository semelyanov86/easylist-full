<script setup lang="ts">
import type { JobShowTab, JobShowTabId } from '@entities/job';
import { Lock } from 'lucide-vue-next';

type Props = {
    tabs: JobShowTab[];
    modelValue: JobShowTabId;
};

type Emits = {
    'update:modelValue': [value: JobShowTabId];
};

defineProps<Props>();
defineEmits<Emits>();
</script>

<template>
    <div class="flex items-center gap-1 border-b border-border">
        <button
            v-for="tab in tabs"
            :key="tab.id"
            type="button"
            class="relative flex items-center gap-1.5 px-4 py-2.5 text-sm font-medium transition-colors"
            :class="
                !tab.enabled
                    ? 'cursor-not-allowed text-muted-foreground/40'
                    : modelValue === tab.id
                      ? 'text-foreground'
                      : 'text-muted-foreground hover:text-foreground'
            "
            :disabled="!tab.enabled"
            @click="tab.enabled && $emit('update:modelValue', tab.id)"
        >
            <Lock v-if="!tab.enabled" class="size-3 opacity-40" />
            {{ tab.title }}
            <span
                v-if="modelValue === tab.id"
                class="absolute inset-x-0 -bottom-px h-0.5 rounded-t-full bg-foreground"
            />
        </button>
    </div>
</template>
