<script setup lang="ts">
import type { Job } from '@entities/job';
import { Badge } from '@shared/ui/badge';
import { Button } from '@shared/ui/button';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@shared/ui/tooltip';
import {
    Building2,
    Calendar,
    ExternalLink,
    Heart,
    MapPin,
    MoreHorizontal,
    Pencil,
    Trash2,
} from 'lucide-vue-next';

type Props = {
    job: Job;
};

defineProps<Props>();

const formatSalary = (salary: number): string => {
    return new Intl.NumberFormat('ru-RU').format(salary) + ' ₽';
};

const formatDate = (dateString: string): string => {
    return new Date(dateString).toLocaleDateString('ru-RU', {
        day: 'numeric',
        month: 'short',
    });
};
</script>

<template>
    <div
        class="group/card relative rounded-lg border border-border bg-card transition-all hover:shadow-sm"
        :style="{
            borderLeftWidth: '3px',
            borderLeftColor: `var(--status-${job.status.color})`,
        }"
    >
        <div class="flex items-center gap-3 px-4 py-3">
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2">
                    <h3 class="truncate text-sm font-medium text-foreground">
                        {{ job.title }}
                    </h3>
                    <Badge
                        class="shrink-0 border-transparent text-xs"
                        :style="{
                            backgroundColor: `color-mix(in srgb, var(--status-${job.status.color}) 15%, transparent)`,
                            color: `var(--status-${job.status.color})`,
                        }"
                    >
                        {{ job.status.title }}
                    </Badge>
                </div>

                <div
                    class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-0.5 text-xs text-muted-foreground"
                >
                    <span class="inline-flex items-center gap-1">
                        <Building2 class="size-3 shrink-0" />
                        {{ job.company_name }}
                    </span>
                    <span
                        v-if="job.location_city"
                        class="inline-flex items-center gap-1"
                    >
                        <MapPin class="size-3 shrink-0" />
                        {{ job.location_city }}
                    </span>
                    <span v-if="job.salary" class="font-medium text-foreground">
                        {{ formatSalary(job.salary) }}
                    </span>
                    <span class="inline-flex items-center gap-1">
                        <Calendar class="size-3 shrink-0" />
                        {{ formatDate(job.created_at) }}
                    </span>
                </div>
            </div>

            <div
                class="flex shrink-0 items-center gap-0.5 opacity-0 transition-opacity group-hover/card:opacity-100"
            >
                <TooltipProvider>
                    <Tooltip>
                        <TooltipTrigger as-child>
                            <Button variant="ghost" size="icon-sm" disabled>
                                <Heart class="size-3.5" />
                            </Button>
                        </TooltipTrigger>
                        <TooltipContent>Скоро</TooltipContent>
                    </Tooltip>

                    <Tooltip>
                        <TooltipTrigger as-child>
                            <Button variant="ghost" size="icon-sm" disabled>
                                <ExternalLink class="size-3.5" />
                            </Button>
                        </TooltipTrigger>
                        <TooltipContent>Скоро</TooltipContent>
                    </Tooltip>

                    <Tooltip>
                        <TooltipTrigger as-child>
                            <Button variant="ghost" size="icon-sm" disabled>
                                <Pencil class="size-3.5" />
                            </Button>
                        </TooltipTrigger>
                        <TooltipContent>Скоро</TooltipContent>
                    </Tooltip>

                    <Tooltip>
                        <TooltipTrigger as-child>
                            <Button variant="ghost" size="icon-sm" disabled>
                                <Trash2 class="size-3.5" />
                            </Button>
                        </TooltipTrigger>
                        <TooltipContent>Скоро</TooltipContent>
                    </Tooltip>

                    <Tooltip>
                        <TooltipTrigger as-child>
                            <Button variant="ghost" size="icon-sm" disabled>
                                <MoreHorizontal class="size-3.5" />
                            </Button>
                        </TooltipTrigger>
                        <TooltipContent>Скоро</TooltipContent>
                    </Tooltip>
                </TooltipProvider>
            </div>
        </div>
    </div>
</template>
