<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { AuthHeader } from '@widgets/auth';
import { LandingFooter } from '@widgets/landing';

import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="flex min-h-svh flex-col bg-muted dark:bg-background">
        <AuthHeader />

        <main class="flex flex-1 items-center justify-center px-4 py-12">
            <div class="w-full max-w-md">
                <div
                    class="rounded-xl border border-border bg-card p-8 shadow-sm md:p-10 dark:shadow-none"
                >
                    <div class="mb-8 flex flex-col items-center gap-4">
                        <Link
                            :href="home()"
                            class="flex items-center justify-center"
                        >
                            <img
                                src="/images/easylist.svg"
                                alt="Easylist"
                                class="h-12 w-12"
                            />
                        </Link>
                        <div class="space-y-1.5 text-center">
                            <h1
                                class="text-xl font-semibold tracking-tight text-foreground"
                            >
                                {{ title }}
                            </h1>
                            <p class="text-sm text-muted-foreground">
                                {{ description }}
                            </p>
                        </div>
                    </div>

                    <slot />
                </div>
            </div>
        </main>

        <LandingFooter :can-register="true" />
    </div>
</template>
