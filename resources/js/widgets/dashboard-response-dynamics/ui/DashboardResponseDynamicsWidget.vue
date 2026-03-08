<script setup lang="ts">
import type { DashboardResponsePoint } from '@entities/job';
import { ArrowDown, ArrowUp, Minus, TrendingUp } from 'lucide-vue-next';
import { type Component, computed } from 'vue';

type Props = {
    points: DashboardResponsePoint[];
};

const props = defineProps<Props>();

const totalCount = computed((): number => {
    return props.points.reduce((sum, p) => sum + p.count, 0);
});

const maxPoint = computed(
    (): { index: number; count: number; label: string } | null => {
        if (props.points.length === 0) {
            return null;
        }

        let maxIdx = 0;
        let maxVal = 0;

        props.points.forEach((p, i) => {
            if (p.count > maxVal) {
                maxVal = p.count;
                maxIdx = i;
            }
        });

        if (maxVal === 0) {
            return null;
        }

        const pt = props.points[maxIdx];
        if (!pt) {
            return null;
        }

        return { index: maxIdx, count: pt.count, label: pt.label };
    },
);

type TrendInfo = {
    icon: Component;
    label: string;
    colorClass: string;
};

const trend = computed((): TrendInfo => {
    const len = props.points.length;
    if (len < 2) {
        return {
            icon: Minus,
            label: '—',
            colorClass: 'text-muted-foreground',
        };
    }

    // Сравниваем последние 4 недели с предыдущими 4
    const half = Math.min(4, Math.floor(len / 2));
    const recent = props.points.slice(-half).reduce((s, p) => s + p.count, 0);
    const previous = props.points
        .slice(-half * 2, -half)
        .reduce((s, p) => s + p.count, 0);

    if (recent > previous) {
        return {
            icon: ArrowUp,
            label: `+${recent - previous}`,
            colorClass: 'text-status-green',
        };
    }

    if (recent < previous) {
        return {
            icon: ArrowDown,
            label: `${recent - previous}`,
            colorClass: 'text-status-red',
        };
    }

    return {
        icon: Minus,
        label: '0',
        colorClass: 'text-muted-foreground',
    };
});

/**
 * SVG-спарклайн: плавная кривая Catmull-Rom → кубические безье,
 * градиентная заливка, сетка, подсветка пика.
 */
const SVG_WIDTH = 300;
const SVG_HEIGHT = 80;
const PADDING_X = 12;
const PADDING_Y = 10;
const PADDING_BOTTOM = 4;

type DotCoord = {
    x: number;
    y: number;
    label: string;
    count: number;
    isPeak: boolean;
};

const at = (coords: DotCoord[], index: number): DotCoord => {
    const clamped = Math.max(0, Math.min(index, coords.length - 1));

    return coords[clamped] as DotCoord;
};

/**
 * Catmull-Rom → кубические безье для гладкой линии.
 */
const buildSmoothPath = (
    coords: DotCoord[],
): { linePath: string; areaPath: string } => {
    if (coords.length < 2) {
        const c = at(coords, 0);

        return {
            linePath: `M ${c.x},${c.y}`,
            areaPath: '',
        };
    }

    const yBase = SVG_HEIGHT - PADDING_BOTTOM;
    const start = at(coords, 0);
    let path = `M ${start.x},${start.y}`;

    for (let i = 0; i < coords.length - 1; i++) {
        const p0 = at(coords, i - 1);
        const p1 = at(coords, i);
        const p2 = at(coords, i + 1);
        const p3 = at(coords, i + 2);

        const tension = 0.35;

        const cp1x = p1.x + ((p2.x - p0.x) * tension) / 3;
        const cp1y = p1.y + ((p2.y - p0.y) * tension) / 3;
        const cp2x = p2.x - ((p3.x - p1.x) * tension) / 3;
        const cp2y = p2.y - ((p3.y - p1.y) * tension) / 3;

        path += ` C ${cp1x},${cp1y} ${cp2x},${cp2y} ${p2.x},${p2.y}`;
    }

    const first = at(coords, 0);
    const last = at(coords, coords.length - 1);
    const areaPath = `${path} L ${last.x},${yBase} L ${first.x},${yBase} Z`;

    return { linePath: path, areaPath };
};

