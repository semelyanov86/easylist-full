<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Input } from '@shared/ui/input';
import { Label } from '@shared/ui/label';

import { index as jobsIndex } from '@/routes/jobs';

type Props = {
    dateFrom: string | null;
    dateTo: string | null;
};

defineProps<Props>();

const updateDate = (field: 'date_from' | 'date_to', value: string): void => {
    router.get(
        jobsIndex().url,
        { [field]: value || undefined },
        { preserveState: true, preserveScroll: true },
    );
};
</script>

<template>
    <div class="flex items-center gap-2">
        <div class="flex items-center gap-1.5">
            <Label class="shrink-0 text-sm text-muted-foreground">С</Label>
            <Input
                type="date"
                :model-value="dateFrom ?? ''"
                class="w-auto"
                @update:model-value="updateDate('date_from', String($event))"
            />
        </div>
        <div class="flex items-center gap-1.5">
            <Label class="shrink-0 text-sm text-muted-foreground">По</Label>
            <Input
                type="date"
                :model-value="dateTo ?? ''"
                class="w-auto"
                @update:model-value="updateDate('date_to', String($event))"
            />
        </div>
    </div>
</template>
