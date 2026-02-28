<script setup lang="ts">
import type { JobFilters } from '@entities/job';

import JobDateFilter from './JobDateFilter.vue';
import JobSearchInput from './JobSearchInput.vue';
import JobViewToggle from './JobViewToggle.vue';

type Props = {
    filters: JobFilters;
};

defineProps<Props>();
</script>

<template>
    <div
        class="flex flex-wrap items-center gap-2 rounded-lg border border-border bg-card p-2"
    >
        <div class="min-w-48 flex-1">
            <JobSearchInput :model-value="filters.search" />
        </div>
        <div class="flex items-center gap-2">
            <JobDateFilter
                :date-from="filters.date_from"
                :date-to="filters.date_to"
            />
            <div class="hidden h-5 w-px bg-border md:block" role="separator" />
            <JobViewToggle />
        </div>
    </div>
</template>
