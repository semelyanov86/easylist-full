<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight, Sparkles } from 'lucide-vue-next';

import { register } from '@/routes';

interface Props {
    canRegister: boolean;
}

defineProps<Props>();
</script>

<template>
    <section
        class="relative overflow-hidden px-6 pt-24 pb-20 lg:pt-36 lg:pb-32"
    >
        <div class="mx-auto max-w-4xl text-center">
            <div
                class="mb-6 inline-flex items-center gap-2 rounded-full border border-border bg-secondary px-4 py-1.5 text-sm text-secondary-foreground"
            >
                <Sparkles class="size-4 text-chart-4" />
                <span>ИИ-помощник для поиска работы</span>
            </div>

            <h1 class="mb-6 text-4xl font-bold tracking-tight lg:text-6xl">
                Организуйте
                <span class="relative inline-block">
                    поиск работы
                    <svg
                        class="absolute -bottom-2 left-0 w-full text-chart-1/60"
                        viewBox="0 0 200 12"
                        preserveAspectRatio="none"
                    >
                        <path
                            d="M0 8 Q50 0, 100 8 T200 8"
                            stroke="currentColor"
                            stroke-width="3"
                            fill="none"
                        />
                    </svg>
                </span>
                и повседневные дела
            </h1>

            <p
                class="mx-auto mb-10 max-w-2xl text-lg text-muted-foreground lg:text-xl"
            >
                Учитывайте отклики на вакансии, генерируйте сопроводительные
                письма через ИИ, анализируйте воронку — и ведите списки покупок
                для всей семьи. Всё в одном месте.
            </p>

            <div
                class="flex flex-col items-center justify-center gap-4 sm:flex-row"
            >
                <Link
                    v-if="canRegister"
                    :href="register()"
                    class="group inline-flex items-center gap-2 rounded-xl bg-primary px-8 py-3.5 text-base font-semibold text-primary-foreground shadow-lg transition hover:bg-primary/90"
                >
                    Начать бесплатно
                    <ArrowRight
                        class="size-4 transition group-hover:translate-x-1"
                    />
                </Link>
                <a
                    href="#features"
                    class="inline-flex items-center gap-2 rounded-xl border border-border bg-background px-8 py-3.5 text-base font-semibold transition hover:bg-accent"
                >
                    Узнать больше
                </a>
            </div>
        </div>

        <!-- Статистика -->
        <div
            class="mx-auto mt-20 grid max-w-3xl grid-cols-2 gap-8 border-t border-border pt-12 md:grid-cols-4"
        >
            <div class="text-center">
                <div class="text-3xl font-bold">2</div>
                <div class="mt-1 text-sm text-muted-foreground">Модуля</div>
            </div>
            <div class="text-center">
                <div class="text-3xl font-bold">ИИ</div>
                <div class="mt-1 text-sm text-muted-foreground">
                    Генерация писем
                </div>
            </div>
            <div class="text-center">
                <div class="text-3xl font-bold">24/7</div>
                <div class="mt-1 text-sm text-muted-foreground">
                    Доступность
                </div>
            </div>
            <div class="text-center">
                <div class="text-3xl font-bold">2FA</div>
                <div class="mt-1 text-sm text-muted-foreground">
                    Защита данных
                </div>
            </div>
        </div>
    </section>
</template>
