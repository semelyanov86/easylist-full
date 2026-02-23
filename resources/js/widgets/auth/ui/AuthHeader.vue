<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Menu, X } from 'lucide-vue-next';
import { ref } from 'vue';

import { dashboard, home, login, register } from '@/routes';

const mobileMenuOpen = ref(false);

const homeUrl = home.url();
</script>

<template>
    <header
        class="sticky top-0 z-50 border-b border-border bg-background/80 backdrop-blur-lg"
    >
        <div
            class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4"
        >
            <Link :href="homeUrl" class="flex shrink-0 items-center gap-3">
                <img
                    src="/images/easylist-logo.svg"
                    alt="Easylist"
                    class="h-10"
                />
            </Link>

            <nav class="hidden items-center gap-8 text-sm font-medium md:flex">
                <Link
                    :href="homeUrl + '#features'"
                    class="text-muted-foreground transition hover:text-foreground"
                >
                    Возможности
                </Link>
                <Link
                    :href="homeUrl + '#benefits'"
                    class="text-muted-foreground transition hover:text-foreground"
                >
                    Преимущества
                </Link>
                <Link
                    :href="homeUrl + '#testimonials'"
                    class="text-muted-foreground transition hover:text-foreground"
                >
                    Отзывы
                </Link>
                <Link
                    :href="homeUrl + '#faq'"
                    class="text-muted-foreground transition hover:text-foreground"
                >
                    FAQ
                </Link>
            </nav>

            <div class="hidden items-center gap-3 md:flex">
                <template v-if="$page.props.auth.user">
                    <Link
                        :href="dashboard()"
                        class="rounded-lg bg-primary px-5 py-2 text-sm font-semibold text-primary-foreground transition hover:bg-primary/90"
                    >
                        Панель управления
                    </Link>
                </template>
                <template v-else>
                    <Link
                        :href="login()"
                        class="text-sm font-medium text-muted-foreground transition hover:text-foreground"
                    >
                        Войти
                    </Link>
                    <Link
                        :href="register()"
                        class="rounded-lg bg-primary px-5 py-2 text-sm font-semibold text-primary-foreground transition hover:bg-primary/90"
                    >
                        Регистрация
                    </Link>
                </template>
            </div>

            <button class="md:hidden" @click="mobileMenuOpen = !mobileMenuOpen">
                <Menu v-if="!mobileMenuOpen" class="size-6" />
                <X v-else class="size-6" />
            </button>
        </div>

        <div
            v-show="mobileMenuOpen"
            class="border-t border-border bg-background px-6 pt-4 pb-6 md:hidden"
        >
            <nav class="mb-6 flex flex-col gap-4 text-sm font-medium">
                <Link
                    :href="homeUrl + '#features'"
                    class="text-muted-foreground transition hover:text-foreground"
                    @click="mobileMenuOpen = false"
                >
                    Возможности
                </Link>
                <Link
                    :href="homeUrl + '#benefits'"
                    class="text-muted-foreground transition hover:text-foreground"
                    @click="mobileMenuOpen = false"
                >
                    Преимущества
                </Link>
                <Link
                    :href="homeUrl + '#testimonials'"
                    class="text-muted-foreground transition hover:text-foreground"
                    @click="mobileMenuOpen = false"
                >
                    Отзывы
                </Link>
                <Link
                    :href="homeUrl + '#faq'"
                    class="text-muted-foreground transition hover:text-foreground"
                    @click="mobileMenuOpen = false"
                >
                    FAQ
                </Link>
            </nav>
            <div class="flex flex-col gap-3">
                <template v-if="$page.props.auth.user">
                    <Link
                        :href="dashboard()"
                        class="rounded-lg bg-primary px-5 py-2.5 text-center text-sm font-semibold text-primary-foreground"
                    >
                        Панель управления
                    </Link>
                </template>
                <template v-else>
                    <Link
                        :href="login()"
                        class="rounded-lg border border-border px-5 py-2.5 text-center text-sm font-medium"
                    >
                        Войти
                    </Link>
                    <Link
                        :href="register()"
                        class="rounded-lg bg-primary px-5 py-2.5 text-center text-sm font-semibold text-primary-foreground"
                    >
                        Регистрация
                    </Link>
                </template>
            </div>
        </div>
    </header>
</template>
