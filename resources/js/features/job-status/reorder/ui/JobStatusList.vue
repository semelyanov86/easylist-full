<script setup lang="ts">
import type { JobStatus } from '@entities/job-status';
import { router } from '@inertiajs/vue3';
import { Button } from '@shared/ui/button';
import { GripVertical, Pencil, Trash2 } from 'lucide-vue-next';
import Sortable from 'sortablejs';
import { onMounted, ref, watch } from 'vue';

import { reorder } from '@/actions/App/Http/Controllers/Settings/JobStatusController';

type Props = {
    statuses: JobStatus[];
};

const props = defineProps<Props>();

const emit = defineEmits<{
    edit: [status: JobStatus];
    delete: [status: JobStatus];
}>();

const localStatuses = ref<JobStatus[]>([...props.statuses]);
const listRef = ref<HTMLElement | null>(null);

watch(
    () => props.statuses,
    (value) => {
        localStatuses.value = [...value];
    },
);

onMounted(() => {
    if (!listRef.value) {
        return;
    }

    Sortable.create(listRef.value, {
        handle: '[data-handle]',
        animation: 150,
        onEnd: (event) => {
            if (event.oldIndex === undefined || event.newIndex === undefined) {
                return;
            }

            const [moved] = localStatuses.value.splice(event.oldIndex, 1);
            if (!moved) {
                return;
            }
            localStatuses.value.splice(event.newIndex, 0, moved);

            const ids = localStatuses.value.map((s) => s.id);
            router.post(
                reorder().url,
                { ids },
                { preserveScroll: true, preserveState: true },
            );
        },
    });
});
</script>

<template>
    <div v-if="localStatuses.length > 0" ref="listRef" class="space-y-2">
        <div
            v-for="status in localStatuses"
            :key="status.id"
            class="flex items-center gap-3 rounded-lg border border-border p-3"
        >
            <button
                type="button"
                data-handle
                class="cursor-grab text-muted-foreground hover:text-foreground"
            >
                <GripVertical class="size-4" />
            </button>
            <span
                class="block size-3 shrink-0 rounded-full"
                :style="{
                    backgroundColor: `var(--status-${status.color})`,
                }"
            />
            <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-medium text-foreground">
                    {{ status.title }}
                </p>
                <p
                    v-if="status.description"
                    class="truncate text-xs text-muted-foreground"
                >
                    {{ status.description }}
                </p>
            </div>
            <div class="flex gap-1">
                <Button
                    variant="ghost"
                    size="icon-sm"
                    @click="emit('edit', status)"
                >
                    <Pencil class="size-3.5 text-muted-foreground" />
                </Button>
                <Button
                    variant="ghost"
                    size="icon-sm"
                    @click="emit('delete', status)"
                >
                    <Trash2 class="size-3.5 text-muted-foreground" />
                </Button>
            </div>
        </div>
    </div>
    <p v-else class="text-sm text-muted-foreground">
        У вас пока нет статусов откликов.
    </p>
</template>