const chartData = computed(() => {
    const count = props.points.length;
    if (count === 0) {
        return {
            linePath: '',
            areaPath: '',
            dots: [] as DotCoord[],
            gridLines: [] as number[],
            lineLength: 0,
        };
    }

    const maxCount = Math.max(...props.points.map((p) => p.count), 1);
    const peakIdx = maxPoint.value?.index ?? -1;

    const usableWidth = SVG_WIDTH - PADDING_X * 2;
    const usableHeight = SVG_HEIGHT - PADDING_Y - PADDING_BOTTOM;
    const stepX = count > 1 ? usableWidth / (count - 1) : 0;

    const coords: DotCoord[] = props.points.map((point, i) => ({
        x: PADDING_X + i * stepX,
        y: PADDING_Y + usableHeight - (point.count / maxCount) * usableHeight,
        label: point.label,
        count: point.count,
        isPeak: i === peakIdx,
    }));

    const { linePath, areaPath } = buildSmoothPath(coords);

    // Горизонтальная сетка: 3 линии
    const gridLines: number[] = [];
    for (let g = 1; g <= 3; g++) {
        gridLines.push(PADDING_Y + (usableHeight / 4) * g);
    }

    // Примерная длина пути для анимации
    let lineLength = 0;
    for (let i = 1; i < coords.length; i++) {
        const prev = at(coords, i - 1);
        const curr = at(coords, i);
        lineLength += Math.sqrt(
            (curr.x - prev.x) ** 2 + (curr.y - prev.y) ** 2,
        );
    }

    return { linePath, areaPath, dots: coords, gridLines, lineLength };
});

const firstLabel = computed((): string => {
    return props.points[0]?.label ?? '';
});

const lastLabel = computed((): string => {
    return props.points[props.points.length - 1]?.label ?? '';
});

const midLabels = computed((): { label: string; x: number }[] => {
    const dots = chartData.value.dots;
    if (dots.length < 5) {
        return [];
    }

    // Показываем каждую 3-ю точку (кроме первой и последней)
    const result: { label: string; x: number }[] = [];
    for (let i = 3; i < dots.length - 1; i += 3) {
        const dot = dots[i];
        if (dot) {
            result.push({ label: dot.label, x: dot.x });
        }
    }

    return result;
});
</script>

