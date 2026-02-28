<script setup lang="ts">
import { Button } from '@shared/ui/button';
import { LayoutGrid, List } from 'lucide-vue-next';
import { ref } from 'vue';

type ViewMode = 'list' | 'kanban';

const currentView = ref<ViewMode>(
    (localStorage.getItem('job-view-mode') as ViewMode) ?? 'list',
);

const setView = (mode: ViewMode): void => {
    currentView.value = mode;
    localStorage.setItem('job-view-mode', mode);
};
</script>

<template>
    <div class="flex items-center gap-1 rounded-md border border-border p-0.5">
        <Button
            variant="ghost"
            size="icon-sm"
            :class="
                currentView === 'list'
                    ? 'bg-accent text-accent-foreground'
                    : 'text-muted-foreground'
            "
            @click="setView('list')"
        >
            <List class="size-4" />
        </Button>
        <Button
            variant="ghost"
            size="icon-sm"
            :class="
                currentView === 'kanban'
                    ? 'bg-accent text-accent-foreground'
                    : 'text-muted-foreground'
            "
            disabled
            @click="setView('kanban')"
        >
            <LayoutGrid class="size-4" />
        </Button>
    </div>
</template>
