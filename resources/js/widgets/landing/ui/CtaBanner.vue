<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight } from 'lucide-vue-next';

import { login, register } from '@/routes';

interface Props {
    canRegister: boolean;
}

defineProps<Props>();
</script>

<template>
    <section class="px-6 py-20">
        <div
            class="mx-auto max-w-4xl rounded-3xl bg-primary p-12 text-center text-primary-foreground lg:p-20"
        >
            <h2 class="mb-4 text-3xl font-bold lg:text-4xl">Готовы начать?</h2>
            <p class="mx-auto mb-8 max-w-xl text-primary-foreground/70">
                Присоединяйтесь к Easylist и организуйте поиск работы и
                повседневные дела в одном удобном приложении.
            </p>
            <div
                class="flex flex-col items-center justify-center gap-4 sm:flex-row"
            >
                <Link
                    v-if="canRegister"
                    :href="register()"
                    class="inline-flex items-center gap-2 rounded-xl bg-primary-foreground px-8 py-3.5 text-base font-semibold text-primary transition hover:bg-primary-foreground/90"
                >
                    Создать аккаунт бесплатно
                    <ArrowRight class="size-4" />
                </Link>
                <template v-if="!$page.props.auth.user">
                    <Link
                        :href="login()"
                        class="inline-flex items-center gap-2 rounded-xl border border-primary-foreground/30 px-8 py-3.5 text-base font-semibold text-primary-foreground transition hover:bg-primary-foreground/10"
                    >
                        Войти в аккаунт
                    </Link>
                </template>
            </div>
        </div>
    </section>
</template>
