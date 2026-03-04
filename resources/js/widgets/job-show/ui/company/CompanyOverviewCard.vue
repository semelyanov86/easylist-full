<script setup lang="ts">
import type { CompanyInfoDetails } from '@entities/company-info';
import {
    Banknote,
    Building2,
    Calendar,
    MapPin,
    TrendingUp,
    Users,
} from 'lucide-vue-next';
import { computed } from 'vue';

type Props = {
    info: CompanyInfoDetails;
};

const props = defineProps<Props>();

type StatItem = {
    label: string;
    value: string;
    icon: typeof Building2;
};

const stats = computed((): StatItem[] => {
    const items: StatItem[] = [];

    if (props.info.industry) {
        items.push({
            label: 'Отрасль',
            value: props.info.industry,
            icon: Building2,
        });
    }
    if (props.info.founded) {
        items.push({
            label: 'Основана',
            value: props.info.founded,
            icon: Calendar,
        });
    }
    if (props.info.employees) {
        items.push({
            label: 'Сотрудники',
            value: props.info.employees,
            icon: Users,
        });
    }
    if (props.info.revenue) {
        items.push({
            label: 'Выручка',
            value: props.info.revenue,
            icon: TrendingUp,
        });
    }
    if (props.info.funding) {
        items.push({
            label: 'Финансирование',
            value: props.info.funding,
            icon: Banknote,
        });
    }
    if (props.info.hq) {
        items.push({
            label: 'Штаб-квартира',
            value: props.info.hq,
            icon: MapPin,
        });
    }
    if (props.info.local_office) {
        items.push({
            label: 'Локальный офис',
            value: props.info.local_office,
            icon: MapPin,
        });
    }

    return items;
});
</script>

<template>
    <div
        v-if="info.overview || stats.length > 0"
        class="overflow-hidden rounded-xl border border-border bg-card shadow-sm"
    >
        <div class="p-5 sm:p-6">
            <p
                v-if="info.overview"
                class="text-sm leading-relaxed text-muted-foreground"
            >
                {{ info.overview }}
            </p>

            <div
                v-if="stats.length > 0"
                class="mt-4 grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-4"
            >
                <div
                    v-for="stat in stats"
                    :key="stat.label"
                    class="flex items-center gap-2.5 rounded-lg bg-muted/50 px-3 py-2.5"
                >
                    <component
                        :is="stat.icon"
                        class="size-4 shrink-0 text-muted-foreground/60"
                    />
                    <div class="min-w-0">
                        <p class="truncate text-xs text-muted-foreground">
                            {{ stat.label }}
                        </p>
                        <p class="truncate text-sm font-medium text-foreground">
                            {{ stat.value }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
