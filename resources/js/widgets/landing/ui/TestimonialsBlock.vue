<script setup lang="ts">
import TestimonialCard from './TestimonialCard.vue';
</script>

<template>
    <section
        id="testimonials"
        class="bg-muted px-6 py-20 lg:py-28 dark:bg-accent"
    >
        <div class="mx-auto max-w-6xl">
            <div
                class="mb-4 text-center text-sm font-semibold tracking-widest text-chart-2 uppercase"
            >
                Отзывы
            </div>
            <h2
                class="mb-4 text-center text-3xl font-bold tracking-tight lg:text-4xl"
            >
                Что говорят наши пользователи
            </h2>
            <p
                class="mx-auto mb-16 max-w-2xl text-center text-muted-foreground"
            >
                Узнайте, как Easylist помогает людям организовать поиск работы и
                повседневные дела.
            </p>

            <div class="grid gap-6 md:grid-cols-3">
                <TestimonialCard
                    quote="Job Tracker полностью изменил мой подход к поиску работы. Теперь я вижу всю картину: сколько откликов отправил, где я в воронке, какие резюме работают лучше."
                    initials="АК"
                    name="Алексей Козлов"
                    role="Frontend-разработчик"
                    avatar-bg-class="bg-chart-1/10"
                    avatar-text-class="text-chart-1"
                />
                <TestimonialCard
                    quote="Списки покупок — спасение для нашей семьи. Жена добавляет товары, а я вижу их в магазине в реальном времени. Никаких забытых продуктов!"
                    initials="ДМ"
                    name="Дмитрий Морозов"
                    role="Менеджер проектов"
                    avatar-bg-class="bg-chart-2/10"
                    avatar-text-class="text-chart-2"
                />
                <TestimonialCard
                    quote="ИИ-генератор сопроводительных писем — это нечто! За месяц поиска получил в три раза больше приглашений на интервью, чем раньше за полгода."
                    initials="ЕС"
                    name="Елена Смирнова"
                    role="Data Scientist"
                    avatar-bg-class="bg-chart-5/10"
                    avatar-text-class="text-chart-5"
                />
            </div>
        </div>
    </section>
</template>
