<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Input } from '@shared/ui/input';
import { Search } from 'lucide-vue-next';
import { ref, watch } from 'vue';

import { index as jobsIndex } from '@/routes/jobs';

type Props = {
    modelValue: string | null;
};

const props = defineProps<Props>();

const searchQuery = ref(props.modelValue ?? '');
let debounceTimer: ReturnType<typeof setTimeout> | null = null;

watch(searchQuery, (value) => {
    if (debounceTimer) {
        clearTimeout(debounceTimer);
    }

    debounceTimer = setTimeout(() => {
        router.get(
            jobsIndex().url,
            { search: value || undefined },
            { preserveState: true, preserveScroll: true },
        );
    }, 300);
});
</script>

<template>
    <div class="relative">
        <Search
            class="absolute top-1/2 left-2.5 size-4 -translate-y-1/2 text-muted-foreground"
        />
        <Input
            v-model="searchQuery"
            type="text"
            placeholder="Поиск по названию, компании, городу..."
            class="border-transparent bg-transparent pl-8 shadow-none focus-visible:border-transparent focus-visible:ring-0"
        />
    </div>
</template>
