<script setup lang="ts">
import { ChevronDown } from 'lucide-vue-next';
import { ref } from 'vue';

import { faqs } from '../model/faqData';

const openFaq = ref<number | null>(null);

const toggleFaq = (index: number): void => {
    openFaq.value = openFaq.value === index ? null : index;
};
</script>

<template>
    <section id="faq" class="px-6 py-20 lg:py-28">
        <div class="mx-auto max-w-3xl">
            <div
                class="mb-4 text-center text-sm font-semibold tracking-widest text-chart-4 uppercase"
            >
                FAQ
            </div>
            <h2
                class="mb-4 text-center text-3xl font-bold tracking-tight lg:text-4xl"
            >
                Часто задаваемые вопросы
            </h2>
            <p class="mx-auto mb-12 max-w-xl text-center text-muted-foreground">
                Ответы на популярные вопросы о возможностях Easylist.
            </p>
            <div class="space-y-3">
                <div
                    v-for="(faq, index) in faqs"
                    :key="index"
                    class="rounded-xl border border-border bg-card transition"
                >
                    <button
                        class="flex w-full items-center justify-between p-5 text-left text-sm font-medium"
                        @click="toggleFaq(index)"
                    >
                        {{ faq.question }}
                        <ChevronDown
                            class="size-4 shrink-0 text-muted-foreground transition-transform duration-200"
                            :class="{ 'rotate-180': openFaq === index }"
                        />
                    </button>
                    <div
                        v-show="openFaq === index"
                        class="border-t border-border px-5 pt-3 pb-5 text-sm leading-relaxed text-muted-foreground"
                    >
                        {{ faq.answer }}
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
