<script setup lang="ts">
import type { JobDetail } from '@entities/job';
import { useCompanyAnalysis } from '@features/company-info';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

import CompanyEmptyState from './company/CompanyEmptyState.vue';
import CompanyHeader from './company/CompanyHeader.vue';
import CompanyLinksCard from './company/CompanyLinksCard.vue';
import CompanyNewsCard from './company/CompanyNewsCard.vue';
import CompanyOverviewCard from './company/CompanyOverviewCard.vue';
import CompanyReviewsCard from './company/CompanyReviewsCard.vue';
import CompanyTechStackCard from './company/CompanyTechStackCard.vue';

type Props = {
    job: JobDetail;
};

const props = defineProps<Props>();

const page = usePage();
const isPremium = computed((): boolean => page.props.auth.user.is_premium);
const info = computed(() => props.job.company_info?.info ?? null);

const { loading, error, analyze } = useCompanyAnalysis();

function handleAnalyze(): void {
    analyze(props.job.id);
}

type LinkItem = {
    label: string;
    url: string;
};

const linkItems = computed((): LinkItem[] => {
    if (!info.value?.links) {
        return [];
    }

    const items: LinkItem[] = [];
    const links = info.value.links;

    if (links.website) {
        items.push({ label: 'Веб-сайт', url: links.website });
    }
    if (links.linkedin) {
        items.push({ label: 'LinkedIn', url: links.linkedin });
    }
    if (links.glassdoor) {
        items.push({ label: 'Glassdoor', url: links.glassdoor });
    }
    if (links.kununu) {
        items.push({ label: 'Kununu', url: links.kununu });
    }

    return items;
});
</script>

<template>
    <div class="space-y-5">
        <CompanyHeader
            :company-name="job.company_name"
            :location-city="job.location_city"
            :info="info"
            :link-items="linkItems"
        />

        <template v-if="info">
            <CompanyOverviewCard :info="info" />

            <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                <CompanyTechStackCard
                    v-if="info.tech_stack && info.tech_stack.length > 0"
                    :tech-stack="info.tech_stack"
                />

                <CompanyReviewsCard
                    v-if="info.reviews"
                    :reviews="info.reviews"
                />

                <CompanyNewsCard
                    v-if="info.recent_news && info.recent_news.length > 0"
                    :news="info.recent_news"
                />

                <CompanyLinksCard
                    v-if="linkItems.length > 0"
                    :link-items="linkItems"
                />
            </div>
        </template>

        <CompanyEmptyState
            v-else
            :is-premium="isPremium"
            :loading="loading"
            :error="error"
            @analyze="handleAnalyze"
        />
    </div>
</template>
