<script setup lang="ts">
import type { StatusTab } from '@entities/job';
import type { JobCategory } from '@entities/job-category';
import { router, usePage } from '@inertiajs/vue3';
import { Button } from '@shared/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuRadioGroup,
    DropdownMenuRadioItem,
    DropdownMenuTrigger,
} from '@shared/ui/dropdown-menu';
import { BarChart3, Filter } from 'lucide-vue-next';
import type { AcceptableValue } from 'reka-ui';
import { computed } from 'vue';

import { dashboard } from '@/routes';

type Props = {
    statuses: StatusTab[];
    activeCategoryId: number | null;
};

const props = defineProps<Props>();

const page = usePage();

const categories = computed((): JobCategory[] => {
    return page.props.jobCategories;
});

const totalCount = computed((): number => {
    return props.statuses.reduce((sum, s) => sum + s.count, 0);
});

const selectedValue = computed((): string => {
    return props.activeCategoryId?.toString() ?? 'all';
});

const activeCategory = computed((): string | null => {
    if (!props.activeCategoryId) {
        return null;
    }
    return (
        categories.value.find((c) => c.id === props.activeCategoryId)?.title ??
        null
    );
});

/**
 * Рассчитываем трапеции для SVG-воронки.
 * Каждый сегмент сужается от верхней ширины к нижней.
 * Ширина пропорциональна количеству вакансий в статусе.
 */
type FunnelSegment = {
    status: StatusTab;
    topWidth: number;
    bottomWidth: number;
    yStart: number;
    yEnd: number;
    path: string;
};

const FUNNEL_MIN_WIDTH = 20;
const FUNNEL_MAX_WIDTH = 100;
const SEGMENT_GAP = 2;

const segments = computed((): FunnelSegment[] => {
    const count = props.statuses.length;
    if (count === 0) {
        return [];
    }

    const maxCount = Math.max(...props.statuses.map((s) => s.count), 1);
    const totalHeight = 100;
    const gapTotal = (count - 1) * SEGMENT_GAP;
    const segmentHeight = (totalHeight - gapTotal) / count;

    return props.statuses.map((status, index): FunnelSegment => {
        const ratio = status.count / maxCount;
        const width =
            FUNNEL_MIN_WIDTH + ratio * (FUNNEL_MAX_WIDTH - FUNNEL_MIN_WIDTH);

        const nextStatus = props.statuses[index + 1];
        const nextRatio = nextStatus ? nextStatus.count / maxCount : 0;
        const nextWidth = nextStatus
            ? FUNNEL_MIN_WIDTH +
              nextRatio * (FUNNEL_MAX_WIDTH - FUNNEL_MIN_WIDTH)
            : width * 0.6;

        const yStart = index * (segmentHeight + SEGMENT_GAP);
        const yEnd = yStart + segmentHeight;

        const topLeft = (FUNNEL_MAX_WIDTH - width) / 2;
        const topRight = topLeft + width;
        const bottomLeft = (FUNNEL_MAX_WIDTH - nextWidth) / 2;
        const bottomRight = bottomLeft + nextWidth;

        const path = `M ${topLeft} ${yStart} L ${topRight} ${yStart} L ${bottomRight} ${yEnd} L ${bottomLeft} ${yEnd} Z`;

        return {
            status,
            topWidth: width,
            bottomWidth: nextWidth,
            yStart,
            yEnd,
            path,
        };
    });
});

const onCategoryChange = (value: AcceptableValue): void => {
    const categoryId = value === 'all' ? undefined : String(value);

    router.get(
        dashboard().url,
        { funnel_category_id: categoryId },
        {
            preserveState: true,
            preserveScroll: true,
            only: ['jobFunnel', 'funnelCategoryId'],
        },
    );
};
</script>

<template>
    <div
        class="overflow-hidden rounded-lg border border-border bg-card shadow-sm"
    >
        <div
            class="flex items-center justify-between border-b border-border px-3 py-2"
        >
            <div class="flex items-center gap-1.5">
                <BarChart3 class="size-3.5 text-muted-foreground" />
                <h3 class="text-xs font-semibold text-foreground">
                    Воронка вакансий
                </h3>
                <span
                    v-if="totalCount > 0"
                    class="rounded bg-muted px-1.5 py-px text-xs font-medium text-muted-foreground tabular-nums"
                >
                    {{ totalCount }}
                </span>
            </div>

            <div class="flex items-center gap-1">
                <span
                    v-if="activeCategory"
                    class="text-xs text-muted-foreground"
                >
                    {{ activeCategory }}
                </span>

                <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                        <Button variant="ghost" size="icon" class="size-6">
                            <Filter
                                class="size-3"
                                :class="
                                    activeCategoryId
                                        ? 'text-primary'
                                        : 'text-muted-foreground'
                                "
                            />
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end" class="w-48">
                        <DropdownMenuRadioGroup
                            :model-value="selectedValue"
                            @update:model-value="onCategoryChange"
                        >
                            <DropdownMenuRadioItem value="all">
                                Все папки
                            </DropdownMenuRadioItem>
                            <DropdownMenuRadioItem
                                v-for="category in categories"
                                :key="category.id"
                                :value="category.id.toString()"
                            >
                                {{ category.title }}
                            </DropdownMenuRadioItem>
                        </DropdownMenuRadioGroup>
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>
        </div>

        <div v-if="statuses.length > 0" class="p-3">
            <div class="flex items-start gap-4">
                <!-- SVG-воронка -->
                <div class="w-28 shrink-0">
                    <svg
                        viewBox="0 0 100 100"
                        preserveAspectRatio="none"
                        class="h-full w-full"
                        :style="{
                            height: `${segments.length * 2}rem`,
                        }"
                    >
                        <path
                            v-for="segment in segments"
                            :key="segment.status.id"
                            :d="segment.path"
                            :style="{
                                fill: `color-mix(in srgb, var(--status-${segment.status.color}) 65%, transparent)`,
                                stroke: `var(--status-${segment.status.color})`,
                                strokeWidth: 0.5,
                            }"
                            class="transition-all duration-300"
                        />
                    </svg>
                </div>

                <!-- Легенда -->
                <div
                    class="flex min-w-0 flex-1 flex-col justify-between"
                    :style="{ height: `${segments.length * 2}rem` }"
                >
                    <div
                        v-for="segment in segments"
                        :key="segment.status.id"
                        class="flex flex-1 items-center gap-2"
                    >
                        <span
                            class="size-2 shrink-0 rounded-full"
                            :style="{
                                backgroundColor: `var(--status-${segment.status.color})`,
                            }"
                        />
                        <span class="min-w-0 truncate text-xs text-foreground">
                            {{ segment.status.title }}
                        </span>
                        <span
                            class="ml-auto shrink-0 text-xs font-medium text-muted-foreground tabular-nums"
                        >
                            {{ segment.status.count }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div
            v-else
            class="flex flex-col items-center gap-2 px-4 py-8 text-center"
        >
            <div
                class="flex size-8 items-center justify-center rounded-full bg-muted/60"
            >
                <BarChart3 class="size-4 text-muted-foreground/40" />
            </div>
            <div class="text-center">
                <p class="text-xs font-medium text-muted-foreground/70">
                    Нет статусов
                </p>
                <p class="mt-0.5 text-xs text-muted-foreground/50">
                    Создайте статусы для отслеживания вакансий
                </p>
            </div>
        </div>
    </div>
</template>
