<script setup lang="ts">
import { Badge } from '@shared/ui/badge';
import { Code2 } from 'lucide-vue-next';

type Props = {
    techStack: string[];
};

defineProps<Props>();
</script>

<template>
    <div
        class="overflow-hidden rounded-xl border border-border bg-card shadow-sm"
    >
        <div class="flex items-center gap-2 border-b border-border px-5 py-3">
            <Code2 class="size-4 text-muted-foreground" />
            <h3 class="text-sm font-semibold text-foreground">Технологии</h3>
            <span class="text-xs text-muted-foreground">
                {{ techStack.length }}
            </span>
        </div>
        <div class="flex flex-wrap gap-1.5 p-4">
            <Badge
                v-for="tech in techStack"
                :key="tech"
                variant="secondary"
                class="text-xs"
            >
                {{ tech }}
            </Badge>
        </div>
    </div>
</template>
