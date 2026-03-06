<script setup lang="ts">
import type { CompanyInfoDetails } from '@entities/company-info';
import {
    Banknote,
    Calendar,
    Factory,
    Globe,
    Landmark,
    Linkedin,
    TrendingUp,
    Users,
} from 'lucide-vue-next';
import { computed } from 'vue';

type Props = {
    companyInfo: CompanyInfoDetails;
};

const props = defineProps<Props>();

const companyDetails = computed(() => {
    const info = props.companyInfo;

    return [
        { label: 'Индустрия', value: info.industry, icon: Factory },
        { label: 'Основана', value: info.founded, icon: Calendar },
        { label: 'Сотрудники', value: info.employees, icon: Users },
        { label: 'Выручка', value: info.revenue, icon: TrendingUp },
        { label: 'Финансирование', value: info.funding, icon: Banknote },
        { label: 'Штаб-квартира', value: info.hq, icon: Landmark },
    ].filter((item) => item.value);
});
</script>

<template>
    <div
        class="overflow-hidden rounded-xl border border-border bg-card shadow-sm"
    >
        <div
            class="flex items-center gap-2 border-b border-border px-6 py-3"
        >
            <h2 class="text-sm font-semibold text-foreground">
                О компании
            </h2>
        </div>

        <div class="p-6">
            <p
                v-if="companyInfo.overview"
                class="mb-6 text-sm leading-relaxed text-muted-foreground"
            >
                {{ companyInfo.overview }}
            </p>

            <div
                v-if="companyDetails.length > 0"
                class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3"
            >
                <div
                    v-for="detail in companyDetails"
                    :key="detail.label"
                    class="flex items-start gap-3 rounded-lg bg-muted/50 p-4"
                >
                    <component
                        :is="detail.icon"
                        class="mt-0.5 size-4 shrink-0 text-muted-foreground"
                    />
                    <div>
                        <p
                            class="text-xs font-medium text-muted-foreground"
                        >
                            {{ detail.label }}
                        </p>
                        <p
                            class="mt-0.5 text-sm font-medium text-foreground"
                        >
                            {{ detail.value }}
                        </p>
                    </div>
                </div>
            </div>

            <div
                v-if="companyInfo.links"
                class="mt-6 flex flex-wrap gap-3"
            >
                <a
                    v-if="companyInfo.links.website"
                    :href="companyInfo.links.website"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center gap-1.5 rounded-md border border-border px-3 py-1.5 text-xs font-medium text-muted-foreground transition hover:bg-muted hover:text-foreground"
                >
                    <Globe class="size-3.5" />
                    Сайт
                </a>
                <a
                    v-if="companyInfo.links.linkedin"
                    :href="companyInfo.links.linkedin"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center gap-1.5 rounded-md border border-border px-3 py-1.5 text-xs font-medium text-muted-foreground transition hover:bg-muted hover:text-foreground"
                >
                    <Linkedin class="size-3.5" />
                    LinkedIn
                </a>
            </div>
        </div>
    </div>
</template>
