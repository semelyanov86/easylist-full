<script setup lang="ts">
import { FaqAccordion } from '@features/faq';
import { Head } from '@inertiajs/vue3';
import {
    BenefitsBlock,
    CtaBanner,
    FeatureShowcaseSection,
    LandingFooter,
    LandingHeader,
    LandingHero,
    TestimonialsBlock,
} from '@widgets/landing';

interface Props {
    canRegister: boolean;
}

withDefaults(defineProps<Props>(), {
    canRegister: true,
});
</script>

<template>
    <Head title="Easylist — Организуйте жизнь просто" />
    <div class="min-h-screen scroll-smooth bg-background text-foreground">
        <LandingHeader :can-register="canRegister" />
        <LandingHero :can-register="canRegister" />
        <BenefitsBlock />
        <FeatureShowcaseSection />
        <TestimonialsBlock />
        <FaqAccordion />
        <CtaBanner :can-register="canRegister" />
        <LandingFooter :can-register="canRegister" />
    </div>
</template>
