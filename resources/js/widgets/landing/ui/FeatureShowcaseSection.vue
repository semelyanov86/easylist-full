<script setup lang="ts">
import { Briefcase, CheckCircle, ShoppingCart } from 'lucide-vue-next';

import JobTrackerShowcase from './JobTrackerShowcase.vue';
import ShoppingListsShowcase from './ShoppingListsShowcase.vue';
</script>

<template>
    <section id="features" class="px-6 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl">
            <div
                class="mb-4 text-center text-sm font-semibold tracking-widest text-chart-1 uppercase"
            >
                Возможности
            </div>
            <h2
                class="mb-4 text-center text-3xl font-bold tracking-tight lg:text-4xl"
            >
                Инструменты для продуктивности
            </h2>
            <p
                class="mx-auto mb-16 max-w-2xl text-center text-muted-foreground lg:text-lg"
            >
                Два мощных модуля, которые помогут организовать поиск работы и
                управлять повседневными задачами.
            </p>

            <!-- Job Tracker -->
            <div class="mb-20 grid items-center gap-12 lg:grid-cols-2">
                <div>
                    <div
                        class="mb-4 inline-flex items-center gap-2 rounded-full bg-chart-1/10 px-4 py-1.5 text-sm font-medium text-chart-1"
                    >
                        <Briefcase class="size-4" />
                        Job Tracker
                    </div>
                    <h3 class="mb-4 text-2xl font-bold lg:text-3xl">
                        Умный трекер вакансий
                    </h3>
                    <p class="mb-6 text-muted-foreground">
                        Полный контроль над процессом поиска работы — от отклика
                        до оффера.
                    </p>
                    <ul class="space-y-4">
                        <li class="flex items-start gap-3">
                            <CheckCircle
                                class="mt-0.5 size-5 shrink-0 text-chart-2"
                            />
                            <span class="text-sm"
                                >Учёт откликов с статусами: отправлено,
                                просмотрено, интервью, оффер, отказ</span
                            >
                        </li>
                        <li class="flex items-start gap-3">
                            <CheckCircle
                                class="mt-0.5 size-5 shrink-0 text-chart-2"
                            />
                            <span class="text-sm"
                                >ИИ-генерация сопроводительных писем на основе
                                вакансии и вашего опыта</span
                            >
                        </li>
                        <li class="flex items-start gap-3">
                            <CheckCircle
                                class="mt-0.5 size-5 shrink-0 text-chart-2"
                            />
                            <span class="text-sm"
                                >Автоматическое извлечение стека технологий из
                                описания вакансии</span
                            >
                        </li>
                        <li class="flex items-start gap-3">
                            <CheckCircle
                                class="mt-0.5 size-5 shrink-0 text-chart-2"
                            />
                            <span class="text-sm"
                                >Аналитика воронки: конверсия откликов,
                                эффективность резюме</span
                            >
                        </li>
                    </ul>
                </div>

                <JobTrackerShowcase />
            </div>

            <!-- Списки покупок -->
            <div class="grid items-center gap-12 lg:grid-cols-2">
                <ShoppingListsShowcase class="order-2 lg:order-1" />

                <div class="order-1 lg:order-2">
                    <div
                        class="mb-4 inline-flex items-center gap-2 rounded-full bg-chart-4/10 px-4 py-1.5 text-sm font-medium text-chart-4"
                    >
                        <ShoppingCart class="size-4" />
                        Списки покупок
                    </div>
                    <h3 class="mb-4 text-2xl font-bold lg:text-3xl">
                        Удобные списки для всей семьи
                    </h3>
                    <p class="mb-6 text-muted-foreground">
                        Создавайте, организуйте и делитесь списками покупок в
                        удобных папках.
                    </p>
                    <ul class="space-y-4">
                        <li class="flex items-start gap-3">
                            <CheckCircle
                                class="mt-0.5 size-5 shrink-0 text-chart-2"
                            />
                            <span class="text-sm"
                                >Папки для организации: продукты, бытовая химия,
                                ремонт и другие</span
                            >
                        </li>
                        <li class="flex items-start gap-3">
                            <CheckCircle
                                class="mt-0.5 size-5 shrink-0 text-chart-2"
                            />
                            <span class="text-sm"
                                >Быстрое добавление товаров и отметка купленных
                                позиций</span
                            >
                        </li>
                        <li class="flex items-start gap-3">
                            <CheckCircle
                                class="mt-0.5 size-5 shrink-0 text-chart-2"
                            />
                            <span class="text-sm"
                                >Совместный доступ к спискам для всей
                                семьи</span
                            >
                        </li>
                        <li class="flex items-start gap-3">
                            <CheckCircle
                                class="mt-0.5 size-5 shrink-0 text-chart-2"
                            />
                            <span class="text-sm"
                                >Синхронизация между устройствами в реальном
                                времени</span
                            >
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
</template>