<template>
    <div
        class="overflow-hidden rounded-lg border border-border bg-card shadow-sm"
    >
        <!-- Заголовок -->
        <div
            class="flex items-center justify-between border-b border-border px-3 py-2"
        >
            <div class="flex items-center gap-1.5">
                <TrendingUp class="size-3.5 text-status-blue" />
                <h3 class="text-xs font-semibold text-foreground">
                    Динамика откликов
                </h3>
                <span
                    v-if="totalCount > 0"
                    class="rounded bg-muted px-1.5 py-px text-xs font-medium text-muted-foreground tabular-nums"
                >
                    {{ totalCount }}
                </span>
            </div>

            <!-- Тренд -->
            <div
                v-if="totalCount > 0"
                class="flex items-center gap-0.5"
                :class="trend.colorClass"
            >
                <component :is="trend.icon" class="size-3" />
                <span class="text-xs font-medium tabular-nums">
                    {{ trend.label }}
                </span>
            </div>
        </div>

        <div v-if="totalCount > 0" class="px-3 pt-3 pb-2">
            <!-- SVG-график -->
            <svg
                :viewBox="`0 0 ${SVG_WIDTH} ${SVG_HEIGHT}`"
                class="response-dynamics-chart w-full"
                preserveAspectRatio="none"
                :style="{ height: '5.5rem' }"
            >
                <defs>
                    <!-- Градиент заливки -->
                    <linearGradient
                        id="areaGradient"
                        x1="0"
                        y1="0"
                        x2="0"
                        y2="1"
                    >
                        <stop
                            offset="0%"
                            :style="{
                                stopColor: 'var(--status-blue)',
                                stopOpacity: 0.15,
                            }"
                        />
                        <stop
                            offset="100%"
                            :style="{
                                stopColor: 'var(--status-blue)',
                                stopOpacity: 0.01,
                            }"
                        />
                    </linearGradient>

                    <!-- Градиент линии -->
                    <linearGradient
                        id="lineGradient"
                        x1="0"
                        y1="0"
                        x2="1"
                        y2="0"
                    >
                        <stop
                            offset="0%"
                            :style="{
                                stopColor: 'var(--status-blue)',
                                stopOpacity: 0.4,
                            }"
                        />
                        <stop
                            offset="40%"
                            :style="{
                                stopColor: 'var(--status-blue)',
                                stopOpacity: 0.8,
                            }"
                        />
                        <stop
                            offset="100%"
                            :style="{
                                stopColor: 'var(--status-blue)',
                                stopOpacity: 1,
                            }"
                        />
                    </linearGradient>
                </defs>

                <!-- Горизонтальная сетка -->
                <line
                    v-for="(y, gi) in chartData.gridLines"
                    :key="gi"
                    :x1="PADDING_X"
                    :y1="y"
                    :x2="SVG_WIDTH - PADDING_X"
                    :y2="y"
                    :style="{
                        stroke: 'var(--border)',
                        strokeWidth: 0.5,
                        strokeDasharray: '2 3',
                    }"
                />

                <!-- Заливка под кривой -->
                <path
                    :d="chartData.areaPath"
                    fill="url(#areaGradient)"
                    class="response-dynamics-area"
                />

                <!-- Плавная кривая -->
                <path
                    :d="chartData.linePath"
                    fill="none"
                    stroke="url(#lineGradient)"
                    :stroke-width="2"
                    stroke-linejoin="round"
                    stroke-linecap="round"
                    class="response-dynamics-line"
                    :style="{
                        strokeDasharray: chartData.lineLength,
                        strokeDashoffset: chartData.lineLength,
                    }"
                />

                <!-- Интерактивные точки -->
                <g v-for="(dot, i) in chartData.dots" :key="i">
                    <!-- Подсветка пика -->
                    <circle
                        v-if="dot.isPeak"
                        :cx="dot.x"
                        :cy="dot.y"
                        r="6"
                        fill="none"
                        :style="{
                            stroke: 'var(--status-blue)',
                            strokeWidth: 1,
                            strokeOpacity: 0.25,
                        }"
                        class="response-dynamics-peak-ring"
                    />

                    <!-- Невидимая область для hover -->
                    <circle
                        :cx="dot.x"
                        :cy="dot.y"
                        r="8"
                        fill="transparent"
                        class="response-dynamics-hover-target"
                    />

                    <!-- Видимая точка -->
                    <circle
                        :cx="dot.x"
                        :cy="dot.y"
                        :r="dot.isPeak ? 3 : 2"
                        class="response-dynamics-dot"
                        :style="{
                            fill: dot.isPeak
                                ? 'var(--status-blue)'
                                : 'var(--card)',
                            stroke: 'var(--status-blue)',
                            strokeWidth: dot.isPeak ? 0 : 1.5,
                        }"
                    >
                        <title>{{ dot.label }}: {{ dot.count }}</title>
                    </circle>
                </g>
            </svg>

            <!-- Подписи недель -->
            <div class="relative mt-1" :style="{ height: '0.875rem' }">
                <span
                    class="absolute left-0 text-xs text-muted-foreground/50 tabular-nums"
                >
                    {{ firstLabel }}
                </span>
                <span
                    v-for="mid in midLabels"
                    :key="mid.label"
                    class="absolute text-xs text-muted-foreground/35 tabular-nums"
                    :style="{
                        left: `${(mid.x / SVG_WIDTH) * 100}%`,
                        transform: 'translateX(-50%)',
                    }"
                >
                    {{ mid.label }}
                </span>
                <span
                    class="absolute right-0 text-xs text-muted-foreground/50 tabular-nums"
                >
                    {{ lastLabel }}
                </span>
            </div>

            <!-- Статистика -->
            <div
                v-if="maxPoint"
                class="mt-2 flex items-center gap-3 border-t border-border/50 pt-2"
            >
                <div class="flex items-center gap-1">
                    <span class="text-xs text-muted-foreground/60">Пик:</span>
                    <span
                        class="text-xs font-medium text-foreground tabular-nums"
                    >
                        {{ maxPoint.count }}
                    </span>
                    <span class="text-xs text-muted-foreground/40">
                        (нед. {{ maxPoint.label }})
                    </span>
                </div>
            </div>
        </div>

        <!-- Пустое состояние -->
        <div
            v-else
            class="flex flex-col items-center gap-2 px-4 py-8 text-center"
        >
            <div
                class="flex size-8 items-center justify-center rounded-full bg-status-blue/10"
            >
                <TrendingUp class="size-4 text-status-blue/40" />
            </div>
            <div class="text-center">
                <p class="text-xs font-medium text-muted-foreground/70">
                    Нет данных
                </p>
                <p class="mt-0.5 text-xs text-muted-foreground/50">
                    Статистика появится после добавления вакансий
                </p>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Анимация отрисовки линии */
.response-dynamics-line {
    animation: draw-line 1.2s cubic-bezier(0.22, 0.61, 0.36, 1) forwards;
}

@keyframes draw-line {
    to {
        stroke-dashoffset: 0;
    }
}

/* Анимация появления заливки */
.response-dynamics-area {
    opacity: 0;
    animation: fade-area 0.6s ease-out 0.8s forwards;
}

@keyframes fade-area {
    to {
        opacity: 1;
    }
}

/* Анимация пульсации кольца пика */
.response-dynamics-peak-ring {
    animation: pulse-ring 2.5s ease-in-out infinite;
}

@keyframes pulse-ring {
    0%,
    100% {
        r: 6;
        stroke-opacity: 0.25;
    }
    50% {
        r: 8;
        stroke-opacity: 0.1;
    }
}

/* Hover-эффект на точках */
.response-dynamics-dot {
    transition:
        r 0.15s ease,
        fill 0.15s ease;
}

.response-dynamics-hover-target:hover + .response-dynamics-dot {
    r: 4;
    fill: var(--status-blue) !important;
    stroke-width: 0;
}

/* Появление графика целиком */
.response-dynamics-chart {
    animation: chart-appear 0.4s ease-out;
}

@keyframes chart-appear {
    from {
        opacity: 0;
        transform: translateY(4px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
