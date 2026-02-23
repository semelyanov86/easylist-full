<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

import { dashboard, home, login, register } from '@/routes';

interface Props {
    canRegister: boolean;
}

defineProps<Props>();
</script>

<template>
    <footer class="border-t border-border px-6 py-12">
        <div class="mx-auto max-w-6xl">
            <div class="grid gap-12 md:grid-cols-4">
                <div class="md:col-span-1">
                    <Link :href="home.url()" class="mb-4 block">
                        <img
                            src="/images/easylist-logo.svg"
                            alt="Easylist"
                            class="h-10"
                        />
                    </Link>
                    <p class="text-sm text-muted-foreground">
                        Организуйте поиск работы и повседневные дела просто и
                        эффективно.
                    </p>
                </div>
                <div>
                    <h4 class="mb-4 text-sm font-semibold">Продукт</h4>
                    <ul class="space-y-2 text-sm text-muted-foreground">
                        <li>
                            <a
                                href="#features"
                                class="transition hover:text-foreground"
                                >Возможности</a
                            >
                        </li>
                        <li>
                            <a
                                href="#benefits"
                                class="transition hover:text-foreground"
                                >Преимущества</a
                            >
                        </li>
                        <li>
                            <a
                                href="#testimonials"
                                class="transition hover:text-foreground"
                                >Отзывы</a
                            >
                        </li>
                        <li>
                            <a
                                href="#faq"
                                class="transition hover:text-foreground"
                                >FAQ</a
                            >
                        </li>
                    </ul>
                </div>
                <div>
                    <h4 class="mb-4 text-sm font-semibold">Модули</h4>
                    <ul class="space-y-2 text-sm text-muted-foreground">
                        <li>Job Tracker</li>
                        <li>Списки покупок</li>
                        <li>ИИ-генератор писем</li>
                        <li>Аналитика воронки</li>
                    </ul>
                </div>
                <div>
                    <h4 class="mb-4 text-sm font-semibold">Аккаунт</h4>
                    <ul class="space-y-2 text-sm text-muted-foreground">
                        <template v-if="$page.props.auth.user">
                            <li>
                                <Link
                                    :href="dashboard()"
                                    class="transition hover:text-foreground"
                                    >Панель управления</Link
                                >
                            </li>
                        </template>
                        <template v-else>
                            <li>
                                <Link
                                    :href="login()"
                                    class="transition hover:text-foreground"
                                    >Войти</Link
                                >
                            </li>
                            <li v-if="canRegister">
                                <Link
                                    :href="register()"
                                    class="transition hover:text-foreground"
                                    >Регистрация</Link
                                >
                            </li>
                        </template>
                    </ul>
                </div>
            </div>
            <div
                class="mt-12 border-t border-border pt-8 text-center text-sm text-muted-foreground"
            >
                &copy; {{ new Date().getFullYear() }} Easylist. Все права
                защищены.
            </div>
        </div>
    </footer>
</template>
