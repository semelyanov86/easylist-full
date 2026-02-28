<script setup lang="ts">
import type { JobsViewMode } from '@entities/job';
import { router } from '@inertiajs/vue3';
import { Button } from '@shared/ui/button';
import { LayoutGrid, List } from 'lucide-vue-next';

import { update } from '@/routes/preferences/jobs-view-mode';

type Props = {
    modelValue: JobsViewMode;
};

type Emits = {
    'update:modelValue': [value: JobsViewMode];
};

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

const setView = (mode: JobsViewMode): void => {
    if (mode === props.modelValue) {
        return;
    }

    emit('update:modelValue', mode);

    router.put(
        update().url,
        { view_mode: mode },
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
};
</script>

<template>
    <div class="flex items-center gap-1 rounded-md border border-border p-0.5">
        <Button
            variant="ghost"
            size="icon-sm"
            :class="
                modelValue === 'table'
                    ? 'bg-accent text-accent-foreground'
                    : 'text-muted-foreground'
            "
            @click="setView('table')"
        >
            <List class="size-4" />
        </Button>
        <Button
            variant="ghost"
            size="icon-sm"
            :class="
                modelValue === 'kanban'
                    ? 'bg-accent text-accent-foreground'
                    : 'text-muted-foreground'
            "
            @click="setView('kanban')"
        >
            <LayoutGrid class="size-4" />
        </Button>
    </div>
</template>
