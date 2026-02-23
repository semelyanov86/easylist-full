<script setup lang="ts">
import { Brain, TrendingUp, Zap } from 'lucide-vue-next';

import BenefitCard from './BenefitCard.vue';
</script>

<template>
    <section id="benefits" class="bg-muted px-6 py-20 lg:py-28 dark:bg-accent">
        <div class="mx-auto max-w-6xl">
            <div
                class="mb-4 text-sm font-semibold tracking-widest text-chart-2 uppercase"
            >
                Почему Easylist
            </div>
            <div class="mb-16 grid gap-4 lg:grid-cols-2">
                <h2 class="text-3xl font-bold tracking-tight lg:text-4xl">
                    Преимущества нашей платформы
                </h2>
                <p class="text-muted-foreground lg:text-lg">
                    Узнайте, почему пользователи выбирают Easylist для
                    организации поиска работы и управления повседневными
                    списками.
                </p>
            </div>

            <div class="grid gap-6 md:grid-cols-3">
                <BenefitCard
                    :icon="Brain"
                    icon-bg-class="bg-chart-1/10"
                    icon-text-class="text-chart-1"
                    title="ИИ-аналитика"
                    description="Искусственный интеллект анализирует вакансии, выделяет стек технологий и генерирует персональные сопроводительные письма."
                />
                <BenefitCard
                    :icon="TrendingUp"
                    icon-bg-class="bg-chart-2/10"
                    icon-text-class="text-chart-2"
                    title="Аналитика воронки"
                    description="Отслеживайте эффективность откликов, понимайте какие резюме работают лучше и оптимизируйте стратегию поиска."
                />
                <BenefitCard
                    :icon="Zap"
                    icon-bg-class="bg-chart-4/10"
                    icon-text-class="text-chart-4"
                    title="Всё в одном месте"
                    description="Job Tracker и списки покупок в одном приложении. Организуйте рабочую и личную жизнь без переключений."
                />
            </div>
        </div>
    </section>
</template>
